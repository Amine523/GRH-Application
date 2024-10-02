

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
                <!-- Edit Button -->
                <Link :href="`/users/${user.id}/edit`" class="text-blue-500 underline">
                    <PrimaryButton>Edit</PrimaryButton>
                </Link>

                <!-- Delete Button -->
                <PrimaryButton @click="deleteUser(user.id)" class="bg-red-500 text-white">Delete</PrimaryButton>
            </div>
        </div>
    </section>
</template>
<script >
export default {
    props: {
        users : {type:Object}
    },
}
</script>
