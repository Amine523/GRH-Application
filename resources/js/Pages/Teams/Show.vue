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
          <p>Name: {{ team.project_manager?.name || 'Not defined' }}</p>
          <p>Email: {{ team.project_manager?.email || 'Not defined' }}</p>
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
  
            <div v-for="(member, index) in form.members" :key="index" class="mb-3">
              <select v-model="member.id" class="w-full border px-3 py-2 rounded">
                <option value="">Select a user</option>
                <option v-for="user in availableUsers" :key="user.id" :value="user.id">
                  {{ user.profile?.first_name }} {{ user.profile?.last_name }} ({{ user.email }})
                </option>
              </select>
              <button v-if="form.members.length > 1" @click="removeMember(index)" class="text-sm text-red-600 mt-1">Remove</button>
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
  
  const form = useForm({ members: [{ id: '' }] });
  const showAddMember = ref(false);
  const processing = ref(false);
  
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
    
    if (user_ids.length === 0) {
      processing.value = false;
      return;
    }
    
    router.post(route('teams.add-member', props.team.id), { 
      user_ids: user_ids 
    }, {
      onSuccess: () => {
        showAddMember.value = false;
        form.members = [{ id: '' }];
        processing.value = false;
        // Recharger la page pour afficher les nouveaux membres
        router.visit(route('teams.show', props.team.id), {
          only: ['team'],
          preserveState: true,
          preserveScroll: true
        });
      },
      onError: (errors) => {
        processing.value = false;
        console.error('Error adding members:', errors);
      }
    });
  }
  
  function removeTeamMember(member) {
    if (confirm('Are you sure you want to remove ' + member.name + ' from this team?')) {
      router.delete(route('teams.remove-member', { 
        team: props.team.id, 
        user: member.id 
      }), {
        onSuccess: () => {
          // Recharger la page pour refléter les changements
          router.visit(route('teams.show', props.team.id), {
            only: ['team'],
            preserveState: true,
            preserveScroll: true
          });
        },
        onError: (errors) => {
          console.error('Error removing member:', errors);
        }
      });
    }
  }
  
  function confirmDeleteTeam() {
    if (confirm('Are you sure you want to delete this team?')) {
      router.delete(route('teams.destroy', props.team.id), {
        onSuccess: () => router.visit(route('teams.index'))
      });
    }
  }
  </script>
  
