<?php

declare(strict_types=1);

use App\Enums\ActiveStatus;
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
        Schema::table('sub_companies', function (Blueprint $table): void {
            $table->unsignedTinyInteger('active')->default(ActiveStatus::ACTIVE->value);
        });

        Schema::table('squads', function (Blueprint $table): void {
            $table->unsignedTinyInteger('active')->default(ActiveStatus::ACTIVE->value);
        });

        Schema::table('hierarchies', function (Blueprint $table): void {
            $table->unsignedTinyInteger('active')->default(ActiveStatus::ACTIVE->value);
        });

        Schema::table('employee_assignments', function (Blueprint $table): void {
            $table->unsignedTinyInteger('active')->default(ActiveStatus::ACTIVE->value);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_assignments', function (Blueprint $table): void {
            $table->dropColumn('active');
        });

        Schema::table('hierarchies', function (Blueprint $table): void {
            $table->dropColumn('active');
        });

        Schema::table('squads', function (Blueprint $table): void {
            $table->dropColumn('active');
        });

        Schema::table('sub_companies', function (Blueprint $table): void {
            $table->dropColumn('active');
        });
    }
};
