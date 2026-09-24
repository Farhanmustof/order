<?php

namespace App\Http\Controllers;

use App\Application\Maintenance\RestoreDeletedRecord;
use App\Infrastructure\Services\EloquentRecycleBin;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    public function index(Request $request, EloquentRecycleBin $bin)
    {
        $type = $request->input('jenis', 'pre_order');
        abort_unless(array_key_exists($type, $bin->types()), 404);

        return view('trash.index', ['type' => $type, 'types' => $bin->types(), 'records' => $bin->listing($type)]);
    }

    public function restore(string $type, int $id, RestoreDeletedRecord $useCase)
    {
        $label = $useCase->execute($type, $id, $this->actorId());

        return back()->with('success', "{$label} berhasil dipulihkan.");
    }
}
