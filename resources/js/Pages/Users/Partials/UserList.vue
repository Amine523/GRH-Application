<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">User List</h2>
            <p class="mt-1 text-sm text-gray-600">Manage the list of users, edit or delete their information.</p>
        </header>
        <div v-for="user in users" :key="user.id" class="mt-3 space-y-3 p-4 border rounded-md shadow-md">
            <div class="flex space-x-4">
                <div class="w-1/2">
                    <p class="text-sm font-medium text-gray-700">First Name</p>
                    <p class="text-lg">{{ user?.profile?.first_name }}</p>
                </div>
                <div class="w-1/2">
                    <p class="text-sm font-medium text-gray-700">Last Name</p>
                    <p class="text-lg">{{ user?.profile?.last_name }}</p>
                </div>
            </div>

            <div class="flex space-x-4">
                <div class="w-1/2">
                    <p class="text-sm font-medium text-gray-700">Phone Number</p>
                    <p class="text-lg">{{ user?.profile?.phone_number }}</p>
                </div>
                <div class="w-1/2">
                    <p class="text-sm font-medium text-gray-700">Address</p>
                    <p class="text-lg">{{ user?.profile?.address }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4">

                <PrimaryButton :disabled="isAdmin(user)" @click="editUser(user.id)" class="bg-#082f49 text-white">
                    edit
                </PrimaryButton>

                <PrimaryButton :disabled="isAdmin(user)" @click="deleteUser(user.id)" class="bg-red-500 text-white">
                    Delete
                </PrimaryButton>
            </div>
        </div>
    </section>
</template>

<script setup>
import {router} from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import {useToast} from "vue-toastification";
import "vue-toastification/dist/index.css";

defineProps({
    users: Array,
});
const toast = useToast();

const deleteUser = (id) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(`/users/${id}`, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('User deleted successfully!');
            },
            onError: () => {
                toast.error('There was an error deleting the user.');
            }
        });
    }
};

const editUser = (id) => {
    router.get(`/users/${id}/edit`);
};
const isAdmin = (user) => {
    return user.roles.includes('admin')
};

</script>
