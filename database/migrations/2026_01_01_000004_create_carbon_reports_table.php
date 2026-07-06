<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carbon_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('generated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('total_waste_kg', 12, 2)->default(0);
            $table->decimal('total_emissions_kg', 12, 2)->default(0);
            $table->string('status')->default('draft');
            $table->json('summary')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carbon_reports');
    }
};
