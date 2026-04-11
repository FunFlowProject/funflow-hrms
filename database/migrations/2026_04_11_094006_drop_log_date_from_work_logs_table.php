<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('work_logs', function (Blueprint $table) {
            if (Schema::hasColumn('work_logs', 'log_date')) {
                $table->dropColumn('log_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_logs', function (Blueprint $table) {
            //
        });
    }
};
