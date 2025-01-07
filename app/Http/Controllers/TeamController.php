<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\TeamRequest;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UserRoleRequest;
use App\Mail\UserAuth;
use App\Mail\WarningUser;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

// Import your request class for validation

class TeamController extends Controller
{
    /**
     * Display a listing of Teams.
     */
    public function index(): Response
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            $teams = Team::all()->load(['projectManager.profile', 'users.profile']);
        } else {
            $teams = Team::whereHas('users', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })->orWhere('project_manager_id', $user->id)
                ->get()->load(['projectManager.profile', 'users.profile']);
        }

        $formattedTeams = $teams->map(function ($team) {
            return [
                'id' => $team->id,
                'team_name' => $team->team_name,
                'project_manager' => $team->projectManager ? [
                    'id' => $team->projectManager->id,
                    'first_name' => $team->projectManager->profile->first_name,
                    'last_name' => $team->projectManager->profile->last_name,
                ] : null,
                'members' => $team->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'first_name' => $user->profile->first_name,
                        'last_name' => $user->profile->last_name,
                        'valid_balance' => $user->valid_balance,
                        'authorization_hours' => $user->authorization_hours,
                    ];
                }),
            ];
        });

        return Inertia::render('Teams/Index', [
            'teams' => $formattedTeams,
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(TeamRequest $teamRequest)
    {
        $team = Team::create([
            'team_name' => $teamRequest->team_name,
            'project_manager_id' => $teamRequest->project_manager_id,
        ]);

        $projectManager = User::find($teamRequest->project_manager_id);
        if (!$projectManager->team_id) {
            $projectManager->team_id = $team->id;
            $projectManager->save();
        }

        return to_route('teams.index');
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): Response
    {
        $projectManagerUsers = User::role('project_manager')->with('profile')->get();
        return Inertia::render('Teams/Create', [
            'projectManagers' => $projectManagerUsers,
        ]);
    }

    /**
     * Show the form for editing the specified team.
     */
    public function edit(Team $team): Response
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

    /**
     * Update the specified user in storage.
     */
    public function update(TeamRequest $teamRequest, Team $team)
    {
        $team->updateOrCreate(
            ['id' => $team->id],
            $teamRequest->validated()
        );
        return to_route('teams.index');
    }

    /**
     * Remove the specified team from storage.
     */
    public function destroy(Team $team)
    {
        $team->delete();

        return to_route('teams.index');
    }
}
