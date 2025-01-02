<?php

namespace App\Http\Controllers;

use App\Http\Requests\LeaveRequest;
use App\Http\Requests\UserRequest;
use App\Mail\LeaveRequestMail;
use App\Models\Leave;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Repositories\LeaveRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use function Psy\debug;
use App\Services\LeaveService;

class LeaveController extends Controller
{
    protected $leaveRepository;

    // Inject LeaveRepository via constructor
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

    public function store(LeaveRequest $leaveRequest)
    {
        $transformedStartDay = Carbon::parse($leaveRequest->start_day)->addDay();
        $transformedEndDay = ($leaveRequest->end_day == null) ? Carbon::parse($leaveRequest->start_day)->addDay() : Carbon::parse($leaveRequest->end_day)->addDay();
        $numberOfDays = $this->leaveRepository->getWeekdaysBetween($transformedStartDay, $transformedEndDay);
        $user = auth()->user();
        $validBalance = $user->valid_balance;
        $authorizationHours = $user->authorization_hours;

        if ($leaveRequest->type_of_leave == 'vacation' && $user->team->team_name != 'softtodo') {
            $leave = $this->leaveRepository->createLeave($leaveRequest, $transformedStartDay, $transformedEndDay);
            $this->leaveRepository->acceptVacation($leave);
            return to_route('leave.index')->with('success', 'Vacation request submitted successfully.');
        }

        if ($leaveRequest->type_of_leave == 'authorization') {

            if ($authorizationHours >= $leaveRequest->authorizationHours) {
                $this->leaveRepository->createLeave($leaveRequest, $transformedStartDay, $transformedEndDay);
                return to_route('leave.index')->with('success', 'Authorization request submitted successfully.');
            } else {
                $admins = User::role('admin')->get();
                $this->leaveRepository->createLeave($leaveRequest, $transformedStartDay, $transformedEndDay);
                foreach ($admins as $admin) {
                    Mail::to($admin->email)->send(new LeaveRequestMail($user, 'approved-authorisation'));
                }
                return back()->with('error', 'Not enough authorization hours available.');
            }
        }

        if ($validBalance >= $numberOfDays) {
            $this->leaveRepository->createLeave($leaveRequest, $transformedStartDay, $transformedEndDay);
            return to_route('leave.index')->with('success', 'Leave request submitted successfully.');
        } else {
            $this->leaveRepository->createLeave($leaveRequest, $transformedStartDay, $transformedEndDay);
            Mail::to($user->email)->send(new LeaveRequestMail($user->first_name, 'approved-extra'));
            return back()->with('error', 'Not enough leave balance.');
        }
    }

    /**
     * Approve a leave request.
     */
    public function approve()
    {

        $request = request()->validate([
            'id' => 'required|integer|exists:leaves,id',
        ]);
        $leave = Leave::with('user')->find($request['id']);
        $validBalance = $leave->user->valid_balance;
        $numberOfDays = $this->leaveRepository->getWeekdaysBetween($leave->start_day, $leave->end_day);
        $daysToDeduct = match ($leave->type_of_leave) {
            'halfday' => 0.5,
            'authorisation' => 0,
            default => $numberOfDays,
        };
        if ($leave->type_of_leave === 'authorisation') {
            if ($leave->user->authorization_hours >= $leave->authorization_hour) {
                $leave->user->authorization_hours -= $leave->authorization_hour;
                $leave->user->save();
                Mail::to($leave->user->email)->send(new LeaveRequestMail($leave->user->profile->first_name, 'approved-authorisation'));

            } else {
                Mail::to($leave->user->email)->send(new LeaveRequestMail($leave->user->profile->first_name, 'rejected-authorisation'));
                return back()->with('error', 'Not enough authorization hours available.');
            }
        }

        if ($leave->type_of_leave !== 'authorisation' && $validBalance >= $daysToDeduct) {
            $leave->user->valid_balance -= $daysToDeduct;
            $leave->status_of_leave = 'approved';
            $leave->user->save();
            $leave->save();

            return to_route('leave.index')->with('success', 'Leave approved successfully.');
        } else {
            $leave->user->valid_balance -= $daysToDeduct;
            $leave->status_of_leave = 'approved';
            $leave->user->save();
            $leave->save();
            Mail::to($leave->user->email)->send(new LeaveRequestMail($leave->user->profile->first_name, 'approved-extra'));

            return to_route('leave.index')->with('success', 'Leave approved successfully.');
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

        $leave->user->valid_balance += $leaveDays;
        $leave->user->save();

        $leave->status_of_leave = 'revoked';
        $leave->save();

        Mail::to($leave->user->email)->send(new LeaveRequestMail($leave->user->profile->first_name, 'revoked', $revokeReason));

        return to_route('leave.index')->with('success', 'Leave request revoked successfully.');
    }
}
