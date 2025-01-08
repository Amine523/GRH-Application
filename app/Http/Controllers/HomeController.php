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
        $recentActivities = Leave::with('user.profile')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($leave) {
                $profile = $leave->user->profile;
                $firstName = $profile->first_name ?? 'N/A';
                $lastName = $profile->last_name ?? 'N/A';
                $profilePicture = $profile->profile_picture ?? null;

                return [
                    'id' => $leave->id,
                    'activity' => $firstName . ' ' . $lastName . ' submitted a ' . $leave->type_of_leave . ' request',
                    'time' => $leave->created_at->diffForHumans(),
                    'profile_picture' => $profilePicture,
                ];
            });

        $inactiveUsers = User::whereDoesntHave('leaves', function ($query) {
            $query->whereDate('start_day', '<=', today())
                ->whereDate('end_day', '>=', today());
        })->with(['profile:id,user_id,first_name,last_name,profile_picture'])
            ->get()
            ->map(function ($user) {
                return [
                    'first_name' => $user->profile->first_name ?? null,
                    'last_name' => $user->profile->last_name ?? null,
                    'profile_picture' => $user->profile->profile_picture ?? null,
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

        $upcomingEvents = Leave::with('user.profile')
            ->whereDate('start_day', '>', $today)
            ->whereDate('start_day', '<=', $today->copy()->addWeek())
            ->get()
            ->map(function ($leave) use ($today) {
                $profile = $leave->user->profile;
                $firstName = $profile->first_name ?? 'N/A';
                $lastName = $profile->last_name ?? 'N/A';
                $profilePicture = $profile->profile_picture ?? null;

                $startDate = $leave->start_day;
                $eventDate = $startDate->isSameDay($today->copy()->addDay()) ? 'Tomorrow' : $startDate->format('l, F j');

                return [
                    'name' => $firstName . ' ' . $lastName,
                    'profile_picture' => $profilePicture,
                    'event' => $leave->type_of_leave . ' Leave',
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
