<script setup>
import { ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';

const user = usePage().props.auth.user;
const { first_name, last_name, address, phone_number } = usePage().props;

const form = useForm({
    first_name: first_name || '',
    last_name: last_name || '',
    address: address || '',
    phone_number: phone_number || '',
});

const isEditing = ref(false);

const enableEditing = () => {
    isEditing.value = true;
};

const submitForm = () => {
    form.patch(route('profile.update'), {
        onSuccess: () => {
            isEditing.value = false;
        }
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Update your account's profile information and email address.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
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
                        :disabled="!isEditing"
                        :class="!isEditing ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : ''"
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
                        :disabled="!isEditing"
                        :class="!isEditing ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : ''"
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
                        :disabled="!isEditing"
                        :class="!isEditing ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : ''"
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
                        :disabled="!isEditing"
                        :class="!isEditing ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : ''"
                        autocomplete="address"
                    />

                    <InputError class="mt-2" :message="form.errors.address" />
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing || !isEditing" @click="submitForm">
                    Save
                </PrimaryButton>

                <PrimaryButton
                    type="button"
                    @click="enableEditing"
                    :disabled="isEditing || form.processing"
                    class="bg-blue-600 hover:bg-blue-700 focus:ring-blue-500"
                >
                    Edit
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition ease-in-out"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Saved successfully!
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
