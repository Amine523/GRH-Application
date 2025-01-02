<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                {{ team ? 'Edit Team' : 'Create New Team' }}
            </h2>
        </header>

        <form @submit.prevent="createTeam" class="mt-3 space-y-3">
            <div class="grid grid-cols-3 gap-4">
                <!-- Team Name Field -->
                <div>
                    <InputLabel for="team_name" value="Team Name"/>

                    <TextInput
                        id="team_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.team_name"
                        autofocus
                        autocomplete="off"
                    />
                    <InputError class="mt-2" :message="form.errors.team_name"/>
                </div>

                <!-- Project Manager Select Field -->
                <div>
                    <InputLabel for="project_managers" value="Team Project Manager"/>
                    <SelectItems
                        id="project_managers"
                        v-model="form.project_manager_id"
                        :options="usersOptions"
                        :error="form.errors.project_manager_id"
                        class="mt-1 block w-full"
                    />
                    <InputError class="mt-2" :message="form.errors.project_manager_id"/>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton type="submit" class="bg-#082f49 text-white">
                    {{ team ? 'Update Team' : 'Create New Team' }}
                </PrimaryButton>
            </div>
        </form>
    </section>
</template>

<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import SelectItems from '@/Components/SelectItems.vue';

const toast = useToast();
const { projectManagers, team } = usePage().props;

// Prepare options for project managers
const usersOptions = projectManagers.map(manager => ({
    value: manager.id,
    label: `${manager.profile.first_name} ${manager.profile.last_name}`
}));

// Initialize form with existing team data if editing
const form = useForm({
    team_name: team ? team.team_name : '', // Use existing team name or empty string
    project_manager_id: team ? team.project_manager_id : '', // Use existing manager id or empty string
});

// Function to handle form submission
const createTeam = () => {
    const routeName = team ? 'teams.update' : 'teams.store'; // Check if team exists to determine route
    const method = team ? 'patch' : 'post'; // Use PUT for updates, POST for creating

    form[method](route(routeName, { team: team ? team.id : null }), { // Pass team ID if editing
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Team ' + (team ? 'updated' : 'created') + ' successfully!');
        },
        onError: () => {
            toast.error('There was an error ' + (team ? 'updating' : 'creating') + ' the team.');
        }
    });
};
</script>
