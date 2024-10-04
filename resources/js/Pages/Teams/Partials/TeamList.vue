<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Team List</h2>
            <p class="mt-1 text-sm text-gray-600">Manage the list of teams, edit or delete their information.</p>
        </header>

        <div v-for="team in teams" :key="team.id" class="mt-3 space-y-3 p-4 border rounded-md shadow-md">
            <div class="flex space-x-4">
                <div class="w-1/2">
                    <p class="text-sm font-medium text-gray-700">Team Name</p>
                    <p class="text-lg">{{ team?.team_name }}</p>
                </div>
                <div class="w-1/2">
                    <p class="text-sm font-medium text-gray-700">Project Manager Name</p>
                    <p class="text-lg">{{ team?.project_manager?.profile?.first_name }} {{ team?.project_manager?.profile?.last_name }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <!-- Show edit and delete buttons only if the user is an admin -->
                <PrimaryButton v-if="isAdmin" @click="editTeam(team.id)" class="bg-#082f49 text-white">
                    Edit
                </PrimaryButton>

                <PrimaryButton v-if="isAdmin" @click="deleteTeam(team.id)" class="bg-red-500 text-white">
                    Delete
                </PrimaryButton>
            </div>
        </div>
    </section>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useToast } from "vue-toastification";
import "vue-toastification/dist/index.css";

const props = defineProps({
    teams: Array,
    isAdmin: Boolean, // Accept isAdmin as a prop
});

const toast = useToast();

const deleteTeam = (id) => {
    if (confirm('Are you sure you want to delete this team?')) {
        router.delete(`/teams/${id}`, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Team deleted successfully!');
            },
            onError: () => {
                toast.error('There was an error deleting the team.');
            }
        });
    }
};

const editTeam = (id) => {
    router.get(`/teams/${id}/edit`);
};

// Function to create a new team
const createTeam = () => {
    router.get(`/teams/create`); // Redirect to the create team page
};
</script>
