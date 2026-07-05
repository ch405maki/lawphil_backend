<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `users` MODIFY `role` VARCHAR(255) NOT NULL DEFAULT 'user'");
        DB::statement("ALTER TABLE `role_permissions` MODIFY `role` VARCHAR(255) NOT NULL DEFAULT 'user'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('admin','user') NOT NULL DEFAULT 'user'");
        DB::statement("ALTER TABLE `role_permissions` MODIFY `role` ENUM('admin','user') NOT NULL DEFAULT 'user'");
    }
};
