<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Update {{user.profile.first_name}}'s profile information.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('users.update'))"
            class="mt-3 space-y-3"
        >
            <div class="flex space-x-4">
                <div class="w-1/2">
                    <InputLabel for="first_name" value="First Name" />

                    <TextInput
                        id="first_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.first_name"
                        autofocus
                        autocomplete="first_name"
                    />

                    <InputError class="mt-2" :message="form.errors.first_name" />
                </div>

                <div class="w-1/2">
                    <InputLabel for="last_name" value="Last Name" />

                    <TextInput
                        id="last_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.last_name"
                        autocomplete="last_name"
                    />

                    <InputError class="mt-2" :message="form.errors.last_name" />
                </div>
            </div>

            <!-- Phone Number and Address side by side -->
            <div class="flex space-x-4">
                <div class="w-1/2">
                    <InputLabel for="phone_number" value="Phone Number" />

                    <TextInput
                        id="phone_number"
                        type="tel"
                        class="mt-1 block w-full"
                        v-model="form.phone_number"
                        autocomplete="phone"
                    />

                    <InputError class="mt-2" :message="form.errors.phone_number" />
                </div>

                <div class="w-1/2">
                    <InputLabel for="address" value="Address" />

                    <TextInput
                        id="address"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.address"
                        autocomplete="address"
                    />

                    <InputError class="mt-2" :message="form.errors.address" />
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton @click="updateUser(user.id)" class="bg-#082f49 text-white">
                    Save Informations
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

const { user } = usePage().props;
const toast = useToast();

const form = useForm({
    first_name: user.profile.first_name || '',
    last_name: user.profile.last_name || '',
    address: user.profile.address || '',
    phone_number: user.profile.phone_number || '',
});
const updateUser = (id) => {
    form.patch(route('users.update', id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('User updated successfully!');
        },
        onError: () => {
            toast.error('There was an error updating the user.');
        }
    });
};
</script>
