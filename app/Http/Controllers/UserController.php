<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UserRoleRequest;
use App\Mail\UserAuth;
use App\Mail\WelcomeNewUserMail;
use App\Models\Role;
use App\Models\User;
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
        $users = User::with('profile')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'profile' => $user->profile,
                    'roles' => $user->getRoleNames(),
                ];
            });
        $roles = Role::all();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(UserRequest $userRequest, ProfileUpdateRequest $profileUpdateRequest , UserRoleRequest $userRoleRequest)
    {
        $user = User::create([
            'email' => $userRequest->validated()['email'],
            'password' => Hash::make('password'),
        ]);
        $user->profile()->create($profileUpdateRequest->validated());
        $user->assignRole($userRoleRequest->validated()['role_id']);
//        Mail::to($user->email)->send(new WelcomeNewUserMail($user));
        return to_route('user.index');
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): Response
    {
        $roles = Role::all();
        return Inertia::render('Users/Create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): Response
    {
        $user->load('profile');
        return Inertia::render('Users/Edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(ProfileUpdateRequest $request, User $user)
    {
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $request->validated()
        );
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
