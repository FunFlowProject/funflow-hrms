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
        Schema::create('employee_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sub_company_id')->constrained('sub_companies')->cascadeOnDelete();
            $table->foreignId('squad_id')->nullable()->constrained('squads')->cascadeOnDelete();
            $table->foreignId('hierarchy_id')->constrained('hierarchies')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'sub_company_id', 'squad_id', 'hierarchy_id'], 'unique_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_assignments');
    }
};
