<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UserRoleRequest;
use App\Mail\BalanceUpdatedMail;
use App\Mail\UserAuth;
use App\Mail\WarningUser;
use App\Mail\WelcomeNewUserMail;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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

        if ($searchText) {
            $users = $users->whereHas('profile', function ($query) use ($searchText) {
                $query->whereAny(['first_name', 'last_name', 'phone_number'], 'LIKE', "%$searchText%");
            });
        }

        return Inertia::render('Users/Index', [
            'users' => $users->get([
                'id', 'valid_balance', 'email',
            ]),
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created team in storage.
     */
    public function store(UserRequest $userRequest, ProfileUpdateRequest $profileUpdateRequest, UserRoleRequest $userRoleRequest)
    {
        // Initialize $filePath with a default value
        $filePath = null;
        // Handle profile picture upload if present
        if ($profileUpdateRequest->hasFile('profile_picture')) {
            $fileUpload = $profileUpdateRequest->file('profile_picture')->store('profile_pictures', 'public');
            $filePath = Storage::url($fileUpload);
        }
        // Create the user
        $user = User::create([
            'email' => $userRequest->email,
            'password' => Hash::make('password'),
            'valide_balance' => 23,
            'team_id' => $userRequest->team_id,
        ]);
        // Create the user's profile with the provided data and the profile picture path
        $user->profile()->create([...$profileUpdateRequest->validated(),'profile_picture'=>$filePath]);
        $user->assignRole($userRoleRequest->role_id);
        Mail::to($user->email)->send(new WelcomeNewUserMail($user));
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

    public function warning(User $user)
    {
        Mail::to($user->email)->send(new WarningUser($user->profile->first_name));
        return to_route('user.index');
    }

    public function update(ProfileUpdateRequest $request, User $user, UserRoleRequest $userRoleRequest, UserRequest $userRequest)
    {
        $profileData = $request->validated();

        if ($request->hasFile('profile_picture')) {
            $fileUpload = $request->file('profile_picture')->store('profile_pictures', 'public');
            $profileData['profile_picture'] = Storage::url($fileUpload);
        } else {
            $profileData['profile_picture'] = $user->profile->profile_picture ?? null;
        }

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );
        $user->update($userRequest->validated());

        if ($userRoleRequest->validated()['valid_balance'] != $user->valid_balance) {
            $user->update(['valid_balance' => $userRoleRequest->validated()['valid_balance']]);
            Mail::to($user->email)->send(new BalanceUpdatedMail($user, $userRoleRequest->validated()['valid_balance']));
        }
        if (isset($userRoleRequest->validated()['role_id'])) {
            $role = Role::where('name', $userRoleRequest->validated()['role_id'])->first();
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
