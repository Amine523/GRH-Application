<template>
    <Head title="Teams" />

    <AuthenticatedLayout :auth="auth">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Teams List</h2>
        </template>

        <div class="py-10">
            <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- ADMIN VIEW -->
                <div v-if="isAdmin" class="bg-white shadow rounded-xl p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">All Teams</h3>
                        <PrimaryButton 
                            v-if="can.createTeam"
                            @click="addTeam" 
                            class="bg-green-600 text-white hover:bg-green-700"
                        >
                            Add New Team
                        </PrimaryButton>
                    </div>

                    <div v-if="filteredTeams.length > 0">
                        <TeamList 
                            :teams="filteredTeams" 
                            :is-admin="isAdmin"
                            :users="users"
                            :auth="auth"
                        />
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">No teams found.</div>
                </div>

                <!-- PROJECT MANAGER VIEW -->
                <div v-else-if="isProjectManager" class="bg-white shadow rounded-xl p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">My Managed Teams</h3>
                        <PrimaryButton 
                            v-if="can.createTeam"
                            @click="addTeam" 
                            class="bg-green-600 text-white hover:bg-green-700"
                        >
                            Add New Team
                        </PrimaryButton>
                    </div>

                    <div v-if="filteredTeams.length > 0">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div 
                                v-for="team in filteredTeams" 
                                :key="team.id" 
                                class="border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow transition relative"
                            >
                                <h4 class="text-md font-semibold text-gray-800 mb-1">{{ team.team_name }}</h4>
                                <p class="text-sm text-gray-500 mb-2">
                                    Members: {{ team.employees_count }}
                                </p>
                                <p class="text-sm text-gray-500 mb-3">
                                    Status: <span class="text-green-600 font-medium">Active</span>
                                </p>
                                
                                <div class="flex items-center space-x-3 mt-4">
                                    <!-- <Link 
                                        :href="route('teams.show', team.id)" 
                                        class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                                    >
                                        View
                                    </Link> -->
                                    <Link 
                                        v-if="team.project_manager_id === auth.user.id || isAdmin"
                                        :href="route('teams.edit', team.id)" 
                                        class="text-yellow-600 hover:text-yellow-900 text-sm font-medium"
                                    >
                                        Edit
                                    </Link>
                                    <!-- <button 
                                        v-if="team.project_manager_id === auth.user.id || isAdmin"
                                        @click="deleteTeam(team.id)" 
                                        class="text-red-600 hover:text-red-900 text-sm font-medium"
                                    >
                                        Delete
                                    </button> -->
                                </div>
                                
                                <!-- Add New Member Button -->
                                <div class="mt-4 pt-3 border-t border-gray-100 flex justify-end">
                                    <Link 
                                        v-if="team.project_manager_id === auth.user.id || isAdmin"
                                        :href="route('teams.show', team.id)" 
                                        class="text-sm bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-md transition"
                                    >
                                        Add Member
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">No teams found.</div>
                </div>

                <!-- USER VIEW -->
                <div v-else class="bg-white shadow rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">My Team</h3>
                    <div v-if="filteredTeams.length > 0">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div 
                                v-for="team in filteredTeams" 
                                :key="team.id" 
                                class="border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow transition"
                            >
                                <!-- <h4 class="text-md font-semibold text-gray-800 mb-1">{{ team.team_name }}</h4>
                                <p class="text-sm text-gray-500 mb-2">
                                    Project Manager: 
                                    <span class="text-gray-700 font-medium">
                                        {{ team.project_manager?.profile?.full_name ?? 'N/A' }}
                                    </span>
                                </p>
                                <p class="text-sm text-gray-500 mb-3">
                                    Members: {{ team.employees_count }}
                                </p> -->
                                <Link 
                                    :href="route('teams.show', team.id)" 
                                    class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                                >
                                    View
                                </Link>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">No teams found.</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TeamList from './Partials/TeamList.vue';
import { computed, ref, onMounted } from 'vue';

const props = defineProps({
    teams: {
        type: Array,
        required: true,
    },
    users: {
        type: Array,
        default: () => [],
    },
    auth: {
        type: Object,
        required: true,
        default: () => ({
            user: {},
            user_roles: [],
            profile: null,
            valid_balance: 0
        })
    },
    can: {
        type: Object,
        default: () => ({}),
    },
    isAdmin: {
        type: Boolean,
        default: false,
    },
    isProjectManager: {
        type: Boolean,
        default: false,
    },
});

// Ensure we have the user roles properly set
const userRoles = computed(() => props.auth.user_roles || []);
const isAdmin = computed(() => props.isAdmin || userRoles.value.includes('admin'));
const isProjectManager = computed(() => props.isProjectManager || 
    userRoles.value.includes('project_manager') || 
    userRoles.value.includes('project manager'));

const filteredTeams = computed(() => {
    if (!props.teams) return [];
    
    if (isAdmin.value) {
        return props.teams;
    } else if (isProjectManager.value) {
        return props.teams.filter(team => team.project_manager_id === props.auth.user?.id);
    } else {
        // For regular users, show teams they are a member of
        return props.teams.filter(team => 
            team.employee_ids && team.employee_ids.includes(props.auth.user?.id)
        );
    }
});

const addTeam = () => {
    router.get(route('teams.create'));
};

const editTeam = (teamId) => {
    router.get(route('teams.edit', teamId));
};

</script>

<!-- <!-- <style scoped>
.transition {
    transition: all 0.2s ease-in-out;
} -->

<!-- /* .hover\:shadow:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
} */ 
</style> -->
