<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waste_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('waste_type');
            $table->decimal('quantity_kg', 12, 2);
            $table->decimal('co2e_kg', 12, 2)->default(0);
            $table->timestamp('occurred_at');
            $table->text('notes')->nullable();
            $table->json('audit_snapshot')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_records');
    }
};
