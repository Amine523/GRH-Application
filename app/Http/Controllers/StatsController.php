<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function index()
    {
        $currentYear = Carbon::now()->year;

        $data = Leave::selectRaw('MONTH(created_at) as month, COUNT(DISTINCT user_id) as user_count')
            ->where('type_of_leave', 'authorisation')
            ->where('status_of_leave', 'approved')
            ->whereYear('created_at', $currentYear)
            ->groupByRaw('MONTH(created_at)')
            ->orderBy('month')
            ->get();

        return view('authorisation_stats.index', compact('data', 'currentYear'));
    }
}
