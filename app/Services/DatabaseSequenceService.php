<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseSequenceService
{
    /**
     * Flag untuk mencegah pemanggilan berulang (re-entrancy) dalam satu proses request.
     */
    protected static bool $isRenumbering = false;

    /**
     * Rapikan semua tabel utama (users, alat, peminjaman) agar ID dimulai dari 1
     * secara berurutan tanpa jeda/gap, sinkronkan foreign key di peminjaman,
     * dan perbarui nilai sqlite_sequence ke ID tertinggi.
     */
    public function renumberAll(): array
    {
        if (self::$isRenumbering) {
            return [];
        }

        self::$isRenumbering = true;

        $driver = DB::connection()->getDriverName();
        $pdo = DB::connection()->getPdo();

        // Matikan sementara pengecekan foreign key agar renumbering transaksional aman tanpa bentrok
        if ($driver === 'sqlite') {
            $pdo->exec('PRAGMA foreign_keys = OFF;');
        } elseif ($driver === 'mysql') {
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 0;');
        }

        $pdo->beginTransaction();

        try {
            // 1. Pemetaan ID Users (1..N berurutan sesuai urutan ID lama)
            $users = $pdo->query('SELECT id FROM users ORDER BY id ASC')->fetchAll(\PDO::FETCH_COLUMN);
            $userMap = [];
            $newUserId = 1;
            foreach ($users as $oldId) {
                $userMap[(int) $oldId] = $newUserId++;
            }

            // 2. Pemetaan ID Alat (1..M berurutan sesuai urutan ID lama)
            $alat = $pdo->query('SELECT id FROM alat ORDER BY id ASC')->fetchAll(\PDO::FETCH_COLUMN);
            $alatMap = [];
            $newAlatId = 1;
            foreach ($alat as $oldId) {
                $alatMap[(int) $oldId] = $newAlatId++;
            }

            // 3. Pemetaan ID Peminjaman (1..K berurutan) dan sinkronisasi foreign key user_id & alat_id
            $peminjaman = $pdo->query('SELECT id, user_id, alat_id FROM peminjaman ORDER BY id ASC')->fetchAll(\PDO::FETCH_ASSOC);
            $peminjamanMap = [];
            $newPemId = 1;
            foreach ($peminjaman as $row) {
                $peminjamanMap[(int) $row['id']] = [
                    'new_id'      => $newPemId++,
                    'new_user_id' => $userMap[(int) $row['user_id']] ?? (int) $row['user_id'],
                    'new_alat_id' => $alatMap[(int) $row['alat_id']] ?? (int) $row['alat_id'],
                ];
            }

            $offset = 10000000;

            // Eksekusi update Users dua tahap (ke nilai offset dulu) agar tidak ada tabrakan PRIMARY KEY
            foreach ($userMap as $oldId => $newId) {
                $tempId = $oldId + $offset;
                $pdo->exec("UPDATE users SET id = $tempId WHERE id = $oldId");
            }
            foreach ($userMap as $oldId => $newId) {
                $tempId = $oldId + $offset;
                $pdo->exec("UPDATE users SET id = $newId WHERE id = $tempId");
            }

            // Eksekusi update Alat dua tahap
            foreach ($alatMap as $oldId => $newId) {
                $tempId = $oldId + $offset;
                $pdo->exec("UPDATE alat SET id = $tempId WHERE id = $oldId");
            }
            foreach ($alatMap as $oldId => $newId) {
                $tempId = $oldId + $offset;
                $pdo->exec("UPDATE alat SET id = $newId WHERE id = $tempId");
            }

            // Eksekusi update Peminjaman (id, user_id, alat_id)
            $stmt = $pdo->prepare('UPDATE peminjaman SET id = :temp_id, user_id = :new_user_id, alat_id = :new_alat_id WHERE id = :old_id');
            foreach ($peminjamanMap as $oldId => $info) {
                $stmt->execute([
                    ':temp_id'     => $oldId + $offset,
                    ':new_user_id' => $info['new_user_id'],
                    ':new_alat_id' => $info['new_alat_id'],
                    ':old_id'      => $oldId,
                ]);
            }
            foreach ($peminjamanMap as $oldId => $info) {
                $tempId = $oldId + $offset;
                $pdo->exec("UPDATE peminjaman SET id = {$info['new_id']} WHERE id = {$tempId}");
            }

            // Reset dan sinkronkan sequence agar auto increment selanjutnya pas setelah ID terakhir
            $maxUser = (int) $pdo->query('SELECT COALESCE(MAX(id), 0) FROM users')->fetchColumn();
            $maxAlat = (int) $pdo->query('SELECT COALESCE(MAX(id), 0) FROM alat')->fetchColumn();
            $maxPem  = (int) $pdo->query('SELECT COALESCE(MAX(id), 0) FROM peminjaman')->fetchColumn();

            if ($driver === 'sqlite') {
                $pdo->exec("DELETE FROM sqlite_sequence WHERE name IN ('users', 'alat', 'peminjaman')");
                $pdo->exec("INSERT INTO sqlite_sequence (name, seq) VALUES ('users', $maxUser)");
                $pdo->exec("INSERT INTO sqlite_sequence (name, seq) VALUES ('alat', $maxAlat)");
                $pdo->exec("INSERT INTO sqlite_sequence (name, seq) VALUES ('peminjaman', $maxPem)");
            }

            if ($pdo->inTransaction()) {
                $pdo->commit();
            }

            if ($driver === 'mysql') {
                $pdo->exec('ALTER TABLE users AUTO_INCREMENT = ' . ($maxUser + 1));
                $pdo->exec('ALTER TABLE alat AUTO_INCREMENT = ' . ($maxAlat + 1));
                $pdo->exec('ALTER TABLE peminjaman AUTO_INCREMENT = ' . ($maxPem + 1));
            }
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Log::error('Gagal merapikan urutan ID database: ' . $e->getMessage(), ['exception' => $e]);
            throw $e;
        } finally {
            if ($driver === 'sqlite') {
                $pdo->exec('PRAGMA foreign_keys = ON;');
            } elseif ($driver === 'mysql') {
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 1;');
            }
            self::$isRenumbering = false;
        }

        // Jika user yang sedang login ID-nya bergeser, perbarui sesi auth agar tidak ter-logout
        if (Auth::check()) {
            $currentId = (int) Auth::id();
            if (isset($userMap[$currentId]) && $userMap[$currentId] !== $currentId) {
                $newUser = User::find($userMap[$currentId]);
                if ($newUser) {
                    Auth::setUser($newUser);
                    if (session()->isStarted()) {
                        Auth::login($newUser);
                    }
                }
            }
        }

        return [
            'users'      => count($userMap),
            'alat'       => count($alatMap),
            'peminjaman' => count($peminjamanMap),
        ];
    }
}
