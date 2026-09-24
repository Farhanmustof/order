<?php

namespace App\Http\Controllers;

use App\Infrastructure\Persistence\Models\AuditLog;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $request->only(['user_id', 'entity', 'from', 'to']);

        return view('audit.index', [
            'logs' => AuditLog::with('user')
                ->when($filters['user_id'] ?? null, fn ($q, $v) => $q->where('user_id', $v))
                ->when($filters['entity'] ?? null, fn ($q, $v) => $q->where('entity', $v))
                ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
                ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
                ->latest('id')->paginate(30)->withQueryString(),
            'filters' => $filters,
            'users' => User::orderBy('name')->get(),
        ]);
    }
}
