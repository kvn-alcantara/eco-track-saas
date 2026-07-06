<?php

namespace App\Mail;

use App\Models\CarbonReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CarbonReportGeneratedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CarbonReport $report
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Relatório de Carbono Disponível: ' . $this->report->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.carbon-report-generated',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
