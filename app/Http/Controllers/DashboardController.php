<?php

namespace App\Http\Controllers;

use App\Infrastructure\Queries\DashboardQuery;

class DashboardController extends Controller
{
    public function __invoke(DashboardQuery $query)
    {
        return view('dashboard', $query->summary());
    }
}
