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
            
            if (!$team) {
                // If no team is assigned, only show the manager's own leaves
                $leaves = Leave::with('user')
                    ->where('user_id', $user->id)
                    ->orderBy('id', 'desc')
                    ->get();
                $users = collect([$user]);
            } else {
                $users = $team->users;
                $userIds = $users->pluck('id');
                $leaves = Leave::with('user')
                    ->whereIn('user_id', $userIds)
                    ->orWhere('user_id', $user->id) // Also include the manager's own leaves
                    ->orderBy('id', 'desc')
                    ->get();
            }
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
        $transformedStartDay = Carbon::parse($leaveRequest->start_day);
        $transformedEndDay = $leaveRequest->end_day
            ? Carbon::parse($leaveRequest->end_day)
            : $transformedStartDay;
        $transformedstartTime = Carbon::parse($leaveRequest->start_time)->addHour(1)->format('H:i');
        $numberOfDays = $this->leaveRepository->getWeekdaysBetween($transformedStartDay, $transformedEndDay);
        $user = auth()->user();
        $validBalance = $user->valid_balance;
        $teamName = $user->team ? strtolower(trim($user->team->team_name ?? '')) : '';

        // Vérifier d'abord si l'utilisateur n'a pas déjà un congé à la même date
        $existingLeave = Leave::where('user_id', $user->id)
            ->where('status_of_leave', '!=', 'rejected')
            ->where(function($query) use ($transformedStartDay, $transformedEndDay) {
                $query->whereBetween('start_day', [
                        $transformedStartDay->format('Y-m-d'), 
                        $transformedEndDay->format('Y-m-d')
                    ])
                    ->orWhereBetween('end_day', [
                        $transformedStartDay->format('Y-m-d'), 
                        $transformedEndDay->format('Y-m-d')
                    ])
                    ->orWhere(function($q) use ($transformedStartDay, $transformedEndDay) {
                        $q->where('start_day', '<=', $transformedStartDay->format('Y-m-d'))
                          ->where('end_day', '>=', $transformedEndDay->format('Y-m-d'));
                    });
            })
            ->exists();

        // if ($existingLeave) {
        //     return response()->json([
        //         'message' => "Vous avez déjà une demande de congé en attente ou approuvée pour cette période."
        //     ], 422);
        // }

        // Ensuite vérifier les chevauchements avec les autres utilisateurs
        $overlappingLeaves = Leave::where('user_id', '!=', $user->id)
            ->where('status_of_leave', '!=', 'rejected')
            ->where(function($query) use ($transformedStartDay, $transformedEndDay) {
                $query->where(function($q) use ($transformedStartDay, $transformedEndDay) {
                    $q->whereBetween('start_day', [
                            $transformedStartDay->format('Y-m-d'), 
                            $transformedEndDay->format('Y-m-d')
                        ])
                        ->orWhereBetween('end_day', [
                            $transformedStartDay->format('Y-m-d'), 
                            $transformedEndDay->format('Y-m-d')
                        ])
                        ->orWhere(function($q) use ($transformedStartDay, $transformedEndDay) {
                            $q->where('start_day', '<=', $transformedStartDay->format('Y-m-d'))
                              ->where('end_day', '>=', $transformedEndDay->format('Y-m-d'));
                        });
                });
            })
            ->with('user.profile')
            ->get();

        if ($overlappingLeaves->isNotEmpty()) {
            return back()->with([
                'error' => 'Un employé a déjà un congé approuvé pendant cette période.',
                'overlap' => true
            ]);
        }

        try {
            // Create the leave request
            $leave = $this->leaveRepository->createLeave($leaveRequest, $transformedStartDay, $transformedEndDay, $transformedstartTime);

            // Send notification email
            Mail::to(['grh@softtodo.com', 'fatma.abid@softtodo.com'])
                ->queue(new LeaveRequestMail(
                    $user->first_name,
                    'request',
                    $leaveRequest->leave_reason,
                    $numberOfDays
                ));

            // Handle auto-approval based on leave type and team
            $message = '';
            $isApproved = false;

            switch ($leaveRequest->type_of_leave) {
                case 'vacation':
                    if ($teamName !== 'softtodo') {
                        $leaveService->approve($leave->id);
                        $message = 'Demande de congé soumise et approuvée avec succès.';
                        $isApproved = true;
                    } else {
                        $message = 'Demande de congé soumise avec succès.';
                    }
                    break;

                case 'authorisation':
                    if ($teamName !== 'softtodo') {
                        $leaveService->approve($leave->id);
                        $message = 'Demande d\'autorisation soumise et approuvée avec succès.';
                        $isApproved = true;
                    } else {
                        $message = 'Demande d\'autorisation soumise avec succès.';
                    }
                    break;

                default:
                    if ($teamName !== 'softtodo' && $validBalance >= $numberOfDays) {
                        $leaveService->approve($leave->id);
                        $message = 'Demande de congé soumise et approuvée avec succès.';
                        $isApproved = true;
                    } else {
                        $message = $validBalance < $numberOfDays 
                            ? 'Solde de congé insuffisant. La demande a été soumise mais nécessite une validation.'
                            : 'Demande de congé soumise avec succès.';
                    }
            }

            // Redirect with success/error message
            return redirect()->route('leaves.index')->with([
                $isApproved ? 'success' : 'info' => $message
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating leave request: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la soumission de la demande de congé.');
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
            return Inertia::render('Leaves/Index', [
                'success' => 'Leave approved successfully.'
            ]);
        } catch (Exception $e) {
            return Inertia::render('Leaves/Index', [
                'error' => $e->getMessage()
            ]);
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

        return Inertia::render('Leaves/Index', [
            'success' => 'Leave request rejected successfully.'
        ]);
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

        return Inertia::render('Leaves/Index', [
            'success' => 'Leave request deleted successfully. Balance updated.'
        ]);
    }
}