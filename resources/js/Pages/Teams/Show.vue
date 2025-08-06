<template>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Back button -->
                    <Link 
                        :href="route('teams.index')" 
                        class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors duration-200 mb-6 group"
                    >
                        <svg class="w-4 h-4 mr-1 transform group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Teams
                    </Link>

                    <!-- Flash Messages -->
                    <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-50 border-l-4 border-green-400 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700">
                                    {{ $page.props.flash.success }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div v-if="$page.props.flash && $page.props.flash.error" class="mb-4 p-4 bg-red-50 border-l-4 border-red-400 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">
                                    {{ $page.props.flash.error }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Team Info -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ team.team_name }}</h1>
                            <p class="text-gray-600 mt-1">
                                Created on {{ new Date(team.created_at).toLocaleDateString() }}
                            </p>
                        </div>
                        
                        <!-- Team Actions -->
                        <div v-if="isAdminOrPM" class="mt-4 flex space-x-3 md:mt-0">
                            <Link 
                                :href="route('teams.edit', team.id)" 
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit
                            </Link>

                            <button
                                @click="confirmDeleteTeam"
                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                            >
                                <!-- <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg> -->
                                Delete
                            </button>
                        </div>
                    </div>

                    <!-- Project Manager -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
                        <div class="px-4 py-5 sm:px-6 bg-gray-50">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Project Manager
                            </h3>
                        </div>
                        <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                            <dl class="sm:divide-y sm:divide-gray-200">
                                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">
                                        Name
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        {{ team.project_manager?.name || 'Not defined' }}
                                    </dd>
                                </div>
                                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">
                                        Email
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        {{ team.project_manager?.email || 'Not defined' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Team Members -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg mt-6">
                        <div class="px-4 py-5 sm:px-6 bg-gray-50 flex justify-between items-center">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Team Members
                            </h3>
                            <button
                                v-if="isAdminOrPM"
                                @click="showAddMember = true"
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Member
                            </button>
                        </div>
                        
                        <div class="bg-white overflow-hidden">
                            <ul class="divide-y divide-gray-200">
                                <li v-for="member in team.members" :key="member.id" class="px-4 py-4 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full" :src="member.profile?.profile_photo_url || 'https://ui-avatars.com/api/?name=' + member.name" :alt="member.name">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ member.name }}
                                                    <span v-if="member.is_project_manager" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                        Project Manager
                                                    </span>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ member.email }}
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="isAdminOrPM" class="ml-4">
                                            <button 
                                                @click="removeMember(member)"
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
                                                :disabled="processingUserId === member.id"
                                                :class="{'opacity-50 cursor-not-allowed': processingUserId === member.id}"
                                            >
                                                <svg v-if="processingUserId === member.id" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span v-else class="flex items-center">
                                                    <!-- <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg> -->
                                                    Remove Member
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </li>
                                <li v-if="team.members.length === 0" class="px-4 py-6 text-center text-gray-500">
                                    No members in this team yet.
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Add Member Modal -->
                    <div v-if="showAddMember" class="fixed inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showAddMember = false"></div>

                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                                <div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                            Add a Member
                                        </h3>
                                        <div class="mt-4">
                                            <select 
                                                v-model="newMemberId"
                                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                                :disabled="processingUserId !== null"
                                            >
                                                <option value="">Select a member</option>
                                                <option v-for="user in availableUsers" :key="user.id" :value="user.id">
                                                    {{ user.profile?.first_name + ' ' + user.profile?.last_name || user.email }}
                                                </option>
                                            </select>
                                            <InputError :message="errors.user_id" class="mt-2" />
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                    <button 
                                        type="button" 
                                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
                                        :disabled="!newMemberId || processingUserId !== null"
                                        @click="addTeamMember"
                                    >
                                        <span v-if="processingUserId">Adding...</span>
                                        <span v-else>Add</span>
                                    </button>
                                    <button 
                                        type="button" 
                                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
                                        @click="showAddMember = false"
                                        :disabled="processingUserId !== null"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    team: {
        type: Object,
        required: true,
        default: () => ({
            id: null,
            team_name: '',
            members: [],
            created_at: null,
            project_manager: null
        })
    },
    users: {
        type: Array,
        default: () => []
    },
    auth: {
        type: Object,
        required: true,
        default: () => ({
            user: {},
            user_roles: []
        })
    },
    errors: {
        type: Object,
        default: () => ({})
    }
});

const showAddMember = ref(false);
const newMemberId = ref('');
const processingUserId = ref(null);

// Computed property to check if user is admin or project manager
const isAdminOrPM = computed(() => {
    const roles = props.auth.user_roles || [];
    return roles.includes('admin') || roles.includes('project_manager');
});

// Computed property to check if user is the project manager of this team
const isTeamManager = computed(() => {
    return props.team.project_manager_id === props.auth.user?.id;
});

const availableUsers = computed(() => {
    const memberIds = props.team.members.map(member => member.id);
    return props.users.filter(user => !memberIds.includes(user.id));
});

const removeMember = (member) => {
    if (confirm('Are you sure you want to remove this member from the team?')) {
        processingUserId.value = member.id;
        
        router.delete(route('teams.remove-member', {
            team: props.team.id,
            user: member.id
        }), {
            preserveScroll: true,
            onSuccess: () => {
                processingUserId.value = null;
            },
            onError: () => {
                processingUserId.value = null;
            }
        });
    }
};
const addTeamMember= () => {
  form.members.push({ id: '' });
};

// const addTeamMember = () => {
//     if (!newMemberId.value) return;
    
//     processingUserId.value = 'adding';
    
//     router.post(route('teams.members.add', props.team.id), {
//         user_id: newMemberId.value
//     }, {
//         preserveScroll: true,
//         onSuccess: () => {
//             showAddMember.value = false;
//             newMemberId.value = '';
//             processingUserId.value = null;
//         },
//         onError: () => {
//             processingUserId.value = null;
//         }
//     });
// };

const confirmDeleteTeam = () => {
    if (confirm('Are you sure you want to delete this team? This action cannot be undone.')) {
        router.delete(route('teams.destroy', props.team.id), {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(route('teams.index'));
            }
        });
    }
};
</script>
