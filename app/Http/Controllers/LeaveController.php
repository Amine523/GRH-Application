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
            $leaves = Leave::with('user')->orderBy('id', 'desc')->get();
            $users = User::with('profile')->get();
        } elseif ($user->hasRole('project_manager')) {
            $team = Team::with(['users.profile'])->find($user->team_id);
            $users = $team->users;
            $userIds = $users->pluck('id');
            $leaves = Leave::with('user')->whereIn('user_id', $userIds)->orderBy('id', 'desc')->get();
        } else {
            $leaves = Leave::with('user')->where('user_id', $user->id)->orderBy('id', 'desc')->get();
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
        $transformedstartTime = Carbon::parse($leaveRequest->start_time)->addHour(1)->format('H:i');
        $numberOfDays = $this->leaveRepository->getWeekdaysBetween($transformedStartDay, $transformedEndDay);
        $user = auth()->user();
        $validBalance = $user->valid_balance;

        $leave = $this->leaveRepository->createLeave($leaveRequest, $transformedStartDay, $transformedEndDay, $transformedstartTime);

        Mail::to(['grh@softtodo.com', 'fatma.abid@softtodo.com'])
            ->send(new LeaveRequestMail(
                $user->first_name,
                'request',
                $leaveRequest->leave_reason,
                $numberOfDays
            ));

        switch ($leaveRequest->type_of_leave) {
            case 'vacation':
                if (strtolower(trim($user->team->team_name)) !== 'softtodo') {
                    $leaveService->approve($leave->id);
                    return to_route('leave.index')->with('success', 'Vacation request submitted and approved successfully.');
                }
                return to_route('leave.index')->with('success', 'Vacation request submitted successfully.');

            case 'authorisation':
                if (strtolower(trim($user->team->team_name)) !== 'softtodo') {
                    $leaveService->approve($leave->id);
                    return to_route('leave.index')->with('success', 'Authorization request submitted and approved successfully.');
                }
                return to_route('leave.index')->with('success', 'Authorization request submitted successfully.');

            default:
                if (strtolower(trim($user->team->team_name)) !== 'softtodo' && $validBalance >= $numberOfDays) {
                    $leaveService->approve($leave->id);
                    return to_route('leave.index')->with('success', 'Leave request submitted and approved successfully.');
                }
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

        return to_route('leave.index')->with('success', 'Leave request deleted successfully. Balance updated.');
    }
}
