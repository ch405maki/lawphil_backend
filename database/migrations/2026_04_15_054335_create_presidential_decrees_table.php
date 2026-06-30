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
    Schema::create('presidential_decrees', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');

        $table->string('pd_number')->nullable();
        $table->string('date')->nullable();
        $table->text('subject')->nullable();
        $table->string('reference')->nullable();
        $table->string('pdf_path')->nullable();
        $table->boolean('pdf_availability')->default(false);
        
        // Para sa compatibility ng import code:
        $table->string('citation')->nullable();
        $table->string('ponente')->nullable();
        $table->string('url')->nullable();
        $table->timestamps();
    });
}
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presidential_decrees');
    }
};
