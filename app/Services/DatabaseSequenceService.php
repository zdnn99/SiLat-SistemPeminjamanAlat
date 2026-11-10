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

        $pdo = DB::connection()->getPdo();

        // Matikan sementara pengecekan foreign key agar renumbering transaksional aman tanpa bentrok
        $pdo->exec('PRAGMA foreign_keys = OFF;');
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

            // Eksekusi update Users dua tahap (ke nilai negatif dulu) agar tidak ada tabrakan PRIMARY KEY
            foreach ($userMap as $oldId => $newId) {
                $pdo->exec("UPDATE users SET id = -$newId WHERE id = $oldId");
            }
            foreach ($userMap as $oldId => $newId) {
                $pdo->exec("UPDATE users SET id = $newId WHERE id = -$newId");
            }

            // Eksekusi update Alat dua tahap
            foreach ($alatMap as $oldId => $newId) {
                $pdo->exec("UPDATE alat SET id = -$newId WHERE id = $oldId");
            }
            foreach ($alatMap as $oldId => $newId) {
                $pdo->exec("UPDATE alat SET id = $newId WHERE id = -$newId");
            }

            // Eksekusi update Peminjaman (id, user_id, alat_id)
            $stmt = $pdo->prepare('UPDATE peminjaman SET id = :new_id, user_id = :new_user_id, alat_id = :new_alat_id WHERE id = :old_id');
            foreach ($peminjamanMap as $oldId => $info) {
                $stmt->execute([
                    ':new_id'      => -$info['new_id'],
                    ':new_user_id' => $info['new_user_id'],
                    ':new_alat_id' => $info['new_alat_id'],
                    ':old_id'      => $oldId,
                ]);
            }
            foreach ($peminjamanMap as $oldId => $info) {
                $pdo->exec("UPDATE peminjaman SET id = {$info['new_id']} WHERE id = -{$info['new_id']}");
            }

            // Reset dan sinkronkan sqlite_sequence agar auto increment selanjutnya pas setelah ID terakhir
            $pdo->exec("DELETE FROM sqlite_sequence WHERE name IN ('users', 'alat', 'peminjaman')");
            $maxUser = (int) $pdo->query('SELECT COALESCE(MAX(id), 0) FROM users')->fetchColumn();
            $maxAlat = (int) $pdo->query('SELECT COALESCE(MAX(id), 0) FROM alat')->fetchColumn();
            $maxPem  = (int) $pdo->query('SELECT COALESCE(MAX(id), 0) FROM peminjaman')->fetchColumn();

            $pdo->exec("INSERT INTO sqlite_sequence (name, seq) VALUES ('users', $maxUser)");
            $pdo->exec("INSERT INTO sqlite_sequence (name, seq) VALUES ('alat', $maxAlat)");
            $pdo->exec("INSERT INTO sqlite_sequence (name, seq) VALUES ('peminjaman', $maxPem)");

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            Log::error('Gagal merapikan urutan ID database: ' . $e->getMessage(), ['exception' => $e]);
            throw $e;
        } finally {
            $pdo->exec('PRAGMA foreign_keys = ON;');
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
