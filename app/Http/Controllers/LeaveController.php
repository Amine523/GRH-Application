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
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\LeaveService;

class LeaveController extends Controller
{
    protected LeaveRepository $leaveRepository;

    public function __construct(LeaveRepository $leaveRepository)
    {
        $this->leaveRepository = $leaveRepository;
    }

    public function index(): Response
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            $leaves = Leave::with(['user.profile'])
                ->orderBy('start_day', 'asc')
                ->get();
            $users = User::with('profile')->get();
        } elseif ($user->hasRole('project_manager')) {
            $team = Team::with(['users.profile'])->find($user->team_id);
            $users = $team->users;
            $userIds = $users->pluck('id');
            $leaves = Leave::with(['user.profile'])
                ->whereIn('user_id', $userIds)
                ->orderBy('start_day', 'asc')
                ->get();
        } else {
            $leaves = Leave::with(['user.profile'])
                ->where('user_id', $user->id)
                ->orderBy('start_day', 'asc')
                ->get();
            $users = User::with('profile')->get();
        }

        return Inertia::render('Leaves/Index', [
            'leaves' => $leaves,
            'users' => $users,
            'holidays' => Holiday::all(),
        ]);
    }
    /**
     * @throws Exception
     */
    public function store(LeaveRequest $leaveRequest, LeaveService $leaveService)
    {
        $user = User::find($leaveRequest->user_id);
        if (!$user) {
            return back()->with('error', 'User not found.');
        }
        if ($leaveRequest->type_of_leave === 'deduction') {
            $leaveService->handleLateDeduction($user, $leaveRequest);
            return to_route('leave.index')->with('success', "Leave deduction has been saved.");
        }

        $transformedStartDay = Carbon::parse($leaveRequest->start_day)->addDay();
        $transformedEndDay = $leaveRequest->end_day
            ? Carbon::parse($leaveRequest->end_day)->addDay()
            : $transformedStartDay;
        $transformedStartTime = Carbon::parse($leaveRequest->start_time)->format('H:i');

        $teamName = strtolower(trim($user->team->team_name ?? ''));

        $leave = $this->leaveRepository->createLeave(
            $leaveRequest,
            $transformedStartDay,
            $transformedEndDay,
            $transformedStartTime
        );

        LeaveNotificationService::sendLeaveNotifications($user, $leave);

        $shouldApprove = ($teamName !== 'softtodo');

        $message = ucfirst($leaveRequest->type_of_leave) . " request submitted successfully.";
        if ($shouldApprove) {
            $leaveService->approve($leave->id);
            $message = ucfirst($leaveRequest->type_of_leave) . " request submitted and approved successfully.";
        }

        return to_route('leaves.index')->with('success', $message);
    }

    /**
     * Approve a leave request.
     */
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
        } elseif ($leave->type_of_leave === 'deduction') {
            $user->valid_balance += $leave->deduction_days;
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
}