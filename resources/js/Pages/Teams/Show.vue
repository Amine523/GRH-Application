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
                                                @click="removeTeamMember(member)"
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
                                                :disabled="processingUserId === member.id"
                                                :class="{'opacity-50 cursor-not-allowed': processingUserId === member.id}"
                                            >
                                                <svg v-if="processingUserId === member.id" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span v-else>Remove Member</span>
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
                                        <div class="mt-4 space-y-4">
                                            <div v-for="(member, index) in form.members" :key="index" class="flex items-center space-x-2">
                                                <select 
                                                    v-model="member.id"
                                                    class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                    :class="{ 'border-red-500': form.errors[`members.${index}.id`] }"
                                                >
                                                    <option value="">Select a team member</option>
                                                    <option 
                                                        v-for="user in availableUsers" 
                                                        :key="user.id" 
                                                        :value="user.id"
                                                        :disabled="isUserSelected(user.id, index)"
                                                    >
                                                        {{ user.profile?.first_name }} {{ user.profile?.last_name }} ({{ user.email }})
                                                    </option>
                                                </select>
                                                <button 
                                                    v-if="form.members.length > 1"
                                                    type="button"
                                                    @click="removeMember(index)"
                                                    class="text-red-600 hover:text-red-800 p-2"
                                                >
                                                    remove
                                                </button>
                                                <InputError :message="form.errors[`members.${index}.id`]" class="mt-1" />
                                            </div>
                                            <button 
                                                type="button"
                                                @click="addMember"
                                                class="mt-2 text-sm text-indigo-600 hover:text-indigo-900 flex items-center"
                                            >
                                                Add another team member
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                    <button
                                        type="button"
                                        @click="saveMembers"
                                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm"
                                        :disabled="processingUserId !== null"
                                    >
                                        <span v-if="processingUserId === null">Save Members</span>
                                        <svg v-else class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        @click="showAddMember = false"
                                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm"
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

const props = defineProps({
    team: {
        type: Object,
        required: true,
        default: () => ({
            id: null,
            team_name: '',
            members: [],
            created_at: null,
            project_manager: null,
            project_manager_id: null
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

const form = useForm({
  name: '',
  members: [{ id: '' }],
  project_manager_id: props.auth.user.id
});

const showAddMember = ref(false);
const processingUserId = ref(null);

const isAdminOrPM = computed(() => {
    const roles = props.auth.user_roles || [];
    return roles.includes('admin') || roles.includes('project_manager');
});

const availableUsers = computed(() => {
    const currentTeamMemberIds = props.team.members.map(member => member.id.toString());
    const selectedIds = form.members.map(m => m.id).filter(id => id !== '');
    return props.users.filter(user => 
        !currentTeamMemberIds.includes(user.id.toString()) &&
        !selectedIds.includes(user.id.toString()) &&
        user.id.toString() !== (props.team.project_manager_id || '').toString()
    ).sort((a, b) => {
        const aName = (a.profile?.first_name || '') + ' ' + (a.profile?.last_name || '');
        const bName = (b.profile?.first_name || '') + ' ' + (b.profile?.last_name || '');
        return aName.localeCompare(bName);
    });
});

const isUserSelected = (userId, currentIndex) => {
  return form.members.some((member, index) => 
    member.id === userId.toString() && index !== currentIndex
  );
};

const addMember = () => {
  form.members.push({ id: '' });
};

const removeMember = (index) => {
    if (form.members.length > 1) {
        form.members.splice(index, 1);
    } else {
        form.members[0].id = '';
    }
    form.clearErrors(`members.${index}.id`);
};

const saveMembers = async () => {
    processingUserId.value = 'saving';
    const memberIds = form.members
        .map(m => m.id)
        .filter(id => id !== '');
        
    if (memberIds.length === 0) {
        form.setError('members', 'Please select at least one member.');
        processingUserId.value = null;
        return;
    }
    
    try {
        await router.post(route('teams.add-member', props.team.id), { 
            user_ids: memberIds 
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // Refresh the page to show the updated members list
                router.reload({ only: ['team'] });
                showAddMember.value = false;
                form.members = [{ id: '' }];
            },
            onError: (errors) => {
                if (errors.message) {
                    form.setError('members', errors.message);
                }
            },
            onFinish: () => {
                processingUserId.value = null;
            }
        });
    } catch (e) {
        console.error('Error adding members:', e);
        processingUserId.value = null;
    }
};

const confirmDeleteTeam = () => {
    if (confirm('Are you sure you want to delete this team? This action cannot be undone.')) {
        router.delete(route('teams.destroy', props.team.id), {
            // onFinish: () => {
            //     // Optionally handle after delete
            // }
        });
    }
};

const removeTeamMember = (member) => {
    if (confirm(`Remove ${member.name} from this team?`)) {
        processingUserId.value = member.id;
        router.delete(route('teams.remove-member', [props.team.id, member.id]), {
            preserveScroll: true,
            onFinish: () => {
                processingUserId.value = null;
            }
        });
    }
};
</script>
