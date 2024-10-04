<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\TeamRequest;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UserRoleRequest;
use App\Mail\UserAuth;
use App\Mail\WelcomeNewUserMail;
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
        $teams = Team::with('projectManager.profile')->get();
        $projectManagerUsers = User::role('project_manager')->with('profile')->get();

        return Inertia::render('Teams/Index', [
            'teams' => $teams,
            'projectManagerUsers' => $projectManagerUsers,
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(TeamRequest $teamRequest)
    {
        Team::create([
            'team_name' => $teamRequest->team_name,
            'project_manager_id' => $teamRequest->project_manager_id ,
        ]);

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
     * Show the form for editing the specified user.
     */
    public function edit(Team $team): Response
    {
        $projectManagerUsers = User::role('project_manager')->with('profile')->get();

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
