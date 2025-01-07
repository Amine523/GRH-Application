<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Leave;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        $recentActivities = Leave::with('user.profile')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($leave) {
                $user = $leave->user->profile;
                $firstName = $user->first_name ?? 'N/A';
                $lastName = $user->last_name ?? 'N/A';
                return [
                    'id' => $leave->id,
                    'activity' => $firstName . ' ' . $lastName . ' submitted a ' . $leave->type_of_leave . ' request',
                    'time' => $leave->created_at->diffForHumans()
                ];
            });

        $inactiveUsers = User::whereDoesntHave('leaves', function ($query) {
            $query->whereDate('start_day', '<=', today())
                ->whereDate('end_day', '>=', today());
        })->with(['profile:id,user_id,first_name,last_name'])
        ->get()
            ->map(function ($user) {
                return [
                    'first_name' => $user->profile->first_name ?? null,
                    'last_name' => $user->profile->last_name ?? null,
                ];
            });

        $activeToday = User::count() - $inactiveUsers->count();

        $quickOverview = [
            'totalUsers' => User::count(),
            'activeToday' => $activeToday,
            'leavesApproved' => Leave::where('status_of_leave', 'approved')->whereMonth('created_at', now()->month)->count(),
            'pendingLeaves' => Leave::where('status_of_leave', 'pending')->count(),
            'inactiveUsers' => $inactiveUsers
        ];

        $today = now();

        $upcomingEvents = Leave::with('user')
            ->whereDate('start_day', '>', $today)
            ->whereDate('start_day', '<=', $today->copy()->addWeek())
            ->get()
            ->map(function ($leave) use ($today) {
                $startDate = $leave->start_day;
                $eventDate = $startDate->isSameDay($today->copy()->addDay()) ? 'Tomorrow' : $startDate->format('l, F j');
                return [
                    'name' => $leave->user->email,
                    'event' => $leave->type_of_leave . ' Leave',
                    'date' => $eventDate
                ];
            });

        return Inertia::render('Dashboard', [
            'recentActivities' => $recentActivities,
            'quickOverview' => $quickOverview,
            'upcomingEvents' => $upcomingEvents
        ]);
    }
}
