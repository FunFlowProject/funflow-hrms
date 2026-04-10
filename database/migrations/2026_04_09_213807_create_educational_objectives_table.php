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
        Schema::create('educational_objectives', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('mandatory')->default(false);
            $table->date('target_date')->nullable();
            $table->string('priority')->default('medium'); // enum low, medium, high
            $table->string('attachment')->nullable();
            
            $table->string('scope_type')->default('company');
            $table->unsignedBigInteger('scope_id')->nullable();
            
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('educational_objectives');
    }
};
