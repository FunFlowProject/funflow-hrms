<?php

declare(strict_types=1);

use App\Enums\DocumentClassification;
use App\Enums\DocumentScope;
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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('file_type'); // 'upload' or 'url'
            $table->string('file_path');
            $table->string('classification', 50)->default(DocumentClassification::InternalUseOnly->value);
            $table->string('scope_type', 50)->default(DocumentScope::Company->value);
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->boolean('requires_acknowledgment')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('classification');
            $table->index(['scope_type', 'scope_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
