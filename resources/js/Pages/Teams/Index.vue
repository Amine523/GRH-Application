<template>
    <Head title="Profile"/>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Teams List</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <div class="flex justify-end">
                        <!-- Show the Add New Team button only if the user is an admin -->
                        <div v-if="isAdmin" class="mb-4">
                            <PrimaryButton @click="addTeam" class="bg-green-600 text-white">
                                Add New Team
                            </PrimaryButton>
                        </div>
                    </div>
                    <TeamList :teams="teams" :isAdmin="isAdmin"></TeamList> <!-- Pass isAdmin to TeamList if needed -->
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TeamList from "@/Pages/Teams/Partials/TeamList.vue";

const props = defineProps({
    teams: {
        type: Object,
    },
    auth: Object,
});

const isAdmin = props.auth.user.roles.some(role => role.name === 'admin');

const addTeam = () => {
    router.get(route('teams.create'));
};
</script>
