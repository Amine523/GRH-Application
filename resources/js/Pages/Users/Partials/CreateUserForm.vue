<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                {{ user ? 'Edit User' : 'Create New User' }}
            </h2>
        </header>

        <form @submit.prevent="createUser" class="mt-3 space-y-3">
            <!-- Three form fields in one row -->
            <div class="grid gap-4">
                <!-- First Name and Last Name -->
                <div class="grid grid-cols-2 gap-4">
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

                    <!-- Last Name -->
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

                <!-- Email -->
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

                <!-- Phone Number, Address, Role, and Team -->
                <div class="grid grid-cols-4 gap-4">
                    <!-- Phone Number -->
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

                    <!-- Address -->
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
                    <!-- Address -->
                    <div>
                        <InputLabel for="valid_balance" value="Valid Balance"/>

                        <TextInput
                            id="valid_balance"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.valid_balance"
                            autocomplete="valid_balance"
                        />

                        <InputError class="mt-2" :message="form.errors.valid_balance"/>
                    </div>

                    <!-- Role -->
                    <div>
                        <InputLabel for="role_id" value="User Role"/>
                        <SelectItems
                            id="role_id"
                            v-model="form.role_id"
                            :options="roleOptions"
                            :error="form.errors.role_id"
                            class="mt-1 block w-full"
                            label=""/>
                    </div>

                    <!-- Team -->
                    <div>
                        <InputLabel for="team_id" value="User Team"/>
                        <SelectItems
                            id="team_id"
                            v-model="form.team_id"
                            :options="teamsOptions"
                            :error="form.errors.team_id"
                            class="mt-1 block w-full"
                            label=""/>
                    </div>
                    <div>
                        <InputLabel for="file" value="Profile Picture"/>
                        <input type="file" @input="form.profile_picture = $event.target.files[0]" />
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center gap-4">
                    <PrimaryButton type="submit" class="bg-#082f49 text-white">
                        {{ user ? 'Update User' : 'Create New User' }}
                    </PrimaryButton>
                </div>
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
const {roles, teams, user} = usePage().props;

// Map roles and teams to options
const mapToOptions = (items, labelField, valueField = 'id') =>
    items.map(item => ({
        value: item[valueField],
        label: item[labelField]
    }));

const roleOptions = mapToOptions(roles, 'name', 'name');
const teamsOptions = mapToOptions(teams, 'team_name');

// Initialize form with existing user data if editing
const form = useForm({
    email: user ? user.email : '',
    first_name: user ? user.profile.first_name : '',
    last_name: user ? user.profile.last_name : '',
    phone_number: user ? user.profile.phone_number : '',
    address: user ? user.profile.address : '',
    role_id: user ? user.roles[0].name : '',
    team_id: user ? user.team_id : '',
    valid_balance : user ? user.valid_balance : '',
    profile_picture: null,
});

// Function to handle form submission
const createUser = () => {
    const routeName = user ? 'users.update' : 'users.store';
    const method = user ? 'patch' : 'post';

    form[method](route(routeName, {user: user ? user.id : null}), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('User ' + (user ? 'updated' : 'created') + ' successfully!');
        },
        onError: () => {
            toast.error('There was an error ' + (user ? 'updating' : 'creating') + ' the user.');
        }
    });
};
</script>
