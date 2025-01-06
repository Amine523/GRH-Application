<?php

namespace App\Http\Controllers;

use App\Http\Requests\LeaveRequest;
use App\Mail\LeaveRequestMail;
use App\Models\Leave;
use App\Models\Team;
use App\Models\User;
use App\Repositories\LeaveRepository;
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
            $leaves = Leave::with('user')->get();
            $users = User::with('profile')->get();
        } elseif ($user->hasRole('project_manager')) {
            $team = Team::with(['users.profile'])->find($user->team_id);
            $users = $team->users;
            $userIds = $users->pluck('id');
            $leaves = Leave::with('user')->whereIn('user_id', $userIds)->get();
        } else {
            $leaves = Leave::with('user')->where('user_id', $user->id)->get();
            $users = User::with('profile')->get();
        }

        return Inertia::render('Leaves/Index', [
            'leaves' => $leaves,
            'users' => $users,
        ]);
    }

    /**
     * @throws Exception
     */
    public function store(LeaveRequest $leaveRequest, LeaveService $leaveService)
    {
        $transformedStartDay = Carbon::parse($leaveRequest->start_day)->addDay();
        $transformedEndDay = $leaveRequest->end_day
            ? Carbon::parse($leaveRequest->end_day)->addDay()
            : $transformedStartDay;

        $numberOfDays = $this->leaveRepository->getWeekdaysBetween($transformedStartDay, $transformedEndDay);
        $user = auth()->user();
        $validBalance = $user->valid_balance;
        $authorizationHours = $user->authorization_hours;

        if ($leaveRequest->type_of_leave === 'vacation' && $user->team->team_name !== 'softtodo') {
            $leave = $this->leaveRepository->createLeave($leaveRequest, $transformedStartDay, $transformedEndDay);
            $leaveService->approve($leave->id);
            return to_route('leave.index')->with('success', 'Vacation request submitted and approved successfully.');
        }

        if ($leaveRequest->type_of_leave === 'authorisation') {
            $leave = $this->leaveRepository->createLeave($leaveRequest, $transformedStartDay, $transformedEndDay);
            $leaveService->approve($leave->id);
            return to_route('leave.index')->with('success', 'Authorization request submitted and approved successfully.');
        }

        if ($validBalance >= $numberOfDays) {
            $leave = $this->leaveRepository->createLeave($leaveRequest, $transformedStartDay, $transformedEndDay);
            $leaveService->approve($leave->id);
            return to_route('leave.index')->with('success', 'Leave request submitted and approved successfully.');
        } else {
            $leave = $this->leaveRepository->createLeave($leaveRequest, $transformedStartDay, $transformedEndDay);
            Mail::to($user->email)->send(new LeaveRequestMail($user->first_name, 'approved-extra'));
            return back()->with('error', 'Not enough leave balance. Leave submitted but requires further review.');
        }
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
            return redirect()->route('leave.index')->with('success', 'Leave approved successfully.');
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

        return to_route('leave.index')->with('success', 'Leave request rejected successfully.');
    }

    public function revoke(LeaveService $leaveService)
    {
        $request = request()->all();
        $leave = Leave::find($request['id']);
        $revokeReason = request()->revokeReason;

        $leaveDays = $leaveService->countWorkingDays($leave->start_day, $leave->end_day);

        $user = $leave->user;
        $user->valid_balance += $leaveDays;
        $user->save();

        $leave->status_of_leave = 'revoked';
        $leave->save();

        Mail::to($user->email)->send(new LeaveRequestMail($user->profile->first_name, 'revoke', $revokeReason));

        return to_route('leave.index')->with('success', 'Leave request revoked successfully.');
    }
}
