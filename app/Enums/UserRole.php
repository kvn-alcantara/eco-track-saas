<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Colaborador = 'colaborador';
    case Auditor = 'auditor';

    public function canCreateWaste(): bool
    {
        return in_array($this, [self::Admin, self::Manager, self::Colaborador], true);
    }

    public function canUpdateWaste(): bool
    {
        return in_array($this, [self::Admin, self::Manager], true);
    }

    public function canDeleteWaste(): bool
    {
        return in_array($this, [self::Admin, self::Manager], true);
    }

    public function canManageReports(): bool
    {
        return in_array($this, [self::Admin, self::Manager], true);
    }

    public function canApproveReports(): bool
    {
        return in_array($this, [self::Admin, self::Manager, self::Auditor], true);
    }

    public function canViewAuditTrail(): bool
    {
        return in_array($this, [self::Admin, self::Manager, self::Auditor], true);
    }
}
