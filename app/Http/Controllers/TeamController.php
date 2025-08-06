<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TeamController extends Controller
{
    use AuthorizesRequests;
    public function index()
{
    try {
        $user = Auth::user()->load('roles');
        
        // Check if user is authenticated
        if (!$user) {
            abort(403, 'Unauthorized action.');
        }

        // Check if user has any role that can access teams
        if (!$user->hasAnyRole(['admin', 'project_manager', 'user'])) {
            abort(403, 'You do not have permission to view teams.');
        }

        // Tous les utilisateurs voient toutes les équipes
        $teams = Team::with('projectManager.profile')->get();

        // Get team members
        $teams->each(function ($team) {
            $employeeIds = $team->employee_ids ?? [];
            $team->employees = User::whereIn('id', $employeeIds)
                ->with('profile')
                ->get()
                ->map(function ($user) {
                    $user->valid_balance = $user->valid_balance ?? 0;
                    return $user;
                });
        });

        // Get available users for team management (only for admins/project managers)
        $isAdmin = $user->hasRole('admin') || $user->hasRole('project_manager');
        $users = $isAdmin 
            ? User::whereHas('roles', fn($q) => $q->where('name', 'user'))
                ->with('profile')
                ->orderBy('email')
                ->get()
            : [];

        return Inertia::render('Teams/Index', [
            'teams' => $teams,
            'users' => $users,
            'auth' => [
                'user' => $user,
                'roles' => $user->roles->pluck('name'),
                'profile' => $user->profile,
            ],
            'isAdmin' => $isAdmin,
        ]);
        
    } catch (\Exception $e) {
        // Log the error
        \Log::error('Error in TeamController@index: ' . $e->getMessage());
        
        // Return a more user-friendly error response
        if ($e->getCode() === 403) {
            return redirect()->back()->withErrors([
                'message' => $e->getMessage()
            ]);
        }
        
        return redirect()->back()->withErrors([
            'message' => 'An error occurred while loading teams. Please try again.'
        ]);
    }
}

    // public function index()
    // {
    //     try {
    //         $user = Auth::user()->load('roles');
            
    //         // Check if user is authenticated
    //         if (!$user) {
    //             abort(403, 'Unauthorized action.');
    //         }

    //         // Check if user has any role that can access teams
    //         if (!$user->hasAnyRole(['admin', 'project_manager', 'user'])) {
    //             abort(403, 'You do not have permission to view teams.');
    //         }
            
    //         // Get all teams with their project managers
    //         $teams = Team::with('projectManager.profile')
    //             ->when(!$user->hasRole('admin'), function($query) use ($user) {
    //                 // For non-admin users, only show teams they're part of
    //                 return $query->where('project_manager_id', $user->id)
    //                     ->orWhereJsonContains('employee_ids', (string)$user->id);
    //             })
    //             ->get();
            
    //         // Get team members
    //         $teams->each(function ($team) {
    //             $employeeIds = $team->employee_ids ?? [];
    //             $team->employees = User::whereIn('id', $employeeIds)
    //                 ->with('profile')
    //                 ->get()
    //                 ->map(function ($user) {
    //                     $user->valid_balance = $user->valid_balance ?? 0;
    //                     return $user;
    //                 });
    //         });
            
    //         // Get available users for team management (only for admins/project managers)
    //         $isAdmin = $user->hasRole('admin') || $user->hasRole('project_manager');
    //         $users = $isAdmin 
    //             ? User::whereHas('roles', fn($q) => $q->where('name', 'user'))
    //                 ->with('profile')
    //                 ->orderBy('email')
    //                 ->get()
    //             : [];

    //         return Inertia::render('Teams/Index', [
    //             'teams' => $teams,
    //             'users' => $users,
    //             'auth' => [
    //                 'user' => $user,
    //                 'roles' => $user->roles->pluck('name'),
    //                 'profile' => $user->profile,
    //             ],
    //             'isAdmin' => $isAdmin,
    //         ]);
            
    //     } catch (\Exception $e) {
    //         // Log the error
    //         \Log::error('Error in TeamController@index: ' . $e->getMessage());
            
    //         // Return a more user-friendly error response
    //         if ($e->getCode() === 403) {
    //             return redirect()->back()->withErrors([
    //                 'message' => $e->getMessage()
    //             ]);
    //         }
            
    //         return redirect()->back()->withErrors([
    //             'message' => 'An error occurred while loading teams. Please try again.'
    //         ]);
    //     }
    // }
    // public function index()
    // {
    //     $user = Auth::user();
    
    //     // Tous les utilisateurs (admin, project_manager, user...) voient toutes les équipes
    //     $teams = Team::with('projectManager.profile')->get();
    
    //     $teams->each(function ($team) {
    //         $employeeIds = $team->employee_ids ?? [];
    //         $team->employees = User::whereIn('id', $employeeIds)->get();
    //         $team->employees_count = count($employeeIds);
    //     });
    
    //     // Get users for the team creation form (only for project managers and admins)
    //     $users = [];
    //     if ($user->hasRole('admin') || $user->hasRole('project_manager') || $user->hasRole('project manager')) {
    //         $users = User::whereHas('roles', function($q) {
    //                 $q->where('name', 'user');
    //             })
    //             ->with('profile')
    //             ->orderBy('email')
    //             ->get();
    //     }
    
    //     return Inertia::render('Teams/Index', [
    //         'teams' => $teams,
    //         'users' => $users,
    //         'auth' => [
    //             'user' => $user->load('roles'),
    //         ]
    //     ]);
    // }


    public function create()
    {
        return Inertia::render('Teams/Create', [
            'users' => User::whereHas('roles', fn($q) => $q->where('name', 'user'))
                ->with('profile')
                ->orderBy('email')
                ->get(),
            'auth' => [
                'user' => auth()->user(),
            ]
        ]);
    }

    // public function edit(Team $team)
    // {
    //     $this->authorize('update', $team);
        
    //     $team->load('projectManager.profile');
        
    //     // Get all users for the form
    //     $users = User::whereHas('roles', function($q) {
    //             $q->where('name', 'user');
    //         })
    //         ->with('profile')
    //         ->orderBy('email')
    //         ->get();
            
    //     // Get current team members
    //     $teamMembers = $team->employee_ids ?? [];
        
    //     return Inertia::render('Teams/Edit', [
    //         'team' => [
    //             'id' => $team->id,
    //             'team_name' => $team->team_name,
    //             'project_manager_id' => $team->project_manager_id,
    //             'members' => $teamMembers,
    //         ],
    //         'users' => $users,
    //         'auth' => [
    //             'user' => auth()->user(),
    //         ]
    //     ]);
    // }
    public function edit(Team $team): \Inertia\Response
    {
        $projectManagerUsers = User::role('project_manager')->with('profile')->get();
        $projectManager = User::find($team->project_manager_id);

        if ($projectManager) {
            if ($projectManager->team_id && $projectManager->team_id != $team->id) {
                $projectManager->team_id = null;
                $projectManager->save();
            } else {
                $projectManager->team_id = $team->id;
                $projectManager->save();
            }
        }

        return Inertia::render('Teams/Edit', [
            'team' => $team,
            'projectManagers' => $projectManagerUsers,
        ]);
    }


    public function show(Team $team)
    {
        // Load the team with its project manager and their profile
        $team->load(['projectManager.profile']);
        
        // Get all team members (including the project manager)
        $members = User::whereIn('id', $team->employee_ids ?? [])
            ->with('profile')
            ->get()
            ->map(function($user) use ($team) {
                $user->is_project_manager = $user->id === $team->project_manager_id;
                return $user;
            });

        // Get potential members (users not in the team)
        $potentialMembers = User::whereNotIn('id', $team->employee_ids ?? [])
            ->where('id', '!=', $team->project_manager_id)
            ->with('profile')
            ->get();

        return Inertia::render('Teams/Show', [
            'team' => [
                'id' => $team->id,
                'team_name' => $team->team_name,
                'project_manager' => $team->projectManager,
                'members' => $members,
                'created_at' => $team->created_at,
                'updated_at' => $team->updated_at,
            ],
            'potentialMembers' => $potentialMembers,
            'can' => [
                'update' => auth()->user()->can('update', $team),
                'delete' => auth()->user()->can('delete', $team),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('project_manager')) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'members' => 'required|array|min:1',
            'members.*.id' => 'required|exists:users,id',
        ]);

        // Create the team
        $team = Team::create([
            'team_name' => $validated['name'],
            'project_manager_id' => $user->id,
            'employee_ids' => array_column($validated['members'], 'id')
        ]);

        return redirect()->route('teams.show', $team->id)
            ->with('success', 'Team created successfully!');
    }

    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'team_name' => 'required|string|max:255',
            'project_manager_id' => 'required|exists:users,id',
            'members' => 'nullable|array',
            'members.*.id' => 'required|exists:users,id',
        ]);

        $employeeIds = isset($validated['members']) ? collect($validated['members'])->pluck('id')->unique()->values()->all() : [];

        $team->update([
            'name' => $validated['team_name'],
            'project_manager_id' => $validated['project_manager_id'],
            'employee_ids' => $employeeIds,
        ]);

        return redirect()->route('teams.show', $team)->with('success', 'Team updated successfully.');
    }

    public function destroy(Team $team)
    {
        $team->delete();
        return redirect()->route('teams.index')->with('success', 'Team deleted successfully.');
    }

    public function addMember(Request $request, Team $team)
{
    return Inertia::render('Teams/Create', [
        'users' => User::whereHas('roles', fn($q) => $q->where('name', 'user'))
            ->with('profile')
            ->orderBy('email')
            ->get(),
        'auth' => [
            'user' => auth()->user(),
        ]
    ]);
}

    public function removeMember(Team $team, User $user)
    {
        try {
            $employeeIds = $team->employee_ids ?? [];
            
            // Convert all values to integers for proper comparison
            $employeeIds = array_map('intval', $employeeIds);
            $userId = (int)$user->id;
            
            // Remove the user ID from the array
            $updatedEmployeeIds = array_values(array_filter($employeeIds, function($id) use ($userId) {
                return $id !== $userId;
            }));
            
            // Update the team with the new employee IDs
            $team->employee_ids = $updatedEmployeeIds;
            
            if ($team->save()) {
                return back()->with('success', 'Member removed successfully.');
            }
            
            return back()->with('error', 'Failed to remove member.');
            
        } catch (\Exception $e) {
            \Log::error('Error removing team member: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while removing the member.');
        }
    }
    // private function getAvailableEmployees()
    // {
    //     return User::whereHas('roles', function ($query) {
    //             $query->where('name', 'user');
    //         })
    //         ->with('profile')          // eager loading de la relation 'profile'
    //         ->orderBy('email')        // tri par email croissant
    //         ->get();                  // exécution de la requête
    // }

    public function pendingLeaveRequests()
    {
        if (!class_exists(LeaveRequest::class)) {
            return collect(); // Return empty collection if LeaveRequest model doesn't exist
        }
        
        return LeaveRequest::whereIn('user_id', $this->employee_ids ?? [])
            ->where('status', 'pending')
            ->with('user')
            ->get();
    }

    public function userTeams()
    {
        return redirect()->route('teams.index');
    }
}