<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Admin Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto space-y-6 sm:px-6 lg:px-8">
                <!-- Recent Leave Requests -->
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-6">
                    <h3 class="text-lg font-bold mb-4">Recent Leave Requests</h3>
                    <ul class="divide-y divide-gray-200">
                        <li v-for="leave in leaves" :key="leave.id" class="py-3">
                            <p><strong>{{ leave.user.profile.first_name }} {{ leave.user.profile.last_name }}</strong> - {{ leave.reason }} ({{ leave.status }})</p>
                        </li>
                    </ul>
                </div>

                <!-- Team Summary -->
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-6">
                    <h3 class="text-lg font-bold mb-4">Team Summary</h3>
                    <ul class="divide-y divide-gray-200">
                        <li v-for="team in teams" :key="team.id" class="py-2">
                            {{ team.name }} ({{ team.users_count }} members)
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    leaves: Array,
    teams: Array,
});
</script>
