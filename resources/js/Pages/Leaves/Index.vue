<script>
import {ref} from 'vue';
import {DatePickerComponent} from '@syncfusion/ej2-vue-calendars';
import {TimePickerComponent} from '@syncfusion/ej2-vue-calendars';
import {RadioButtonComponent} from '@syncfusion/ej2-vue-buttons';
import {SliderComponent} from '@syncfusion/ej2-vue-inputs';
import {DialogComponent} from '@syncfusion/ej2-vue-popups';
import {ScheduleComponent, Day, Month, Agenda} from '@syncfusion/ej2-vue-schedule';
import {DropDownListComponent} from '@syncfusion/ej2-vue-dropdowns';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, router} from '@inertiajs/vue3';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import {useToast} from "vue-toastification";
import moment from "moment";

export default {
    name: "Index",
    components: {
        PrimaryButton,
        'ejs-schedule': ScheduleComponent,
        'ejs-datepicker': DatePickerComponent,
        'ejs-timepicker': TimePickerComponent,
        'ejs-radiobutton': RadioButtonComponent,
        'ejs-slider': SliderComponent,
        'ejs-dialog': DialogComponent,
        'ejs-dropdownlist': DropDownListComponent,
        Head,
        AuthenticatedLayout,
        DataTable,
        Column,
        InputText,
        Tag
    },
    provide: {
        schedule: [Day, Month, Agenda]
    },

    data() {
        return {
            eventSettings: {
                dataSource: [],
                allowAdding: false
            },
            workDays: [1, 2, 3, 4, 5],
            views: ['Month', 'Day', 'Agenda'],
            selectedDate: new Date(),
            isAdmin: false,
            isProjectManager: false,
            showDialog: false,
            showRefuseDialog: false,
            refuseReason: null,
            refusedLeave: null,
            showRevokeDialog: false,
            revokedLeave: null,
            revokeReason: null,
            type_of_leave: '',
            start_day: null,
            start_time: null,
            end_day: null,
            authorisationHours: 0,
            team_user: null,
            users: [],
            mappedUsers: [],
            selectedProducts: [],
            minTime: new Date('1970-01-01T08:00:00'),
            maxTime: new Date('1970-01-01T17:00:00'),
            filters: {
                global: {value: ''}
            },
        };
    },
    props: {
        leaves: Array,
        user: Object,
    },
    computed: {
        mappedLeaves() {
            return this.leaves.map(leave => {
                const user = this.users.find(user => user.id === leave.user_id);
                return {
                    id: leave.id,
                    first_name: user ? user.profile.first_name : 'Unknown',
                    last_name: user ? user.profile.last_name : 'User',
                    start_day: leave.start_day,
                    start_time: leave.start_time,
                    end_day: leave.end_day,
                    type_of_leave: leave.type_of_leave,
                    status_of_leave: leave.status_of_leave
                };
            });
        }
    },
    created() {
        if (this.$attrs.auth.user_roles[0].includes('admin')) {
            this.isAdmin = true;
        }
        if (this.$attrs.auth.user_roles[0].includes('project_manager')) {
            this.isProjectManager = true;
        }
        this.users = this.$attrs.users;
        this.mappedUsers = this.mapToOptions(this.users, ['profile.first_name', 'profile.last_name'], 'id');
        this.setEventDataSource();
    },
    methods: {
        disableWeekends(args) {
            const day = args.date.getDay();
            if (day === 0 || day === 6) {
                args.isDisabled = true;
            }
        },
        approveLeave(leaveId) {
            const leaveData = {
                id: leaveId,
            };
            const toast = useToast();
            router.post(route('leave.approve'), leaveData, {
                preserveScroll: true,
                onSuccess: () => {
                    this.refreshLeaves();
                },
            })
        },
        deleteLeave(leaveId) {
            const leaveData = {
                id: leaveId,
            };
            router.post(route('leave.delete'), leaveData, {
                preserveScroll: true,
                onSuccess: () => {
                    this.refreshLeaves();
                },
            })
        },
        refuseLeave() {
            const leaveData = {
                id: this.refusedLeave,
                leaveReason: this.refuseReason,
            };
            router.post(route('leave.refuse'), leaveData).then(response => {
                this.refreshLeaves();
            }).catch(error => {
                console.error('Error refusing leave:', error);
            });
        },
        revokeLeave() {
            const leaveData = {
                id: this.revokedLeave,
                revokeReason: this.revokeReason,
            };
            router.post(route('leave.revoke'), leaveData, {
                preserveScroll: true,
                onSuccess: () => {
                    this.refreshLeaves();
                },
            });
        },
        refreshLeaves() {
            router.get(route('leave.index')).then(response => {
                this.leaves = response.data.leaves;
            });
        },
        mapToOptions(items, labelFields, valueField = 'id') {
            return items.map(item => ({
                value: item[valueField],
                label: Array.isArray(labelFields)
                    ? labelFields.map(field => this.getNestedValue(item, field)).join(' ')
                    : this.getNestedValue(item, labelFields)
            }));
        },
        onEventRender(args) {
            if (args.data.Status === 'pending') {
                args.element.style.backgroundColor = 'orange';
            } else if (args.data.Status === 'approved') {
                if (args.data.Subject.includes('authorisation')) {
                    args.element.style.backgroundColor = '#205fa9';
                } else if (args.data.Subject.includes('sick')) {
                    args.element.style.backgroundColor = '#203b48';
                } else {
                    args.element.style.backgroundColor = 'green';
                }
            } else if (args.data.Status === 'rejected') {
                args.element.style.backgroundColor = 'red';
            } else if (args.data.Status === 'revoked') {
                args.element.style.backgroundColor = 'gray';
            }
        },
        getNestedValue(item, field) {
            return field.split('.').reduce((obj, key) => obj && obj[key], item);
        },
        openDialog() {
            this.showDialog = true;
        },
        openRefuseDialog(id) {
            this.refusedLeave = id;
            this.showRefuseDialog = true;
        },
        openRevokeDialog(id) {
            this.revokedLeave = id;
            this.showRevokeDialog = true;
        },
        closeDialog() {
            this.showDialog = false;
            this.showRefuseDialog = false;
            this.showRevokeDialog = false;
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
                start_time: this.start_time,
                end_day: this.end_day,
                authorisationHours: this.type_of_leave === 'authorisation' ? String(this.authorisationHours) : null,
                user_id: this.team_user ? this.team_user : this.$attrs.auth.user.id,
            };
            router.post(route('leave.store'), leaveData, {
                preserveScroll: true,
                onSuccess: () => {
                    this.closeDialog();
                    this.refreshLeaves();
                    this.setEventDataSource();
                },
            })
        },
        setEventDataSource() {
            this.eventSettings.dataSource = this.leaves
                .filter(leave =>
                    !['revoked', 'rejected'].includes(leave.status_of_leave.toLowerCase())
                )
                .flatMap(leave => {
                    const user = this.users.find(user => user.id === leave.user_id);
                    const userName = user ? user.profile.first_name.toUpperCase() : 'Unknown User';
                    let subject = '';
                    const startDate = moment(leave.start_day, 'DD/MM/YYYY');
                    const endDate = moment(leave.end_day, 'DD/MM/YYYY');

                    const events = [];
                    let currentStart = startDate.clone();

                    if(leave.type_of_leave === 'authorisation' && leave.start_time) {
                        subject = leave.type_of_leave + ' '+ leave.start_time  + ': ' + userName;
                    } else {
                        subject = leave.type_of_leave + ': ' + userName;
                    }
                    while (currentStart.isSameOrBefore(endDate)) {
                        const currentWeekEnd = moment.min(
                            currentStart.clone().day(5),
                            endDate.clone()
                        );

                        if (currentStart.day() !== 0 && currentStart.day() !== 6) {
                            events.push({
                                Id: leave.id,
                                Subject: `${subject}`,
                                StartTime: currentStart.format('MM/DD/YYYY'),
                                EndTime: currentWeekEnd.clone().add(1, 'day').format('MM/DD/YYYY'),
                                Status: leave.status_of_leave,
                                Type: leave.type_of_leave,
                                FirstName: user?.profile?.first_name ?? '',
                                LastName: user?.profile?.last_name ?? '',
                            });
                        }

                        currentStart = currentWeekEnd.clone().add(3, 'days'); // Advance to next week
                    }

                    return events;
                });
        }
    }
}
</script>

