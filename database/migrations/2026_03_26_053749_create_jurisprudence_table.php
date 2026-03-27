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
        Schema::create('jurisprudence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('gr_number')->nullable();
            $table->date('date')->nullable();
            $table->text('citation')->nullable();
            $table->text('reference')->nullable();
            $table->string('url')->nullable();
            $table->boolean('pdf_availability')->default(false);
            $table->string('pdf_path')->nullable();
            $table->string('ponente')->nullable();
            $table->text('subject')->nullable();
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurisprudence');
    }
};
