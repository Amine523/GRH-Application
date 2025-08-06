<template>
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    {{ team.team_name }}
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    {{ team.employees_count }} member{{ team.employees_count !== 1 ? 's' : '' }}
                    <span v-if="team.project_manager" class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                        Manager: {{ team.project_manager.name || team.project_manager.email }}
                    </span>
                </p>
            </div>
            <div v-if="isAdmin || isProjectManager" class="flex space-x-2">
                <Link 
                    :href="route('teams.edit', team.id)" 
                    class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                >
                    Edit
                </Link>
            </div>
        </div>

        <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
            <h4 class="text-sm font-medium text-gray-700 mb-3">Team Members</h4>
            
            <div v-if="team.employees && team.employees.length > 0" class="space-y-3">
                <div 
                    v-for="employee in team.employees" 
                    :key="employee.id"
                    class="flex items-center justify-between p-2 rounded-md hover:bg-gray-50"
                >
                    <div class="flex items-center space-x-3">
                        <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-medium">
                            {{ employee.name?.charAt(0).toUpperCase() || employee.email?.charAt(0).toUpperCase() || 'U' }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                {{ employee.name || employee.email }}
                                <span v-if="employee.id === team.project_manager_id" class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">
                                    Manager
                                </span>
                            </p>
                            <p class="text-xs text-gray-500">{{ employee.email }}</p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-500">
                        {{ employee.pivot?.role || 'Member' }}
                    </span>
                </div>
            </div>
            
            <div v-else class="text-center py-4 text-sm text-gray-500">
                No team members yet.
            </div>

            <!-- Pending leave requests badge -->
            <div v-if="team.pending_leave_requests && team.pending_leave_requests.length > 0" class="mt-4 pt-4 border-t">
                <Link 
                    :href="route('teams.show', team.id)" 
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 hover:bg-yellow-200"
                >
                    <ExclamationCircleIcon class="-ml-0.5 mr-1.5 h-4 w-4" />
                    {{ team.pending_leave_requests.length }} pending request{{ team.pending_leave_requests.length !== 1 ? 's' : '' }}
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { ExclamationCircleIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    team: {
        type: Object,
        required: true
    },
    isAdmin: {
        type: Boolean,
        default: false
    },
    isProjectManager: {
        type: Boolean,
        default: false
    }
});
</script>
