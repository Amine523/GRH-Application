<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">User List</h2>
            <p class="mt-1 text-sm text-gray-600">Manage the list of users, edit or delete their information.</p>
            <div class="flex items-end mt-2">
                <FilterInput
                    id="small-input"
                    label="Filter Users"
                    v-model="filterValue"
                    placeholder="Type to filter users..."
                    class="flex-grow"
                    @update:modelValue="handleSearch"
                />
            </div>
        </header>
        <div v-for="user in users" :key="user.id" class="mt-3 space-y-3 p-4 border rounded-md shadow-md">
            <!-- First Row: First Name, Last Name, Phone Number -->
            <div class="flex space-x-4">
                <div class="w-1/3">
                    <p class="text-sm font-medium text-gray-700">First Name</p>
                    <p class="text-lg">{{ user?.profile?.first_name }}</p>
                </div>
                <div class="w-1/3">
                    <p class="text-sm font-medium text-gray-700">Last Name</p>
                    <p class="text-lg">{{ user?.profile?.last_name }}</p>
                </div>

                <div class="w-1/3">
                    <p class="text-sm font-medium text-gray-700">Email</p>
                    <p class="text-lg">{{ user?.email }}</p>
                </div>
            </div>

            <!-- Second Row: Email, Leave Balance, User Role -->
            <div class="flex space-x-4 mt-4">
                <div class="w-1/3">
                    <p class="text-sm font-medium text-gray-700">Phone Number</p>
                    <p class="text-lg">{{ user?.profile?.phone_number }}</p>
                </div>
                <div class="w-1/3" v-if="isAdmin">
                    <p class="text-sm font-medium text-gray-700">Leave Balance</p>
                    <p class="text-lg">{{ user?.valid_balance }}</p>
                </div>
                <div class="w-1/3" v-if="isAdmin">
                    <p class="text-sm font-medium text-gray-700">User Role</p>
                    <p class="text-lg">{{ user?.roles[0].name }}</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-4 mt-4">
                <PrimaryButton :disabled="isAdmin(user)" @click="editUser(user.id)" class="bg-#082f49 text-white">
                    Edit
                </PrimaryButton>
                <PrimaryButton :disabled="isAdmin(user)" @click="deleteUser(user.id)" class="bg-red-500 text-white">
                    Delete
                </PrimaryButton>
                <PrimaryButton :disabled="isAdmin(user)" @click="warning(user.id)" class="bg-amber-400 text-white">
                    Warn
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
import FilterInput from "@/Components/FilterInput.vue";
import { ref, watch } from "vue";

defineProps({
    users: Array,
});
const toast = useToast();
const filterValue = ref('');

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
const warning = (id) => {
    router.post(`/users/${id}/warning`);
}
const editUser = (id) => {
    router.get(`/users/${id}/edit`);
};
const isAdmin = (user) => {
    return user.roles.includes('admin')
};

const handleSearch = () => {
    let url = new URL(route('user.index'));
    url.searchParams.set('q', filterValue.value); // Use .value to access filterValue
    router.visit(url, {
        replace: true,
        preserveScroll: true,
        preserveState: true,
        only: ['users'],
    });
};

// watch(filterValue, () => {
//     handleSearch();
// });
</script>
