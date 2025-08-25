<template>
    <Head title="Teams" />

    <AuthenticatedLayout :auth="auth">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Teams List</h2>
        </template>

        <div class="py-10">
            <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- TEAMS LIST -->
                <div class="bg-white shadow rounded-xl p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ isAdmin ? 'All Teams' : isProjectManager ? 'My Managed Teams' : 'My Team' }}
                        </h3>
                        <!-- <PrimaryButton 
                            v-if="(isAdmin || isProjectManager) && can.createTeam"
                            @click="addTeam" 
                            class="bg-green-600 text-white hover:bg-green-700"
                        >
                            Add New Team
                        </PrimaryButton> -->
                    </div>

                    <template v-if="filteredTeams.length > 0">
                        <TeamList 
                            v-if="isAdmin || isProjectManager"
                            :teams="filteredTeams" 
                            :is-admin="isAdmin"
                            :users="users"
                            :auth="auth"
                        />
                        
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div 
                                v-for="team in filteredTeams" 
                                :key="team.id" 
                                class="border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow transition"
                            >
                                <h4 class="text-md font-semibold text-gray-800 mb-1">{{ team.team_name }}</h4>
                                <p class="text-sm text-gray-500 mb-2">
                                    Project Manager: 
                                    <span class="text-gray-700 font-medium">
                                        {{ team.project_manager?.profile?.first_name }} {{ team.project_manager?.profile?.last_name }}
                                    </span>
                                </p>
                                <Link 
                                    :href="route('teams.show', team.id)" 
                                    class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                                >
                                    View
                                </Link>
                            </div>
                        </div>
                    </template>
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
        default: () => ([]),
    },
    users: {
        type: Array,
        default: () => ([]),
    },
    auth: {
        type: Object,
        required: true,
        default: () => ({
            user: null,
            roles: [],
            profile: null,
        }),
    },
    can: {
        type: Object,
        default: () => ({
            createTeam: false,
        }),
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
const userRoles = computed(() => props.auth.roles || []);
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

