<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdatePasswordForm from '/resources/js/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '/resources/js/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import {Head, router} from '@inertiajs/vue3'; // import router
import UserList from "@/Pages/Users/Partials/UserList.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

defineProps({
    users: {
        type: Object,
    },
});

const addUser = () => {
    router.get(route('users.create'));
};
</script>

<template>
    <Head title="Profile"/>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">User List</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <div class="flex justify-end">
                        <PrimaryButton @click="addUser" class="bg-green-600 text-white">
                            Add New User
                        </PrimaryButton>
                    </div>
                    <UserList :users="users"></UserList>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
