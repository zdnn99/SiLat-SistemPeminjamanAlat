<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $sqlPath = database_path('silat_multimedia.sql');
        if (file_exists($sqlPath)) {
            \Illuminate\Support\Facades\DB::unprepared(file_get_contents($sqlPath));
        }
    }
}