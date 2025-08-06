<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UserRoleRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Mail\BalanceUpdatedMail;
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
        $filePath = '/storage/images/placeholder.png';
        if ($profileUpdateRequest->hasFile('profile_picture')) {
            $fileUpload = $profileUpdateRequest->file('profile_picture')->store('profile_pictures', 'public');
            $filePath = Storage::url($fileUpload);
        }
        $user = User::create([
            'email' => $userRequest->email,
            'password' => Hash::make('password'),
            'valide_balance' => 23,
            'team_id' => $userRequest->team_id,
        ]);
        $user->profile()->create([...$profileUpdateRequest->validated(), 'profile_picture' => $filePath]);
        $user->assignRole($userRoleRequest->role_id);
        Mail::to($user->email)->send(new WelcomeNewUserMail($user));
        return to_route('users.index')->with('success', 'User created successfully.');
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
        
        // Charger les relations nécessaires
        $user->load(['profile', 'roles']);
        
        // Créer un tableau avec les données formatées pour la vue
        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'team_id' => $user->team_id,
            'profile' => $user->profile ? [
                'first_name' => $user->profile->first_name ?? '',
                'last_name' => $user->profile->last_name ?? '',
                'phone_number' => $user->profile->phone_number ?? '',
                'profile_picture' => $user->profile->profile_picture ?? null,
            ] : [
                'first_name' => '',
                'last_name' => '',
                'phone_number' => '',
                'profile_picture' => null,
            ],
            'roles' => $user->roles->pluck('id')->toArray(),
        ];
        
        return Inertia::render('Users/Edit', [
            'user' => $userData,
            'roles' => $roles,
            'teams' => $teams,
        ]);
    }

    public function warning(User $user)
    {
        Mail::to($user->email)->send(new WarningUser($user->profile->first_name));
        return to_route('users.index');
    }

    public function update(ProfileUpdateRequest $request, User $user, UserRoleRequest $userRoleRequest, UserUpdateRequest $userRequest)
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

        return to_route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return to_route('users.index');
    }
}
