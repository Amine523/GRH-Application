<template>
    <div class="py-6">
      <div class="max-w-4xl mx-auto">
        <!-- Back button -->
        <Link :href="route('teams.index')" class="text-indigo-600 hover:underline">
          ← Back to Teams
        </Link>
  
        <!-- Flash Messages -->
        <div v-if="$page.props.flash && $page.props.flash.success" class="text-green-600 my-4">
          {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.flash && $page.props.flash.error" class="text-red-600 my-4">
          {{ $page.props.flash.error }}
        </div>
  
        <!-- Team Info -->
        <div class="bg-white p-4 rounded shadow mt-4">
          <h1 class="text-2xl font-bold">{{ team.team_name }}</h1>
          <p class="text-sm text-gray-600">Created on {{ formatDate(team.created_at) }}</p>
  
          <div v-if="isAdminOrPM" class="mt-4 space-x-2">
            <Link :href="route('teams.edit', team.id)" class="bg-indigo-600 text-white px-4 py-2 rounded">Edit</Link>
            <button @click="confirmDeleteTeam" class="bg-red-600 text-white px-4 py-2 rounded">Delete</button>
          </div>
        </div>
  
        <!-- Project Manager Info -->
        <div class="bg-white p-4 rounded shadow mt-6">
          <h2 class="text-lg font-semibold">Project Manager</h2>
          <p>Name: {{ team.project_manager?.profile?.first_name }} {{ team.project_manager?.profile?.last_name || 'Not assigned' }}</p>
          <!-- <p>Email: {{ team.project_manager?.email || 'Not defined' }}</p> -->
        </div>
  
        <!-- Team Members List -->
        <div class="bg-white p-4 rounded shadow mt-6">
          <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold">Team Members</h2>
            <button v-if="isAdminOrPM" @click="showAddMember = true" class="bg-indigo-600 text-white px-3 py-1 rounded">Add Member</button>
          </div>
  
          <ul class="mt-4 space-y-2">
            <li v-for="member in team.members" :key="member.id" class="flex justify-between items-center">
              <div>
                <p class="font-medium">{{ member.name }}</p>
                <p class="text-sm text-gray-500">{{ member.email }}</p>
              </div>
              <button v-if="isAdminOrPM" @click="removeTeamMember(member)" class="text-red-600 text-sm">Remove</button>
            </li>
            <li v-if="team.members.length === 0" class="text-gray-500">No members in this team yet.</li>
          </ul>
        </div>
  
        <!-- Add Member Modal -->
        <div v-if="showAddMember" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
          <div class="bg-white p-6 rounded shadow w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">Add Member</h3>
  
            <div v-for="(member, index) in form.members" :key="index" class="mb-4">
              <div class="relative">
                <div class="flex items-center space-x-2">
                  <div class="flex-1 relative">
                    <div class="relative">
                      <input
                        type="text"
                        v-model="member.searchQuery"
                        @focus="member.isOpen = true"
                        @blur="handleBlur(member)"
                        :placeholder="member.id ? '' : 'Search users...'"
                        class="w-full border border-gray-300 rounded-md py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                      >
                      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                      </div>
                      <div v-if="member.id" class="absolute inset-y-0 right-0 flex items-center pr-2">
                        <button 
                          type="button" 
                          @click.stop="member.id = ''; member.searchQuery = ''"
                          class="text-gray-400 hover:text-gray-600"
                        >
                          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                          </svg>
                        </button>
                      </div>
                    </div>
                    
                    <!-- Dropdown -->
                    <div 
                      v-show="member.isOpen" 
                      class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
                    >
                      <div v-if="filteredUsers(member).length === 0" class="px-4 py-2 text-gray-500">
                        No users found
                      </div>
                      <div 
                        v-else
                        v-for="user in filteredUsers(member)" 
                        :key="user.id"
                        @click="selectUser(member, user)"
                        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
                      >
                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-medium">
                          {{ getUserInitials(user) }}
                        </div>
                        <div class="ml-3">
                          <div class="font-medium text-gray-900">
                            {{ user.profile?.first_name }} {{ user.profile?.last_name }}
                          </div>
                          <div class="text-xs text-gray-500">
                            {{ user.email }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <button 
                    v-if="form.members.length > 1" 
                    @click="removeMember(index)" 
                    type="button"
                    class="p-2 text-red-600 hover:text-red-800 focus:outline-none"
                    :disabled="form.processing"
                  >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span class="sr-only">Remove</span>
                  </button>
                </div>
                
             <!-- Selected User Badge -->
<div 
  v-if="selectedUser(member)" 
  class="mt-3 flex items-center p-2 bg-gray-50 rounded-lg shadow-sm border border-gray-200"
>
  <!-- Avatar -->
  <!-- <div class="flex-shrink-0 h-9 w-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold">
    {{ getUserInitials(selectedUser(member)) }}
  </div> -->

  <!-- Infos utilisateur -->
  <div class="ml-3 flex-1">
    <div class="text-sm font-medium text-gray-900">
      {{ selectedUser(member)?.profile?.first_name }} {{ selectedUser(member)?.profile?.last_name }}
    </div>
    <div class="text-xs text-gray-500">
      {{ selectedUser(member)?.email }}
    </div>
  </div>

  <!-- Bouton retirer -->
  <button 
    type="button"
    @click="member.user_id = null"
    class="ml-3 inline-flex items-center px-2 py-1 text-xs text-gray-500 hover:text-red-600 transition"
    title="Remove user"
  >
    ✕
  </button>
</div>
</div>

            </div>
  
            <button @click="addMember" class="text-sm text-indigo-600 mb-4">+ Add another</button>
  
            <div class="flex justify-end space-x-2">
              <button @click="saveMembers" class="bg-indigo-600 text-white px-4 py-2 rounded">Save</button>
              <button @click="showAddMember = false" class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed } from 'vue';
  import { useForm, Link, router } from '@inertiajs/vue3';
  
  const props = defineProps({
    team: Object,
    users: Array,
    auth: Object
  });
  
  const form = useForm({
    members: [{ id: '', searchQuery: '', isOpen: false }],
  });

  const showAddMember = ref(false);
  const processing = ref(false);

  // Get user initials for avatar
  const getUserInitials = (user) => {
    if (!user?.profile) return '??';
    const { first_name, last_name } = user.profile;
    return [first_name?.[0], last_name?.[0]].filter(Boolean).join('').toUpperCase() || '??';
  };

  // Filter users based on search query
  const filteredUsers = (member) => {
    if (!member.searchQuery) return availableUsers.value;
    
    const query = member.searchQuery.toLowerCase();
    return availableUsers.value.filter(user => {
      const name = `${user.profile?.first_name || ''} ${user.profile?.last_name || ''}`.toLowerCase();
      return name.includes(query) || user.email.toLowerCase().includes(query);
    });
  };

  // Handle user selection
  const selectUser = (member, user) => {
    member.id = user.id;
    member.searchQuery = `${user.profile?.first_name} ${user.profile?.last_name}`;
    member.isOpen = false;
  };

  // Get selected user data
  const selectedUser = (member) => {
    if (!member.id) return null;
    return availableUsers.value.find(u => u.id === member.id) || { profile: {} };
  };
  
  const isAdminOrPM = computed(() => {
    const roles = props.auth.user_roles || [];
    return roles.includes('admin') || roles.includes('project_manager');
  });
  
  const availableUsers = computed(() => {
    const memberIds = props.team.members.map(m => m.id);
    const selectedIds = form.members.map(m => m.id);
    return props.users.filter(u => !memberIds.includes(u.id) && !selectedIds.includes(u.id));
  });
  
  function formatDate(date) {
    return new Date(date).toLocaleDateString();
  }
  
  function addMember() {
    form.members.push({ id: '' });
  }
  
  function removeMember(index) {
    form.members.splice(index, 1);
  }
  
  function saveMembers() {
    processing.value = true;
    const user_ids = form.members.map(m => m.id).filter(id => id);
  
    router.post(route('teams.add-member', props.team.id), { user_ids }, {
      onSuccess: () => {
        showAddMember.value = false;
        form.members = [{ id: '' }];
        processing.value = false;
      },
      onError: () => {
        processing.value = false;
      }
    });
  }
  
  function removeTeamMember(member) {
    if (confirm('Remove this member?')) {
      router.delete(route('teams.remove-member', { team: props.team.id, user: member.id }), {
        onSuccess: () => {
          // Reload team
          router.visit(route('teams.show', props.team.id), {
            only: ['team'],
            preserveState: true,
            preserveScroll: true
          });
        }
      });
    }
  }
  
  function handleBlur(member) {
    setTimeout(() => {
      member.isOpen = false;
    }, 200);
  }

  function confirmDeleteTeam() {
    if (confirm('Are you sure you want to delete this team?')) {
      router.delete(route('teams.destroy', props.team.id), {
        onSuccess: () => router.visit(route('teams.index'))
      });
    }
  }
  </script>
  
