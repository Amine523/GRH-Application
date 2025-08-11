<?php

namespace App\Http\Controllers;

use App\Http\Requests\LeaveRequest;
use App\Mail\LeaveRequestMail;
use App\Models\Leave;
use App\Models\Team;
use App\Models\User;
use App\Models\Holiday;
use App\Repositories\LeaveRepository;
use App\Services\LeaveNotificationService;
use App\Services\LeaveService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class LeaveController extends Controller
{
    protected LeaveRepository $leaveRepository;

    public function __construct(LeaveRepository $leaveRepository)
    {
        $this->leaveRepository = $leaveRepository;
    }

    public function index(): Response
    {
        $user = auth()->user()->load('profile');
        $leaves = collect();
        $users = collect();

        if ($user->hasRole('admin')) {
            $leaves = Leave::with(['user.profile'])
                ->orderBy('start_day', 'desc')
                ->get();
            $users = User::with(['profile', 'team'])->get();
        } elseif ($user->hasRole('project_manager')) {
            $managedTeams = Team::where('project_manager_id', $user->id)->get();

            if ($managedTeams->isNotEmpty()) {
                $userIds = $managedTeams->flatMap(function ($team) {
                    return $team->employee_ids ?? [];
                })->unique()->values()->all();

                if (!in_array($user->id, $userIds)) {
                    $userIds[] = $user->id;
                }

                $users = User::with(['profile', 'team'])
                    ->whereIn('id', $userIds)
                    ->get();

                $leaves = Leave::with(['user.profile'])
                    ->whereIn('user_id', $userIds)
                    ->orderBy('start_day', 'desc')
                    ->get();
            } else {
                $users = collect([$user]);
                $leaves = Leave::with(['user.profile'])
                    ->where('user_id', $user->id)
                    ->orderBy('start_day', 'desc')
                    ->get();
            }
        } else {
            $leaves = Leave::with(['user.profile'])
                ->where('user_id', $user->id)
                ->orderBy('start_day', 'desc')
                ->get();
            $users = collect([$user]);
        }

        $teams = Team::all();
        $teamsData = $teams->map(function ($team) {
            return [
                'id' => $team->id,
                'team_name' => $team->team_name,
                'employee_ids' => $team->employee_ids ?? [],
                'project_manager_id' => $team->project_manager_id
            ];
        });

        return Inertia::render('Leaves/Index', [
            'leaves' => $leaves,
            'users' => $users,
            'teams' => $teamsData,
            'holidays' => Holiday::all(),
        ]);
    }

    public function show($id)
    {
        $leave = Leave::with(['user.profile'])->findOrFail($id);
        return Inertia::render('Leaves/Show', [
            'leave' => $leave
        ]);
    }

    public function store(LeaveRequest $leaveRequest, LeaveService $leaveService)
    {
        $user = User::find($leaveRequest->user_id);
        if (!$user) {
            return back()->with('error', 'User not found.');
        }

        $startDay = Carbon::parse($leaveRequest->start_day)->addDay();
        $endDay = $leaveRequest->end_day
            ? Carbon::parse($leaveRequest->end_day)->addDay()
            : $startDay;

        if (Leave::hasTeamOverlap($startDay, $endDay, $user->id)) {
            return back()->with('error', 'Another team member already has leave scheduled during this period.');
        }

        if ($leaveRequest->type_of_leave === 'deduction') {
            $leaveService->handleLateDeduction($user, $leaveRequest);
            return to_route('leaves.index')->with('success', "Leave deduction has been saved.");
        }
    }

    public function edit($id)
    {
        $leave = Leave::findOrFail($id);
        $users = User::with(['profile', 'team'])->get();
        $teams = Team::all();

        return Inertia::render('Leaves/Edit', [
            'leave' => $leave,
            'users' => $users,
            'teams' => $teams
        ]);
    }

    public function update(LeaveRequest $request, $id)
    {
        $leave = Leave::findOrFail($id);

        $leave->update([
            'user_id' => $request->user_id,
            'start_day' => $request->start_day,
            'start_time' => $request->start_time,
            'end_day' => $request->end_day,
            'end_time' => $request->end_time,
            'type_of_leave' => $request->type_of_leave,
            'status_of_leave' => $request->status_of_leave,
            'authorization_hour' => $request->authorization_hour,
            'deduction_days' => $request->deduction_days
        ]);

        return redirect()->route('leaves.index')->with('success', 'Leave updated successfully.');
    }

    public function approve(LeaveService $leaveService)
    {
        $request = request()->validate([
            'id' => 'required|integer|exists:leaves,id',
        ]);

        try {
            $leaveService->approve($request['id']);
            return redirect()->route('leaves.index')->with('success', 'Leave approved successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Refuse a leave request.
     */
    public function refuse()
    {
        $request = request()->all();
        $leave = Leave::find($request['id']);
        $leaveReason = request()->leaveReason;
        $leave->status_of_leave = 'rejected';
        $leave->save();
        Mail::to($leave->user->email)->send(new LeaveRequestMail($leave->user->profile->first_name, 'rejected', $leaveReason));

        return to_route('leaves.index')->with('success', 'Leave request rejected successfully.');
    }

    public function delete()
    {
        $request = request()->validate([
            'id' => 'required|integer|exists:leaves,id',
        ]);

        $leave = Leave::findOrFail($request['id']);
        $user = $leave->user;

        if ($leave->type_of_leave === 'authorisation') {
            if ($user->authorization_hours == 6) {
                $user->valid_balance += 0.5;
            }

            $user->authorization_hours -= $leave->authorization_hour;
        } elseif ($leave->type_of_leave === 'halfday') {
            $user->valid_balance += 0.5;
        } else {
            $leaveDays = Carbon::parse($leave->start_day)
                    ->diffInWeekdays(Carbon::parse($leave->end_day)) + 1;
            $user->valid_balance += $leaveDays;
        }
        $user->save();
        $leave->delete();

        return to_route('leaves.index')->with('success', 'Leave request deleted successfully. Balance updated.');
    }

    public function cancel(Request $request)
    {
        $leave = Leave::find($request->id);

        if (!$leave) {
            return back()->with('error', 'Leave request not found.');
        }

        $user = Auth::user();

        if ($user->id !== $leave->user_id && !$user->hasAnyRole(['admin', 'project_manager'])) {
            return back()->with('error', 'You are not authorized to cancel this leave request.');
        }

        if ($leave->status_of_leave !== 'pending') {
            return back()->with('error', 'This leave request cannot be cancelled as it has already been processed.');
        }

        $leave->delete();

        return back()->with('success', 'Leave request cancelled successfully.');
    }

    /**
     * Check for overlapping leave requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkOverlap(Request $request)
    {
        $request->validate([
            'start_day' => 'required|date',
            'end_day' => 'required|date|after_or_equal:start_day',
            'user_id' => 'required|exists:users,id',
        ]);

        $startDay = Carbon::parse($request->start_day);
        $endDay = Carbon::parse($request->end_day);
        $userId = $request->user_id;

        // Check for existing leaves that overlap with the requested dates
        $overlappingLeaves = Leave::where('user_id', $userId)
            ->where('status_of_leave', '!=', 'rejected')
            ->where(function ($query) use ($startDay, $endDay) {
                $query->whereBetween('start_day', [$startDay, $endDay])
                    ->orWhereBetween('end_day', [$startDay, $endDay])
                    ->orWhere(function ($q) use ($startDay, $endDay) {
                        $q->where('start_day', '<=', $startDay)
                            ->where('end_day', '>=', $endDay);
                    });
            })
            ->get();

        if ($overlappingLeaves->isNotEmpty()) {
            $leaveDates = $overlappingLeaves->map(function ($leave) {
                return [
                    'start' => $leave->start_day->format('Y-m-d'),
                    'end' => $leave->end_day->format('Y-m-d'),
                    'type' => $leave->type_of_leave,
                    'status' => $leave->status_of_leave
                ];
            });

            return response()->json([
                'is_overlapping' => true,
                'overlap_message' => 'You already have leave requests during this period.',
                'leaves' => $leaveDates
            ]);
        }

        return response()->json([
            'is_overlapping' => false
        ]);
    }
}
