<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presidential_decrees', function (Blueprint $table) {
            $table->string('tenure')->nullable()->after('subject');
        });
    }

    public function down(): void
    {
        Schema::table('presidential_decrees', function (Blueprint $table) {
            $table->dropColumn('tenure');
        });
    }
};