<template>
    <Head title="Leave Request"/>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <div class="flex justify-between py-5 gap-2">
                        <div class="flex gap-5">
                            <div class="manuel-item flex gap-2 items-center"><span class="is-square is-green-square"></span> Vacation Leave</div>
                          <div class="manuel-item flex gap-2 items-center"><span class="is-square is-darkBlue-square"></span> Sick Leave</div>
                          <div class="manuel-item flex gap-2 items-center"><span class="is-square is-blue-square"></span> Autorisation</div>
                          <div class="manuel-item flex gap-2 items-center"><span class="is-square is-orange-square"></span> Pending Request</div>
                        </div>
                        <PrimaryButton @click="openDialog" class="bg-green-600 text-white">
                            Add Leave Request
                        </PrimaryButton>
                    </div>
                    <ejs-schedule
                        :event-settings="eventSettings"
                        :views="views"
                        :selected-date="selectedDate"
                        height="750px"
                        :eventRendered="onEventRender"
                        :firstDayOfWeek="1"
                    ></ejs-schedule>
                </div>
            </div>
            <div class="mx-auto space-y-6 sm:px-6 lg:px-8 py-5">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <div class="mt-6">
                        <h3 class="text-lg font-bold mb-4">Leave Requests</h3>
                        <DataTable ref="dt" :value="mappedLeaves" dataKey="id" :paginator="true" :rows="10"
                                   :filters="filters"
                                   paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                                   :rowsPerPageOptions="[5, 10, 25]"
                                   currentPageReportTemplate="Showing {first} to {last} of {totalRecords} leaves">

                            <template #header>
                                <div class="flex justify-content-end">
            <span class="p-input-icon-left">
                <InputText v-model="filters['global'].value" placeholder="Search for Leaves"/>
            </span>
                                </div>
                            </template>

                            <template #empty>
                                <h4>No leaves found</h4>
                            </template>

                            <Column selectionMode="multiple" headerStyle="width: 3rem"/>
                            <Column field="first_name" header="First Name" sortable/>
                            <Column field="last_name" header="Last Name" sortable/>
                            <Column field="start_day" header="Start Day" sortable/>
                            <Column field="end_day" header="End Day" sortable/>
                            <Column field="type_of_leave" header="Type of Leave" sortable/>
                            <Column field="status_of_leave" header="Status of Leave" sortable bodyClass="text-center">
                                <template #body="slotProps">
                                    <Tag v-if="slotProps.data.status_of_leave === 'approved'" severity="success"
                                         value="Approved"/>
                                    <Tag v-else-if="slotProps.data.status_of_leave === 'pending'" severity="warn"
                                         value="Pending"/>
                                    <Tag v-else-if="slotProps.data.status_of_leave === 'revoked'" severity="info"
                                         value="Revoked"/>
                                    <Tag v-else severity="danger" value="Refused"/>
                                </template>
                            </Column>
                            <Column field="action" header="Action" bodyClass="text-center"
                                    v-if="isProjectManager || isAdmin">
                                <template #body="slotProps">
                                    <template v-if="slotProps.data.status_of_leave === 'pending'">
                                        <PrimaryButton
                                            @click="approveLeave(slotProps.data.id)"
                                            class="bg-blue-600 text-white mr-2"
                                        >
                                            Approve
                                        </PrimaryButton>
                                        <PrimaryButton
                                            @click="openRefuseDialog(slotProps.data.id)"
                                            class="bg-red-600 text-white mr-2"
                                        >
                                            Reject
                                        </PrimaryButton>
                                    </template>
                                    <template v-else-if="slotProps.data.status_of_leave !== 'revoked'">
                                        <PrimaryButton
                                            @click="openRevokeDialog(slotProps.data.id)"
                                            class="bg-orange-500 text-white mr-2"
                                        >
                                            Revoke
                                        </PrimaryButton>
                                    </template>
                                    <PrimaryButton
                                        @click="deleteLeave(slotProps.data.id)"
                                        class="bg-gray-600 text-white mr-2"
                                    >
                                        Remove
                                    </PrimaryButton>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
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
                        <ejs-datepicker format='dd-MM-yyyy' v-model="start_day" :firstDayOfWeek='1'
                                        :renderDayCell="disableWeekends"></ejs-datepicker>

                        <label>End Date:</label>
                        <ejs-datepicker format='dd-MM-yyyy' v-model="end_day" :firstDayOfWeek='1'
                                        :renderDayCell="disableWeekends"></ejs-datepicker>
                    </div>

                    <div v-if="type_of_leave === 'halfday' || type_of_leave === 'authorisation'">
                        <label>Date:</label>
                        <ejs-datepicker v-model="start_day" :firstDayOfWeek='1'
                                        :renderDayCell="disableWeekends"></ejs-datepicker>
                    </div>

                    <div v-if="type_of_leave === 'authorisation'">
                        <label>Time:</label>
                        <ejs-timepicker :min="minTime" :max="maxTime" :value="minTime" v-model="start_time"></ejs-timepicker>
                    </div>

                    <!-- Slider for authorisation hours -->
                    <div v-if="type_of_leave === 'authorisation'">
                        <label>Authorisation Hour (0-2) per Month:</label>
                        <ejs-slider
                            v-model="authorisationHours"
                            :min="0"
                            :max="2"
                            :step="0.5"
                        ></ejs-slider>
                        <!-- Display the current value of the slider -->
                        <span>Current Hours: {{ authorisationHours }}</span>
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
        <ejs-dialog
            :visible="showRefuseDialog"
            header="Reason for Refusal"
            :showCloseIcon="true"
            width="420px"
            @close="closeDialog"
        >
            <div class="p-4 py-5">
                <div class="mb-4">
                    <label for="refuseReason" class="block text-sm font-medium text-gray-700 mb-2">
                        Please provide the reason for refusal
                    </label>
                    <textarea
                        id="refuseReason"
                        v-model="refuseReason"
                        rows="4"
                        class="w-full border border-gray-300 p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter reason here..."
                    ></textarea>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <PrimaryButton @click="refuseLeave()" class="bg-blue-500 text-white">
                        Refuse Request
                    </PrimaryButton>
                    <PrimaryButton @click="closeDialog" class="bg-red-600 text-white">
                        Cancel
                    </PrimaryButton>
                </div>
            </div>
        </ejs-dialog>
        <ejs-dialog
            :visible="showRevokeDialog"
            header="Reason for the revoke"
            :showCloseIcon="true"
            width="420px"
            @close="closeDialog"
        >
            <div class="p-4 py-5">
                <div class="mb-4">
                    <label for="revokeReason" class="block text-sm font-medium text-gray-700 mb-2">
                        Please provide the reason for the revoke
                    </label>
                    <textarea
                        id="revokeReason"
                        v-model="revokeReason"
                        rows="4"
                        class="w-full border border-gray-300 p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter reason here..."
                    ></textarea>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <PrimaryButton @click="revokeLeave()" class="bg-blue-500 text-white">
                        Revoke Request
                    </PrimaryButton>
                    <PrimaryButton @click="closeDialog" class="bg-red-600 text-white">
                        Cancel
                    </PrimaryButton>
                </div>
            </div>
        </ejs-dialog>

    </AuthenticatedLayout>
</template>
