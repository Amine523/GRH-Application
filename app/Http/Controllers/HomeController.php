<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Leave;
use App\Models\User;

class HomeController extends Controller
{
    public function adminIndex()
    {
        $today = now();
        $oneWeekFromNow = $today->copy()->addWeek();

        // Fetch recent activities efficiently
        $recentActivities = Leave::with(['user.profile:id,user_id,first_name,last_name,profile_picture'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($leave) {
                $profile = optional($leave->user->profile);

                return [
                    'id' => $leave->id,
                    'activity' => "{$profile->first_name} {$profile->last_name} submitted a {$leave->type_of_leave} request",
                    'time' => $leave->created_at->diffForHumans(),
                    'profile_picture' => $profile->profile_picture,
                ];
            });

        // Get inactive users
        $inactiveUsers = User::whereHas('leaves', function ($query) use ($today) {
            $query->whereDate('start_day', '<=', $today)
                ->whereDate('end_day', '>=', $today);
        })
            ->with('profile:id,user_id,first_name,last_name,profile_picture')
            ->get()
            ->map(function ($user) {
                $profile = optional($user->profile);
                return [
                    'first_name' => $profile->first_name,
                    'last_name' => $profile->last_name,
                    'profile_picture' => $profile->profile_picture,
                ];
            });

        // Active users count
        $totalUsers = User::count();
        $activeToday = $totalUsers - $inactiveUsers->count();

        // Quick Overview Data
        $quickOverview = [
            'totalUsers' => $totalUsers,
            'activeToday' => $activeToday,
            'leavesApproved' => Leave::where('status_of_leave', 'approved')
                ->whereMonth('created_at', now()->month)
                ->count(),
            'pendingLeaves' => Leave::where('status_of_leave', 'pending')->count(),
            'inactiveUsers' => $inactiveUsers
        ];

        // Upcoming Events (Leaves)
        $upcomingEvents = Leave::with(['user.profile:id,user_id,first_name,last_name,profile_picture'])
            ->whereDate('start_day', '>', $today)
            ->whereDate('start_day', '<=', $oneWeekFromNow)
            ->get()
            ->map(function ($leave) use ($today) {
                $profile = optional($leave->user->profile);
                $eventDate = $leave->start_day->isSameDay($today->copy()->addDay())
                    ? 'Tomorrow'
                    : $leave->start_day->format('l, F j');

                return [
                    'name' => "{$profile->first_name} {$profile->last_name}",
                    'profile_picture' => $profile->profile_picture,
                    'event' => "{$leave->type_of_leave} Leave",
                    'date' => $eventDate,
                ];
            });

        return Inertia::render('Dashboard', [
            'recentActivities' => $recentActivities,
            'quickOverview' => $quickOverview,
            'upcomingEvents' => $upcomingEvents
        ]);
    }

    public function index()
    {
        $user = auth()->user();

        $leaveCredits = [
            'sick' => $user->leaves()->where('type_of_leave', 'sick')->count(),
            'vacation' => $user->leaves()->where('type_of_leave', 'vacation')->count(),
            'authorization' => $user->leaves()->where('type_of_leave', 'authorization')->count(),
            'halfDay' => $user->leaves()->where('type_of_leave', 'half day')->count(),
            'remaining' => $user->valid_balance,
        ];

        return Inertia::render('UserDashboard', [
            'leaveCredits' => $leaveCredits,
        ]);
    }
}
