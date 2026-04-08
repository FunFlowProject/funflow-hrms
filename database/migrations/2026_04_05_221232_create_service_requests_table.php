<?php

declare(strict_types=1);

use App\Enums\ServiceRequestStatus;
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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_catalog_item_id')->nullable()->constrained('service_catalog_items')->nullOnDelete();
            $table->string('service_name_snapshot');
            $table->string('service_category_snapshot');
            $table->boolean('service_requires_justification_snapshot')->default(false);

            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('status', 60)->default(ServiceRequestStatus::Submitted->value);
            $table->text('justification')->nullable();
            $table->text('fulfillment_note')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('requester_id');
            $table->index('handled_by');
            $table->index('service_category_snapshot');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
