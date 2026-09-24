<?php

namespace App\Domain\Auth;

enum Permission: string
{
    case ViewData = 'view-data';
    case ManageTransactions = 'manage-transactions';
    case ManageMasterData = 'manage-master-data';
    case ManageUsers = 'manage-users';
    case RestoreData = 'restore-data';
    case ViewAuditLog = 'view-audit-log';
}
