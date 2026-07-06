<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Carbono Disponível</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
        }
        .header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            padding: 32px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .content {
            padding: 40px;
            color: #374151;
            line-height: 1.6;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
            color: #111827;
        }
        .summary-card {
            background-color: #f9fafb;
            border-radius: 12px;
            padding: 24px;
            margin: 24px 0;
            border: 1px solid #f0f1f3;
        }
        .metric-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px dashed #e5e7eb;
        }
        .metric-row:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        .metric-label {
            font-weight: 500;
            color: #6b7280;
        }
        .metric-value {
            font-weight: 700;
            color: #111827;
            text-align: right;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .status-completed {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-processing {
            background-color: #fef3c7;
            color: #92400e;
        }
        .button {
            display: inline-block;
            background-color: #10b981;
            color: #ffffff !important;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            margin-top: 16px;
        }
        .footer {
            background-color: #f9fafb;
            padding: 24px;
            text-align: center;
            font-size: 13px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Relatório de Carbono Disponível</h1>
        </div>
        <div class="content">
            <div class="greeting">Olá,</div>
            <p>Temos o prazer de informar que o processamento do relatório de pegada de carbono <strong>{{ $report->title }}</strong> foi finalizado.</p>
            
            <div class="summary-card">
                <div class="metric-row">
                    <span class="metric-label">Identificador do Relatório</span>
                    <span class="metric-value">#{{ $report->id }}</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Período</span>
                    <span class="metric-value">
                        {{ $report->period_start ? $report->period_start->format('d/m/Y') : '-' }} a 
                        {{ $report->period_end ? $report->period_end->format('d/m/Y') : '-' }}
                    </span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Total de Resíduos</span>
                    <span class="metric-value">{{ number_format($report->total_waste_kg, 2, ',', '.') }} kg</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Total de Emissões</span>
                    <span class="metric-value">{{ number_format($report->total_emissions_kg, 2, ',', '.') }} kg CO₂e</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Status</span>
                    <span class="metric-value">
                        <span class="status-badge {{ $report->status === 'completed' ? 'status-completed' : 'status-processing' }}">
                            {{ $report->status }}
                        </span>
                    </span>
                </div>
            </div>
            
            <p>O relatório está pronto para visualização detalhada em seu painel.</p>
            
            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/api/reports/{{ $report->id }}" class="button" style="color: #ffffff;">Visualizar Relatório</a>
            </div>
        </div>
        <div class="footer">
            EcoTrack SaaS — Gerenciamento Inteligente de Emissões de Carbono<br>
            Este é um e-mail automático, por favor não responda.
        </div>
    </div>
</body>
</html>
