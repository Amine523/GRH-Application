<template>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
            v-for="project in projects" 
            :key="project.id" 
            class="border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow transition relative"
        >
            <div class="flex justify-between items-start mb-2">
                <h4 class="text-lg font-semibold text-gray-800">{{ project.name }}</h4>
                <span :class="[
                    'px-2 py-1 text-xs font-semibold rounded-full',
                    project.status === 'in_progress' ? 'bg-blue-100 text-blue-800' :
                    project.status === 'completed' ? 'bg-green-100 text-green-800' :
                    project.status === 'on_hold' ? 'bg-yellow-100 text-yellow-800' :
                    project.status === 'cancelled' ? 'bg-red-100 text-red-800' :
                    'bg-gray-100 text-gray-800'
                ]">                                                                                                                                                                       
                    {{ project.status_label || 'Inconnu' }}
                </span>
            </div>
            
            <div class="space-y-2 text-sm text-gray-600 mb-4">
                <div class="flex items-center">
                    <svg class="h-4 w-4 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>{{ project.manager?.profile?.first_name }} {{ project.manager?.profile?.last_name }}</span>
                </div>
                <div class="flex items-center">
                    <svg class="h-4 w-4 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>{{ project.members_count || 0 }} membre(s)</span>
                </div>
                <div class="flex items-center">
                    <svg class="h-4 w-4 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Du {{ formatDate(project.start_date) }} au {{ formatDate(project.end_date) }}</span>
                </div>
            </div>
            
            <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                <div class="flex space-x-2">
                    <Link 
                        :href="route('projects.show', project.id)" 
                        class="text-sm bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-md transition flex items-center"
                    >
                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Voir
                    </Link>
                    <Link 
                        v-if="isAdmin || auth.user.id === project.manager_id"
                        :href="route('projects.edit', project.id)" 
                        class="text-sm bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-md transition flex items-center"
                    >
                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Modifier
                    </Link>
                </div>
                <span class="text-xs text-gray-500">
                    Créé le {{ formatDate(project.created_at) }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { fr } from 'date-fns/locale';

defineProps({
    projects: {
        type: Array,
        required: true
    },
    isAdmin: {
        type: Boolean,
        default: false
    },
    auth: {
        type: Object,
        required: true,
        default: () => ({})
    }
});

const formatDate = (dateString) => {
    if (!dateString) return 'Non défini';
    return format(new Date(dateString), 'dd/MM/yyyy', { locale: fr });
};
</script>
