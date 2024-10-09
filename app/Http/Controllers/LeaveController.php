<?php

namespace App\Http\Controllers;

use App\Http\Requests\LeaveRequest;
use App\Http\Requests\UserRequest;
use App\Models\Leave;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            $leaves = Leave::with('user')->get();
            $users = User::with('profile')->get();

        } elseif ($user->hasRole('project_manager')) {
            // Get the team associated with the project manager and eager load users with their profiles
            $team = Team::with(['users.profile'])->find($user->team_id); // Eager load the profile relationship
            $users = $team->users; // This will give you the users in the team
            $userIds = $users->pluck('id'); // Extract user IDs
            $leaves = Leave::with('user')->whereIn('user_id', $userIds)->get();
        } else {
            $leaves = Leave::where('user_id', $user->id)->get();
            $users = [];
        }

        return Inertia::render('Leaves/Index', [
            'leaves' => $leaves,
            'users' => $users
        ]);
    }

    /**
     * Store a new leave request.
     */
    public function store(LeaveRequest $leaveRequest)
    {
        $transformedStartDay =Carbon::parse($leaveRequest->start_day);
        $transformedEndDay = Carbon::parse($leaveRequest->end_day);

        Leave::create([
            'user_id' => $leaveRequest->user_id,
            'type_of_leave' => $leaveRequest->type_of_leave,
            'start_day' => $transformedStartDay->format('Y/m/d'),
            'end_day' => $transformedEndDay->format('Y/m/d'),
            'status_of_leave' => 'pending'
        ]);

        return to_route('leave.index');
    }
}
