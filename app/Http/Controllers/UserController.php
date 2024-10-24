<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UserRoleRequest;
use App\Mail\UserAuth;
use App\Mail\WelcomeNewUserMail;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

// Import your request class for validation

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(): Response
    {
        $roles = Role::all();
        $searchText = \request()->input('q') ?? '';
        $users = User::with(['profile', 'roles']);

        if ($searchText){
            $users = $users->whereHas('profile',function ($query) use($searchText){
                $query->whereAny(['first_name','last_name','phone_number'],'LIKE',"%$searchText%");
            });
        }

        return Inertia::render('Users/Index', [
            'users' => $users->get([
                'id','valid_balance'
            ]),
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created team in storage.
     */
    public function store(UserRequest $userRequest, ProfileUpdateRequest $profileUpdateRequest, UserRoleRequest $userRoleRequest)
    {
        $user = User::create([
            'email' => $userRequest->email,
            'password' => Hash::make('password'),
            'valide_balance' => 23,
            'team_id' => $userRequest->team_id,
        ]);
        $user->profile()->create($profileUpdateRequest->validated());
        $user->assignRole($userRoleRequest->role_id);
//        Mail::to($user->email)->send(new WelcomeNewUserMail($user));
        Mail::to('saif.ayedi@live.fr')->send(new WelcomeNewUserMail($user));
        return to_route('user.index');
    }

    /**
     * Show the form for creating a new team.
     */
    public function create(): Response
    {
        $roles = Role::all();
        $teams = Team::all();
        return Inertia::render('Users/Create', [
            'roles' => $roles,
            'teams' => $teams,
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): Response
    {
        $roles = Role::all();
        $teams = Team::all();
        $user->load(['profile', 'roles']);
        return Inertia::render('Users/Edit', [
            'user' => $user,
            'roles' => $roles,
            'teams' => $teams,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(ProfileUpdateRequest $request, User $user, UserRoleRequest $userRoleRequest)
    {
        $validatedRoleData = $userRoleRequest->validated();

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $request->validated()
        );

        if (isset($validatedRoleData['role_id'])) {
            $role = Role::where('name', $validatedRoleData['role_id'])->first();
            if ($role) {
                $user->roles()->sync($role->id);
            }
        }

        return to_route('user.index');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return to_route('user.index');
    }
}
