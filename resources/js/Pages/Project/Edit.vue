<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Project</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Project Name -->
                        <div>
                            <InputLabel for="name" value="Project Name" />
                            <TextInput
                                id="name"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.name"
                                required
                                autofocus
                                autocomplete="name"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <!-- Project Description -->
                        <div>
                            <InputLabel for="description" value="Description" />
                            <TextArea
                                id="description"
                                class="mt-1 block w-full"
                                v-model="form.description"
                                rows="4"
                            />
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>

                        <!-- Project Manager -->
                        <div>
                            <InputLabel for="project_manager_id" value="Project Manager" />
                            <select 
                                id="project_manager_id"
                                v-model="form.project_manager_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option value="">Select a Project Manager</option>
                                <option v-for="manager in projectManagers" :key="manager.id" :value="manager.id">
                                    {{ manager.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.project_manager_id" />
                        </div>

                        <!-- Status -->
                        <div>
                            <InputLabel for="status" value="Status" />
                            <select 
                                id="status"
                                v-model="form.status"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.status" />
                        </div>

                        <!-- Start Date -->
                        <div>
                            <InputLabel for="start_date" value="Start Date" />
                            <TextInput
                                id="start_date"
                                type="date"
                                class="mt-1 block w-full"
                                v-model="form.start_date"
                            />
                            <InputError class="mt-2" :message="form.errors.start_date" />
                        </div>

                        <!-- End Date -->
                        <div>
                            <InputLabel for="end_date" value="End Date" />
                            <TextInput
                                id="end_date"
                                type="date"
                                class="mt-1 block w-full"
                                v-model="form.end_date"
                            />
                            <InputError class="mt-2" :message="form.errors.end_date" />
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-end mt-4 space-x-4">
                            <DangerButton @click="confirmProjectDeletion">
                                Delete Project
                            </DangerButton>
                            
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Update Project
                            </PrimaryButton>
                        </div>
                    </form>

                    <!-- Delete Project Confirmation Modal -->
                    <ConfirmationModal :show="confirmingProjectDeletion" @close="closeModal">
                        <template #title>
                            Delete Project
                        </template>

                        <template #content>
                            Are you sure you want to delete this project? This action cannot be undone.
                        </template>

                        <template #footer>
                            <SecondaryButton @click="closeModal">
                                Cancel
                            </SecondaryButton>

                            <DangerButton
                                class="ml-3"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                                @click="deleteProject"
                            >
                                Delete Project
                            </DangerButton>
                        </template>
                    </ConfirmationModal>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({
    project: {
        type: Object,
        required: true
    },
    projectManagers: {
        type: Array,
        required: true
    }
});

const form = useForm({
    name: props.project.name,
    description: props.project.description,
    project_manager_id: props.project.project_manager_id,
    status: props.project.status,
    start_date: props.project.start_date,
    end_date: props.project.end_date,
});

const confirmingProjectDeletion = ref(false);

const confirmProjectDeletion = () => {
    confirmingProjectDeletion.value = true;
};

const deleteProject = () => {
    router.delete(route('projects.destroy', props.project.id), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};

const closeModal = () => {
    confirmingProjectDeletion.value = false;
};

const submit = () => {
    form.put(route('projects.update', props.project.id), {
        preserveScroll: true,
        onSuccess: () => {},
    });
};
</script>
