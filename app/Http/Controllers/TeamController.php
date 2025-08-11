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
        $user = Auth::user()->load('roles');

        if (!$user || !$user->hasAnyRole(['admin', 'project_manager', 'user'])) {
            abort(403, 'Unauthorized');
        }

        // Tous les utilisateurs voient toutes les équipes
        $teams = Team::with('projectManager.profile')->get();

        // Charger les employés de chaque équipe
        $teams->each(function ($team) {
            $employeeIds = $team->employee_ids ?? [];
            $team->employees = User::whereIn('id', $employeeIds)
                ->with('profile')
                ->get()
                ->map(fn($user) => tap($user, fn($u) => $u->valid_balance = $u->valid_balance ?? 0));
        });

        // Si admin ou project_manager, récupérer les utilisateurs "user" pour gestion
        $isAdmin = $user->hasRole('admin') || $user->hasRole('project_manager');
        $users = $isAdmin
            ? User::role('user')->with('profile')->orderBy('email')->get()
            : collect();

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
    }

    public function create()
    {
        return Inertia::render('Teams/Create', [
            'users' => User::role('user')->with('profile')->orderBy('email')->get(),
            'auth' => [
                'user' => Auth::user(),
            ]
        ]);
    }

    public function edit(Team $team)
    {
        $projectManagers = User::role('project_manager')->with('profile')->get();

        return Inertia::render('Teams/Edit', [
            'team' => $team,
            'projectManagers' => $projectManagers,
        ]);
    }

    public function show(Team $team)
    {
        $user = Auth::user()->load('roles');

        $team->load('projectManager.profile');

        $members = User::whereIn('id', $team->employee_ids ?? [])
            ->with('profile')
            ->get()
            ->map(function ($user) use ($team) {
                $user->is_project_manager = $user->id === $team->project_manager_id;
                return $user;
            });

        $potentialMembers = User::whereNotIn('id', $team->employee_ids ?? [])
            ->where('id', '!=', $team->project_manager_id)
            ->role('user')
            ->with('profile')
            ->get();

        return Inertia::render('Teams/Show', [
            'team' => [
                'id' => $team->id,
                'team_name' => $team->team_name,
                'project_manager' => $team->projectManager,
                'project_manager_id' => $team->project_manager_id,
                'members' => $members,
                'created_at' => $team->created_at,
                'updated_at' => $team->updated_at,
            ],
            'users' => $potentialMembers,
            'auth' => [
                'user' => $user,
                'user_roles' => $user->roles->pluck('name'),
            ],
            'can' => [
                'update' => Auth::user()->can('update', $team),
                'delete' => Auth::user()->can('delete', $team),
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

        $team = Team::create([
            'team_name' => $validated['name'],
            'project_manager_id' => $user->id,
            'employee_ids' => array_column($validated['members'], 'id'),
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

        $employeeIds = isset($validated['members'])
            ? collect($validated['members'])->pluck('id')->unique()->values()->all()
            : [];

        $team->update([
            'team_name' => $validated['team_name'],
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
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|integer|exists:users,id',
        ]);

        $currentEmployeeIds = $team->employee_ids ?? [];
        $newMemberIds = array_diff($validated['user_ids'], $currentEmployeeIds);

        if (empty($newMemberIds)) {
            return back()->with('info', 'No new members to add');
        }

        $updatedEmployeeIds = array_values(array_unique(array_merge($currentEmployeeIds, $newMemberIds)));
        $team->employee_ids = $updatedEmployeeIds;

        if ($team->save()) {
            $message = count($newMemberIds) > 1 
                ? 'Members added successfully' 
                : 'Member added successfully';
                
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => $message,
                    'status' => 'success'
                ]);
            }
            
            return back()->with('success', $message);
        }

        $error = 'Failed to save team members';
        
        if ($request->wantsJson()) {
            return response()->json([
                'message' => $error,
                'status' => 'error'
            ], 500);
        }
        
        return back()->with('error', $error);
    }

    public function removeMember(Team $team, User $user)
    {
        $employeeIds = $team->employee_ids ?? [];
        $userId = (int)$user->id;

        $updatedEmployeeIds = array_values(array_filter($employeeIds, fn($id) => (int)$id !== $userId));

        $team->employee_ids = $updatedEmployeeIds;

        if ($team->save()) {
            return back()->with('success', 'Member removed successfully.');
        }

        return back()->with('error', 'Failed to remove member.');
    }
}
