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
        // Ginalaw natin ito para maging executive_orders ang table name
        Schema::create('executive_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id'); // Identical sa Jurisprudence
            $table->string('eo_number')->nullable(); // Refactored mula gr_number
            $table->date('date')->nullable();
            $table->text('reference')->nullable(); // Ginaya natin yung citation/reference pattern mo
            $table->string('url')->nullable();
            $table->boolean('pdf_availability')->default(false);
            $table->string('pdf_path')->nullable();
            $table->text('subject')->nullable(); // Identical sa Jurisprudence
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('executive_orders');
    }
};