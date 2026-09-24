@extends('layouts.app')
@section('title', 'Riwayat aktivitas')

@php use App\Infrastructure\Persistence\Models\AuditLog; @endphp

@section('content')
<div class="page-head">
    <div>
        <h1>Riwayat aktivitas</h1>
        <p class="sub">Siapa melakukan apa dan kapan. Catatan ini tidak bisa diubah atau dihapus dari aplikasi.</p>
    </div>
</div>

<section class="panel">
    <div class="panel-body border-bottom">
        <form method="GET" class="filters">
            <div>
                <label class="form-label" for="user_id">Pengguna</label>
                <select name="user_id" id="user_id" class="form-select">
                    <option value="">Semua</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" @selected(($filters['user_id'] ?? '') == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="entity">Data</label>
                <select name="entity" id="entity" class="form-select">
                    <option value="">Semua</option>
                    @foreach (AuditLog::ENTITY_LABELS as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['entity'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="from">Dari</label>
                <input type="date" name="from" id="from" value="{{ $filters['from'] ?? '' }}" class="form-control">
            </div>
            <div>
                <label class="form-label" for="to">sampai</label>
                <input type="date" name="to" id="to" value="{{ $filters['to'] ?? '' }}" class="form-control">
            </div>
            <button class="btn btn-outline-primary">Terapkan</button>
            @if (array_filter($filters)) <a href="{{ route('audit.index') }}" class="btn btn-link">Reset</a> @endif
        </form>
    </div>
    @if ($logs->isEmpty())
        <x-empty title="Belum ada aktivitas yang cocok."></x-empty>
    @else
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Keterangan</th></tr></thead>
                <tbody>
                @foreach ($logs as $log)
                    @php
                        $link = match ($log->entity) {
                            'pre_order' => $log->entity_id ? route('pre-orders.show', $log->entity_id) : null,
                            'production_plan' => $log->entity_id ? route('production.show', $log->entity_id) : null,
                            default => null,
                        };
                    @endphp
                    <tr>
                        <td class="text-nowrap">{{ $log->created_at->translatedFormat('d M Y') }}<span class="cell-sub">{{ $log->created_at->format('H:i:s') }}</span></td>
                        <td>{{ $log->user?->name ?? 'Akun terhapus' }}</td>
                        <td class="text-nowrap small">
                            {{ AuditLog::ACTION_LABELS[$log->action] ?? $log->action }}
                            <span class="cell-sub">{{ AuditLog::ENTITY_LABELS[$log->entity] ?? $log->entity }}</span>
                        </td>
                        <td>@if ($link)<a href="{{ $link }}">{{ $log->description }}</a>@else{{ $log->description }}@endif</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages()) <div class="panel-foot">{{ $logs->links() }}</div> @endif
    @endif
</section>
@endsection
