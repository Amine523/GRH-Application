<template>
    <section>
        <header class="mb-6 flex justify-between items-center">
            <h2 class="text-lg font-medium text-gray-900">Team List</h2>
            <Link 
                v-if="isAdmin || isProjectManager" 
                :href="route('teams.create')" 
                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition"
            >
                Add New Team
            </Link>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div 
                v-for="team in teams" 
                :key="team.id" 
                class="flex flex-col p-6 border rounded-lg shadow-lg bg-white space-y-4"
            >
                <div class="mb-4">
                    <p class="text-sm font-semibold text-gray-600">Team Name</p>
                    <p class="text-xl font-bold text-gray-800">{{ team.team_name }}</p>
                </div>
                
                <div class="mb-4">
                    <p class="text-sm font-semibold text-gray-600">Project Manager</p>
                    <p class="text-lg text-gray-800">
                        {{ team.project_manager?.profile?.first_name }} {{ team.project_manager?.profile?.last_name }}
                    </p>
                </div>
                
                <div class="mb-4">
                    <p class="text-sm font-semibold text-gray-600 mb-2">Team Members</p>
                    <div 
                        v-for="member in team.employees" 
                        :key="member.id" 
                        class="flex justify-between items-center py-1"
                    >
                        <span>
                            {{ member.profile?.first_name }} {{ member.profile?.last_name }} 
                            <span v-if="member.valid_balance !== undefined" class="text-gray-500">({{ member.valid_balance }})</span>
                        </span>
                    </div>
                </div>
                
                <div class="flex flex-wrap gap-2 mt-auto">
                    <Link 
                        v-if="isAdmin || team.project_manager_id === auth.user.id"
                        :href="route('teams.show', team.id)"
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition flex items-center justify-center"
                        :title="isAdmin ? 'View and manage team members' : 'View team details'"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>Manage Team</span>
                    </Link>
                    
                    <Link 
                        v-if="isAdmin"
                        :href="route('teams.edit', team.id)" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition"
                    >
                        Edit
                    </Link>
                    
                    <button
                        v-if="isAdmin"
                        @click="confirmDelete(team.id, team.team_name)" 
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition"
                        :disabled="deleteInProgress"
                    >
                        <span v-if="deleteInProgress">Deleting...</span>
                        <span v-else>Delete</span>
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({
    teams: {
        type: Array,
        required: true,
    },
    auth: {
        type: Object,
        required: true,
    },
    users: {
        type: Array,
        default: () => [],
    },
    isAdmin: {
        type: Boolean,
        default: false,
    },
});

const deleteInProgress = ref(false);

const isProjectManager = props.auth.roles?.includes('project_manager') || false;

const confirmDelete = (teamId, teamName) => {
    if (confirm(`Are you sure you want to delete the team "${teamName}"? This action cannot be undone.`)) {
        deleteTeam(teamId);
    }
};

const deleteTeam = (teamId) => {
    if (!teamId) return;
    
    deleteInProgress.value = true;
    
    router.delete(route('teams.destroy', teamId), {
        preserveScroll: true,
        onSuccess: () => {
            deleteInProgress.value = false;
        },
        onError: () => {
            deleteInProgress.value = false;
            alert('Failed to delete team. Please try again.');
        }
    });
};

const removeMember = (team, memberId) => {
    if (!confirm('Are you sure you want to remove this member from the team?')) {
        return;
    }
    
    router.delete(route('teams.members.remove', { 
        team: team.id, 
        member: memberId 
    }), {
        preserveScroll: true,
        onError: () => {
            alert('Failed to remove member. Please try again.');
        }
    });
};
</script>