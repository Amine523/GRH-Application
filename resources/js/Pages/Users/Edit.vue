
<template>
    <Head title="Edit User"/>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit {{ user.profile.first_name}} {{ user.profile.last_name}} Profile</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <UpdateUserInformationForm :user="user"></UpdateUserInformationForm>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import UpdateUserInformationForm from "@/Pages/Users/Partials/UpdateUserInformationForm.vue";

defineProps({

    user: {
        type: Object,
    },
});
</script>
