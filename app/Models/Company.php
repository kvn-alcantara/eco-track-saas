<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'tax_id',
        'industry',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function wasteRecords(): HasMany
    {
        return $this->hasMany(WasteRecord::class);
    }

    public function carbonReports(): HasMany
    {
        return $this->hasMany(CarbonReport::class);
    }
}
