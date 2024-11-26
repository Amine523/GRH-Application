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
        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <tbody>
                <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 transition duration-200 border-b">
                    <th scope="row" class="flex items-center px-6 py-4 text-gray-900 whitespace-nowrap">
                        <img
                            class="w-12 h-12 rounded-full"
                            :src="user?.profile?.profile_picture || '/images/default-profile.png'"
                            alt="profile image">
                        <div class="ps-3">
                            <div class="text-base font-semibold">{{ user?.profile?.first_name }}
                                {{ user?.profile?.last_name }}
                            </div>
                            <div class="font-normal text-gray-500">{{ user?.email }}</div>
                        </div>
                    </th>
                    <td class="px-6 py-4">
                        {{ user?.roles[0].name }}
                    </td>
                    <td class="px-6 py-4">
                        {{ user?.profile?.phone_number }}
                    </td>
                    <td class="px-6 py-4">
                        {{ user?.valid_balance }}
                    </td>
                    <td class="px-6 py-4">
                        {{ user?.profile?.address }}
                    </td>
                    <td class="px-2 py-2">
                        <a @click="editUser(user.id)" class="font-medium text-blue-600 hover:underline">
                            <img src="/images/edit.svg" alt="" width="20px" class="svg">
                        </a>
                    </td>
                    <td class="px-2 py-2">
                        <a @click="deleteUser(user.id)" class="font-medium text-blue-600 hover:underline">
                            <img src="/images/delete.svg" alt="" width="20px" class="svg">
                        </a>
                    </td>
                    <td class="px-2 py-2">
                        <a @click="warning(user.id)" class="font-medium text-blue-600 hover:underline">
                            <img src="/images/warning.svg" alt="" width="23px" class="svg">
                        </a>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>

<style scoped>
/* Add a border on table rows */
tr {
    border-bottom: 1px solid #e5e7eb; /* Tailwind's gray-200 */
}

/* Make the row hover effect smooth */
tr:hover {
    transition: background-color 0.2s ease-in-out;
    transform: translateY(-1px);
}

/* Make the cursor a pointer when hovering over .svg images */
.svg:hover {
    cursor: pointer;
}
</style>
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
</script>
