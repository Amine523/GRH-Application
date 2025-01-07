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

        $activeToday = User::whereDoesntHave('leaves', function ($query) {
            $query->whereDate('start_day', '<=', today())
                ->whereDate('end_day', '>=', today());
        })->count();

        $quickOverview = [
            'totalUsers' => User::count(),
            'activeToday' => $activeToday,
            'leavesApproved' => Leave::where('status_of_leave', 'approved')->whereMonth('created_at', now()->month)->count(),
            'pendingLeaves' => Leave::where('status_of_leave', 'pending')->count()
        ];

        // Define quick actions
        $quickActions = [
            ['label' => 'Add New User', 'action' => 'addUser'],
            ['label' => 'Create Announcement', 'action' => 'createAnnouncement'],
            ['label' => 'Generate Report', 'action' => 'generateReport']
        ];

        $today = now();

        $today = now();

        $upcomingEvents = Leave::with('user') // Load the user associated with the leave
        ->whereDate('start_day', '>', $today) // Leave starts in the future
        ->whereDate('start_day', '<=', $today->copy()->addWeek()) // Leave starts within the next 7 days
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
            'quickActions' => $quickActions,
            'upcomingEvents' => $upcomingEvents
        ]);
    }
}
