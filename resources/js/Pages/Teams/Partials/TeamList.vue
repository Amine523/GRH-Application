<template>
    <section>
        <header class="mb-6">
            <h2 class="text-lg font-medium text-gray-900">Team List</h2>
        </header>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="team in teams" :key="team.id" class="flex flex-col justify-center items-center p-6 border rounded-lg shadow-lg bg-white space-y-4 text-center">
                <div class="w-full mb-4">
                    <p class="text-sm font-semibold text-gray-600">Team Name</p>
                    <p class="text-xl font-bold text-gray-800">{{ team?.team_name }}</p>
                </div>
                <div class="w-full mb-4">
                    <p class="text-sm font-semibold text-gray-600">Project Manager Name</p>
                    <p class="text-lg text-gray-800">
                        {{ team?.project_manager?.first_name }} {{ team?.project_manager?.last_name }}
                    </p>
                </div>
                <div class="w-full mb-4">
                    <p class="text-sm font-semibold text-gray-600">Team Members with Valid Balance</p>
                    <div v-for="member in team.members" :key="member.id">
                        {{ member.first_name }}    {{ member.last_name }} 	&#10132; {{ member.valid_balance }}
                    </div>
                </div>
                <div class="flex justify-center items-center gap-4 mt-4">
                    <!-- Display buttons for admin only -->
                    <PrimaryButton v-if="isAdmin" @click="editTeam(team.id)" class="px-4 py-2 bg-#082f49 text-white rounded-md">
                        Edit
                    </PrimaryButton>

                    <PrimaryButton v-if="isAdmin" @click="deleteTeam(team.id)" class="px-4 py-2 bg-red-500 text-white rounded-md">
                        Delete
                    </PrimaryButton>
                </div>
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
