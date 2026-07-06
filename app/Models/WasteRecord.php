<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteRecord extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'recorded_by_user_id',
        'waste_type',
        'quantity_kg',
        'co2e_kg',
        'occurred_at',
        'notes',
        'audit_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'quantity_kg' => 'decimal:2',
            'co2e_kg' => 'decimal:2',
            'occurred_at' => 'datetime',
            'audit_snapshot' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
