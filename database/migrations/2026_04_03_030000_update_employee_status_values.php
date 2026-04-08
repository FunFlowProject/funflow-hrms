<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        DB::table('users')->where('status', 'active')->update(['status' => 'joined']);
        DB::table('users')->where('status', 'inactive')->update(['status' => 'terminated']);
        DB::table('users')->where('status', 'pending_onboarding')->update(['status' => 'onboarding']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        DB::table('users')->where('status', 'joined')->update(['status' => 'active']);
        DB::table('users')->where('status', 'terminated')->update(['status' => 'inactive']);
        DB::table('users')->where('status', 'onboarding')->update(['status' => 'pending']);
        DB::table('users')->where('status', 'pending_onboarding')->update(['status' => 'pending']);
    }
};
