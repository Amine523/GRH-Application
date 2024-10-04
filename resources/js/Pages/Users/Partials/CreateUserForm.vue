<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Create New User
            </h2>
        </header>

        <form
            @submit.prevent="createUser"
            class="mt-3 space-y-3"
        >
            <!-- Three form fields in one row -->
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <InputLabel for="email" value="Email"/>

                    <TextInput
                        id="email"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        autofocus
                        autocomplete="email"
                    />

                    <InputError class="mt-2" :message="form.errors.email"/>
                </div>

                <div>
                    <InputLabel for="first_name" value="First Name"/>

                    <TextInput
                        id="first_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.first_name"
                        autofocus
                        autocomplete="first_name"
                    />

                    <InputError class="mt-2" :message="form.errors.first_name"/>
                </div>

                <div>
                    <InputLabel for="last_name" value="Last Name"/>

                    <TextInput
                        id="last_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.last_name"
                        autocomplete="last_name"
                    />

                    <InputError class="mt-2" :message="form.errors.last_name"/>
                </div>
            </div>

            <!-- Phone Number, Address, and Country in one row -->
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <InputLabel for="phone_number" value="Phone Number"/>

                    <TextInput
                        id="phone_number"
                        type="tel"
                        class="mt-1 block w-full"
                        v-model="form.phone_number"
                        autocomplete="phone"
                    />

                    <InputError class="mt-2" :message="form.errors.phone_number"/>
                </div>

                <div>
                    <InputLabel for="address" value="Address"/>

                    <TextInput
                        id="address"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.address"
                        autocomplete="address"
                    />

                    <InputError class="mt-2" :message="form.errors.address"/>
                </div>

                <div>
                    <InputLabel for="role_id" value="User Role"/>
                    <SelectItems
                        id="role_id"
                        v-model="form.role_id"
                        :options="roleOptions"
                        :error="form.errors.role_id"
                        class="mt-1 block w-full"
                        label=""
                    />
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton @click="createUser" class="bg-#082f49 text-white">
                    Create New User
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
import {useForm, usePage} from '@inertiajs/vue3';
import {useToast} from 'vue-toastification';
import SelectItems from '@/Components/SelectItems.vue';

const toast = useToast();
const { roles } = usePage().props;
const roleOptions = roles.map(role => ({
    value: role.name,
    label: role.name
}));
const form = useForm({
    email: '',
    first_name: '',
    last_name: '',
    phone_number: '',
    address: '',
    role_id: '',
});

const createUser = () => {
    form.post(route('users.store'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('User created successfully!');
        },
        onError: () => {
            toast.error('There was an error creating the user.');
        }
    });
};
</script>
