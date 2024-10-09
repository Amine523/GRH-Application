<script>
import { ref } from 'vue';
import { DatePickerComponent } from '@syncfusion/ej2-vue-calendars';
import { RadioButtonComponent } from '@syncfusion/ej2-vue-buttons';
import { SliderComponent } from '@syncfusion/ej2-vue-inputs';
import { DialogComponent } from '@syncfusion/ej2-vue-popups';
import { ScheduleComponent, Day, Month, Agenda } from '@syncfusion/ej2-vue-schedule';
import { DropDownListComponent } from '@syncfusion/ej2-vue-dropdowns';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import PrimaryButton from "@/Components/PrimaryButton.vue";

export default {
    name: "Index",
    components: {
        PrimaryButton,
        'ejs-schedule': ScheduleComponent,
        'ejs-datepicker': DatePickerComponent,
        'ejs-radiobutton': RadioButtonComponent,
        'ejs-slider': SliderComponent,
        'ejs-dialog': DialogComponent,
        'ejs-dropdownlist': DropDownListComponent,
        Head,
        AuthenticatedLayout,
    },
    provide: {
        schedule: [Day, Month, Agenda]
    },
    data() {
        return {
            eventSettings: {
                dataSource: [] // Initialize as empty to be populated later
            },
            views: ['Month', 'Day', 'Agenda'],
            selectedDate: new Date(),
            isAdmin: false,
            isProjectManager: false,
            showDialog: false,
            type_of_leave: '',
            start_day: null,
            end_day: null,
            authorisationHours: 0,
            team_user: null,
            users: [],
            mappedUsers: [],
        };
    },
    props: {
        leaves: Array
    },
    created() {
        if (this.$attrs.auth.user_roles[0].includes('admin')) {
            this.isAdmin = true;
        }
        if (this.$attrs.auth.user_roles[0].includes('project_manager')) {
            this.isProjectManager = true;
        }
        // Set users and map to options
        this.users = this.$attrs.users;
        this.mappedUsers = this.mapToOptions(this.users, 'profile.first_name', 'id');

        // Map leaves to the eventSettings.dataSource format
        this.eventSettings.dataSource = this.leaves.map(leave => {
            const user = this.users.find(user => user.id === leave.user_id);
            const userName = user ? user.profile.first_name.toUpperCase() : 'Unknown User';
            console.log(leave.status_of_leave);
            return {
                Id: leave.id,
                Subject: ` ${leave.type_of_leave} for  ${userName} Status : ${leave.status_of_leave.toUpperCase()}`,
                StartTime: new Date(leave.start_day),
                EndTime: new Date(leave.end_day),
                Status: leave.status_of_leave,
            };
        });
    },
    methods: {

        mapToOptions(items, labelField, valueField = 'id') {
            return items.map(item => ({
                value: item[valueField],
                label: this.getNestedValue(item, labelField)
            }));
        },
        onEventRender(args) {
            if (args.data.Status === 'pending') {
                args.element.style.backgroundColor = 'orange';
            } else if (args.data.Status === 'approved') {
                args.element.style.backgroundColor = 'green';
            } else if (args.data.Status === 'refused') {
                args.element.style.backgroundColor = 'red';
            }
        },
        getNestedValue(item, field) {
            return field.split('.').reduce((obj, key) => obj && obj[key], item);
        },
        openDialog() {
            this.showDialog = true;
        },
        closeDialog() {
            this.showDialog = false;
        },
        handletype_of_leaveChange(value) {
            this.type_of_leave = value;
            if (value === 'halfday' || value === 'authorisation') {
                this.end_day = this.start_day;
            }
        },
        submitLeaveRequest() {
            const leaveData = {
                type_of_leave: this.type_of_leave,
                start_day: this.start_day,
                end_day: this.end_day,
                authorisationHours: this.type_of_leave === 'authorisation' ? String(this.authorisationHours) : null,
                user_id: this.team_user ? this.team_user : this.$attrs.auth.user.id,
            };
            router.post(route('leave.store'), leaveData);
            this.closeDialog();
        }
    }
}
</script>

<template>
    <Head title="Leave Request"/>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Leave Request</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <div class="flex justify-end py-5 gap-2">
                        <PrimaryButton @click="openDialog" class="bg-green-600 text-white">
                            Add Leave Request
                        </PrimaryButton>
                    </div>

                    <ejs-schedule
                        :event-settings="eventSettings"
                        :views="views"
                        :selected-date="selectedDate"
                        height="600px"
                        :eventRendered="onEventRender"
                    ></ejs-schedule>
                </div>
            </div>
        </div>

        <ejs-dialog
            :visible="showDialog"
            header="Please fill leave information"
            :showCloseIcon="true"
            width="420px"
            @close="closeDialog"
        >
            <div class="p-4 py-5">
                <h3 class="mb-5">Select Leave Type</h3>
                <div class="flex space-x-[10px]">
                    <ejs-radiobutton
                        label="Vacation"
                        name="type_of_leave"
                        v-on:change="handletype_of_leaveChange('vacation')"
                        style="margin-bottom: 30px;"
                        class="custom-radio"
                    ></ejs-radiobutton>
                    <ejs-radiobutton
                        label="Sick"
                        name="type_of_leave"
                        v-on:change="handletype_of_leaveChange('sick')"
                        style="margin: 30px;"
                    ></ejs-radiobutton>
                    <ejs-radiobutton
                        label="Authorisation"
                        name="type_of_leave"
                        v-on:change="handletype_of_leaveChange('authorisation')"
                        style="margin: 30px;"
                    ></ejs-radiobutton>
                    <ejs-radiobutton
                        label="Half Day"
                        name="type_of_leave"
                        v-on:change="handletype_of_leaveChange('halfday')"
                        style="margin: 30px;"
                    ></ejs-radiobutton>
                </div>

                <!-- User selection for admin or project manager -->
                <div v-if="isAdmin || isProjectManager" class="mt-5">
                    <label>Select User:</label>
                    <ejs-dropdownlist
                        :dataSource="mappedUsers"
                        v-model="team_user"
                        :fields="{ text: 'label', value: 'value' }"
                        placeholder="Select a user"
                    ></ejs-dropdownlist>
                </div>

                <div class="mt-5 gap-3">
                    <div v-if="type_of_leave !== 'authorisation' && type_of_leave !== 'halfday'">
                        <label>Start Date:</label>
                        <ejs-datepicker v-model="start_day"></ejs-datepicker>

                        <label>End Date:</label>
                        <ejs-datepicker v-model="end_day"></ejs-datepicker>
                    </div>

                    <div v-if="type_of_leave === 'halfday' || type_of_leave === 'authorisation'">
                        <label>Date:</label>
                        <ejs-datepicker v-model="start_day"></ejs-datepicker>
                    </div>

                    <!-- Slider for authorisation hours -->
                    <div v-if="type_of_leave === 'authorisation'">
                        <label>Authorisation Minute (0-120):</label>
                        <ejs-slider
                            v-model="authorisationHours"
                            :min="0"
                            :max="120"
                            :step="15"
                        ></ejs-slider>
                        <!-- Display the current value of the slider -->
                        <span>Current Minute: {{ authorisationHours }}</span>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <PrimaryButton @click="submitLeaveRequest" class="bg-blue-500 text-white">
                        Submit
                    </PrimaryButton>
                    <PrimaryButton @click="closeDialog" class="bg-red-600 text-white">
                        Cancel
                    </PrimaryButton>
                </div>
            </div>
        </ejs-dialog>
    </AuthenticatedLayout>
</template>
