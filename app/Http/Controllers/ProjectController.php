<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the projects.
     */
    public function index()
    {
        $user = Auth::user()->load('roles');

        if (!$user || !$user->hasAnyRole(['admin', 'project_manager', 'user'])) {
            abort(403, 'Unauthorized');
        }
        // Charger les projets avec le manager et son profil
        $projects = Project::with(['manager.profile'])->get();

        // Ajouter les membres et leur nombre à chaque projet
        $projects->each(function ($project) {
            // Récupérer les membres à partir des IDs stockés dans member_ids
            $memberIds = $project->member_ids ?? [];
            $project->members = !empty($memberIds) 
                ? User::with('profile')->whereIn('id', $memberIds)->get() 
                : collect();
            $project->members_count = count($memberIds);
        });

        return Inertia::render('Project/Index', [
            'projects' => $projects,
            'users' => User::with('profile')->get(),
            'auth' => [
                'user' => $user,
                'user_roles' => $user->roles->pluck('name'),
                'profile' => $user->profile,
            ],
            'can' => [
                'createProject' => $user->hasRole('admin') || $user->hasRole('project_manager'),
            ],
            'isAdmin' => $user->hasRole('admin'),
            'isProjectManager' => $user->hasRole('project_manager'),
        ]);
    }

    /**
     * Show the form for creating a new project.
     */
    public function create()
    {
        $user = Auth::user()->load('roles');
        
        // Vérifier si l'utilisateur a le droit de créer un projet
        if (!$user->hasAnyRole(['admin', 'project_manager'])) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('Project/Create', [
            'users' => User::with('profile')->get(),
        ]);
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user()->load('roles');
        
        // Vérifier si l'utilisateur a le droit de créer un projet
        if (!$user->hasAnyRole(['admin', 'project_manager'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'manager_id' => 'required|exists:users,id',
            'members' => 'nullable|array',
            'members.*.id' => 'required|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            // Récupérer les IDs des membres (en excluant les valeurs vides)
            $memberIds = collect($validated['members'] ?? [])
                ->pluck('id')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $project = Project::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'manager_id' => $validated['manager_id'],
                'member_ids' => $memberIds,
            ]);

            DB::commit();

            return redirect()->route('projects.index')
                ->with('success', 'Project created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create project. Please try again.');
        }
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project)
    {
        $user = Auth::user()->load('roles');
        
        // Vérifier si l'utilisateur est admin, project_manager, ou membre du projet
        $isMember = in_array($user->id, $project->member_ids ?? []) || $user->id === $project->manager_id;
        $hasAccess = $user->hasRole('admin') || $user->hasRole('project_manager') || $isMember;
        
        if (!$hasAccess) {
            abort(403, 'You do not have permission to view this project.');
        }

        // Charger le manager et son profil
        $project->load(['manager.profile']);
        
        // Charger les membres du projet depuis les IDs stockés
        $memberIds = $project->member_ids ?? [];
        $project->members = !empty($memberIds) 
            ? User::with('profile')->whereIn('id', $memberIds)->get()
            : collect();

        // Récupérer tous les utilisateurs disponibles pour l'ajout de membres (si admin ou manager du projet)
        $users = collect();
        if ($user->hasRole('admin') || $user->hasRole('project_manager')) {
            $users = User::with('profile')->whereNotIn('id', $memberIds)->get();
        }

        return Inertia::render('Project/Show', [
            'project' => $project,
            'users' => $users,
            'auth' => [
                'user' => $user,
                'user_roles' => $user->roles->pluck('name'),
                'profile' => $user->profile,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project)
    {
        $this->authorize('update', $project);

        $project->load(['manager.profile', 'members']);

        return Inertia::render('Project/Edit', [
            'project' => $project,
            'users' => User::with('profile')->get(),
        ]);
    }

    /**
     * Update the specified project in storage.
     */
    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'manager_id' => 'required|exists:users,id',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $project->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'manager_id' => $validated['manager_id'],
            ]);

            if (isset($validated['member_ids'])) {
                $project->members()->sync($validated['member_ids']);
            }

            DB::commit();

            return redirect()->route('projects.index')
                ->with('success', 'Project updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update project. Please try again.');
        }
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project)
    {
        $user = auth()->user();
        
        // Check if user is admin or the project manager
        if (!$user->hasRole('admin') && $project->manager_id !== $user->id) {
            return response()->json([
                'message' => 'You are not authorized to delete this project.'
            ], 403);
        }

        try {
            DB::beginTransaction();
            
            // Delete the project
            $project->delete();
            
            DB::commit();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Project deleted successfully.'
                ]);
            }

            return redirect()->route('projects.index')
                ->with('success', 'Project deleted successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting project: ' . $e->getMessage());
            
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete project. Please try again.'
                ], 500);
            }
            
            return back()->with('error', 'Failed to delete project. Please try again.');
        }
    }

    /**
     * Get all projects for the current user.
     */
    public function myProjects()
    {
        $user = Auth::user();
        
        $projects = Project::where('manager_id', $user->id)
            ->orWhereHas('members', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['manager.profile', 'members.profile'])
            ->get();

        return response()->json($projects);
    }
}