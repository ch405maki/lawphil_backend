<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('administrative_order', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('ao_number');
        $table->date('date');
        $table->string('citation')->nullable(); 
        $table->string('tenure')->nullable();   
        $table->longText('description')->nullable(); 
        $table->text('url')->nullable();
        $table->boolean('pdf_availability')->default(false);
        $table->string('pdf_path')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('administrative_order');
    }
};
