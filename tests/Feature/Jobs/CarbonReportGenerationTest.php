<?php

namespace Tests\Feature\Jobs;

use App\Events\CarbonReportGenerated;
use App\Jobs\GenerateCarbonReportJob;
use App\Mail\CarbonReportGeneratedMail;
use App\Models\CarbonReport;
use App\Models\Company;
use App\Models\User;
use App\Models\WasteRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CarbonReportGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_trigger_report_generation(): void
    {
        $response = $this->postJson('/api/v1/reports/generate', [
            'title' => 'Anual 2026',
            'period_start' => '2026-01-01',
            'period_end' => '2026-12-31',
        ]);

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_trigger_report_generation_asynchronously(): void
    {
        Queue::fake();

        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/reports/generate', [
            'title' => 'Anual 2026',
            'period_start' => '2026-01-01',
            'period_end' => '2026-12-31',
        ]);

        $response->assertAccepted();
        $response->assertJson([
            'message' => 'Seu relatório está sendo processado',
        ]);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'title',
                'period_start',
                'period_end',
                'status',
            ]
        ]);

        $this->assertDatabaseHas('carbon_reports', [
            'company_id' => $company->id,
            'title' => 'Anual 2026',
            'status' => 'processing',
        ]);

        Queue::assertPushed(GenerateCarbonReportJob::class);
    }

    public function test_report_generation_validation(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        Sanctum::actingAs($user);

        // Missing fields
        $response = $this->postJson('/api/v1/reports/generate', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'period_start', 'period_end']);

        // End date before start date
        $response = $this->postJson('/api/v1/reports/generate', [
            'title' => 'Anual 2026',
            'period_start' => '2026-12-31',
            'period_end' => '2026-01-01',
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['period_end']);
    }

    public function test_job_processes_records_and_updates_status(): void
    {
        Event::fake();

        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        // Target range: 2026-01-01 to 2026-01-31
        $report = CarbonReport::create([
            'company_id' => $company->id,
            'title' => 'Jan 2026',
            'period_start' => '2026-01-01',
            'period_end' => '2026-01-31',
            'status' => 'processing',
        ]);

        // Waste records to include
        WasteRecord::factory()->create([
            'company_id' => $company->id,
            'occurred_at' => '2026-01-05 10:00:00',
            'quantity_kg' => 1500.0,
            'co2e_kg' => 750.0,
        ]);
        WasteRecord::factory()->create([
            'company_id' => $company->id,
            'occurred_at' => '2026-01-25 15:00:00',
            'quantity_kg' => 2500.0,
            'co2e_kg' => 1250.0,
        ]);

        // Waste record outside period (too early)
        WasteRecord::factory()->create([
            'company_id' => $company->id,
            'occurred_at' => '2025-12-31 23:59:59',
            'quantity_kg' => 5000.0,
            'co2e_kg' => 2500.0,
        ]);

        // Waste record outside period (too late)
        WasteRecord::factory()->create([
            'company_id' => $company->id,
            'occurred_at' => '2026-02-01 00:00:00',
            'quantity_kg' => 6000.0,
            'co2e_kg' => 3000.0,
        ]);

        // Waste record for other company
        WasteRecord::factory()->create([
            'company_id' => $otherCompany->id,
            'occurred_at' => '2026-01-15 10:00:00',
            'quantity_kg' => 8000.0,
            'co2e_kg' => 4000.0,
        ]);

        // Run the Job
        GenerateCarbonReportJob::dispatchSync($report);

        $report->refresh();

        $this->assertEquals('completed', $report->status);
        $this->assertEquals(4000.0, $report->total_waste_kg);
        $this->assertEquals(2000.0, $report->total_emissions_kg);
        $this->assertEquals(2, $report->summary['records_processed']);

        Event::assertDispatched(CarbonReportGenerated::class, function ($event) use ($report) {
            return $event->report->id === $report->id;
        });
    }

    public function test_notification_listener_sends_email(): void
    {
        Mail::fake();

        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $report = CarbonReport::create([
            'company_id' => $company->id,
            'title' => 'Test Report',
            'period_start' => '2026-01-01',
            'period_end' => '2026-01-31',
            'status' => 'completed',
        ]);

        event(new CarbonReportGenerated($report));

        Mail::assertSent(CarbonReportGeneratedMail::class, function ($mail) use ($user, $report) {
            return $mail->hasTo($user->email) && $mail->report->id === $report->id;
        });
    }
}
