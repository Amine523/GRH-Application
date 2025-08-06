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
                        <InputLabel for="first_name" value="First Name*"/>

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
                        <InputLabel for="last_name" value="Last Name*"/>

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
                    <InputLabel for="email" value="Email*"/>

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
                        <InputLabel for="phone_number" value="Phone Number*"/>

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
                        <InputLabel for="valid_balance" value="Valid Balance*"/>

                        <TextInput
                            id="valid_balance"
                            type="number"
                            class="mt-1 block w-full"
                            v-model="form.valid_balance"
                            autocomplete="valid_balance"
                            step="0.1"
                            @blur="enforceOneDecimalPlace"

                        />

                        <InputError class="mt-2" :message="form.errors.valid_balance"/>
                    </div>

                    <!-- Role -->
                    <div>
                        <InputLabel for="role_id" value="User Role*"/>
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
                        <label class="block mb-2 text-sm font-medium text-gray-900" for="file_input">Profile
                            picture</label>
                        <input
                            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none"
                            id="file_input" type="file" @input="form.profile_picture = $event.target.files[0]">
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
import { useForm, usePage, router } from '@inertiajs/vue3';
import SelectItems from '@/Components/SelectItems.vue';

const { roles, teams, user } = usePage().props;

// Map roles and teams to options
const mapToOptions = (items, labelField, valueField = 'id') =>
    items.map(item => ({
        value: item[valueField],
        label: item[labelField]
    }));

const roleOptions = mapToOptions(roles, 'name', 'name');
const teamsOptions = mapToOptions(teams, 'team_name');

// Initialize form with existing user data if editing
// Calculate leave balance proportionally from current/next month until end of year
const calculateLeaveBalance = () => {
    const vacationDaysPerMonth = 1.66;
    const sickLeaveDays = 3;
    
    // Get current date
    const now = new Date();
    const currentDate = now.getDate();
    let currentMonth = now.getMonth(); // 0-11 (January-December)
    
    // If current date is after the 15th, start from next month
    if (currentDate > 15) {
        currentMonth += 1; // Move to next month
    }
    
    // Calculate remaining months in the year (including current/next month)
    const remainingMonths = 12 - currentMonth;
    
    // Calculate vacation days proportionally for remaining months
    let calculatedVacation = 0;
    if (remainingMonths > 0) {
        calculatedVacation = Math.round(vacationDaysPerMonth * remainingMonths * 10) / 10; // Keep one decimal
    }
    
    return {
        vacationDays: calculatedVacation,
        sickLeaveDays: sickLeaveDays,
        total: calculatedVacation + sickLeaveDays
    };
};

// Calculate leave balance for the current year
const leaveBalance = calculateLeaveBalance();

const form = useForm({
    first_name: user?.first_name ?? '',
    last_name: user?.last_name ?? '',
    email: user?.email ?? '',
    phone_number: user?.profile?.phone_number ?? '',
    address: user?.profile?.address ?? '',
    role_id: user?.roles?.[0]?.name ?? '',
    team_id: user?.team_id ?? '',
    valid_balance: user?.valid_balance ?? leaveBalance.total.toString(),
    leave_balance: user?.leave_balance ?? leaveBalance.total.toString(),
    profile_picture: null,
    _method: 'post',
});

// Function to handle form submission
const createUser = () => {
    const routeName = user ? 'users.update' : 'users.store';
    form._method = user ? 'patch' : 'post';

    form.post(route(routeName, {user: user ? user.id : null}), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            // Using Inertia's flash message
            router.visit(route('users.index'), {
                only: ['flash'],
                onSuccess: () => {
                    // This will show the flash message on the users index page
                }
            });
        },
        onError: () => {
            // Error handling can be shown using form.errors in the template
        }
    });
};
// Function to enforce one decimal place
const enforceOneDecimalPlace = () => {
    let value = parseFloat(form.valid_balance); // Parse input as a float
    if (isNaN(value)) {
        value = 0.0; // Default to 0.0 if invalid
    }
    form.valid_balance = value.toFixed(1); // Format to one decimal place
};
</script>