<script>
import { DatePickerComponent, TimePickerComponent } from '@syncfusion/ej2-vue-calendars'
import { RadioButtonComponent } from '@syncfusion/ej2-vue-buttons'
import { SliderComponent } from '@syncfusion/ej2-vue-inputs'
import { ScheduleComponent, Day, Month, Agenda } from '@syncfusion/ej2-vue-schedule'
import { DropDownListComponent } from '@syncfusion/ej2-vue-dropdowns'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import Dialog from 'primevue/dialog'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import Tag from 'primevue/tag'
import moment from 'moment'
import RadioButton from 'primevue/radiobutton'
import Fieldset from 'primevue/fieldset'
import AutoComplete from 'primevue/autocomplete'
import Select from 'primevue/select'
import DatePicker from 'primevue/datepicker'
import Slider from 'primevue/slider'
import InputNumber from 'primevue/inputnumber'

export default {
    name: 'Index',
    components: {
        PrimaryButton,
        'ejs-schedule': ScheduleComponent,
        'ejs-datepicker': DatePickerComponent,
        'ejs-timepicker': TimePickerComponent,
        'ejs-radiobutton': RadioButtonComponent,
        'ejs-slider': SliderComponent,
        'ejs-dropdownlist': DropDownListComponent,
        Head,
        AuthenticatedLayout,
        DataTable,
        Column,
        InputText,
        Tag,
        Dialog,
        RadioButton,
        Fieldset,
        AutoComplete,
        Select,
        DatePicker,
        Slider,
        InputNumber
    },
    provide: {
        schedule: [Day, Month, Agenda]
    },
    data () {
        return {
            page: usePage(),
            selectedLeaves: [],
            leaveTypes: [
                { label: 'Vacation', value: 'vacation' },
                { label: 'Sick', value: 'sick' },
                { label: 'Authorisation', value: 'authorisation' },
                { label: 'Half Day', value: 'halfday' },
                { label: 'Late Deduction', value: 'deduction' }
            ],
            sessionOptions: [
                { label: 'Morning (08:00 - 12:00)', value: 'morning' },
                { label: 'Afternoon (13:00 - 17:00)', value: 'afternoon' }
            ],
            timeOptions: [
                { label: '08:00', value: '08:00' },
                { label: '08:30', value: '08:30' },
                { label: '09:00', value: '09:00' },
                { label: '09:30', value: '09:30' },
                { label: '10:00', value: '10:00' },
                { label: '10:30', value: '10:30' },
                { label: '11:00', value: '11:00' },
                { label: '11:30', value: '11:30' },
                { label: '12:00', value: '12:00' },
                { label: '12:30', value: '12:30' },
                { label: '13:00', value: '13:00' },
                { label: '13:30', value: '13:30' },
                { label: '14:00', value: '14:00' },
                { label: '14:30', value: '14:30' },
                { label: '15:00', value: '15:00' },
                { label: '15:30', value: '15:30' },
                { label: '16:00', value: '16:00' },
                { label: '16:30', value: '16:30' }
            ],
            // Schedule settings
            eventSettings: {
                dataSource: [],
                allowAdding: false
            },
            workDays: [1, 2, 3, 4, 5],
            views: ['Month', 'Day', 'Agenda'],
            selectedDate: new Date(),

            // Dialog visibility states
            activeDialog: null,  // 'refuse', etc.

            // User and team data
            users: [],
            mappedUsers: [],
            selectedProducts: [],
            localLeaves: [],

            // Table filters
            filters: {
                global: { value: '', matchMode: 'contains' }
            },

            // Leave form data using Inertia's useForm()
            leaveForm: useForm({
                type_of_leave: '',
                authorisation_hour: '',
                start_day: null,
                start_time: new Date('1970-01-01T08:00:00'),
                end_day: null,
                halfday_session: 'morning',
                authorisationHours: 0,
                team_user: null,
                user_id: null,
                deduction_days: null
            }),

            // Leave action variables
            refusedLeave: null,
            refuseReason: null,

            // Dialog visibility states (will be removed later)
            showDialog: false,
            showRefuseDialog: false,
        }
    },
    props: {
        leaves: Array,
        user: Object,
    },
    computed: {
        approvedLeaves () {
            return this.localLeaves
                .filter(leave => leave.status_of_leave.toLowerCase() === 'approved')
                .map(leave => {
                    this.users = this.$attrs.users
                    this.mappedUsers = this.mapToOptions(this.users, ['profile.first_name', 'profile.last_name'], 'id')

                    const user = this.users.find(u => u.id === leave.user_id)

                    const userName = user ? `${user.profile.first_name.toUpperCase()} ${user.profile.last_name}` : 'Unknown User'
                    const startDate = moment(leave.start_day, 'DD/MM/YYYY')
                    const endDate = moment(leave.end_day, 'DD/MM/YYYY')

                    let subject = userName
                    if (leave.type_of_leave === 'authorisation' && leave.start_time) {
                        const hoursFormatted = Number.isInteger(leave.authorization_hour)
                            ? `${leave.authorization_hour}h`
                            : `${parseFloat(leave.authorization_hour).toFixed(1)}h`
                        subject += ` - ${leave.start_time} | (${hoursFormatted})`
                    } else if (leave.type_of_leave === 'halfday') {
                        subject += ` - ${leave.start_time === '08:00' ? 'Morning' : 'Afternoon'}`
                    }

                    return {
                        Id: leave.id,
                        Subject: subject,
                        StartTime: startDate.format('YYYY-MM-DD'),
                        EndTime: endDate.format('YYYY-MM-DD'),
                        Status: leave.status_of_leave,
                        Type: leave.type_of_leave,
                        FirstName: user?.profile?.first_name ?? '',
                        LastName: user?.profile?.last_name ?? '',
                    }
                })
        },
        mappedLeaves () {
            return this.leaves.map(leave => {
                const user = this.users.find(user => user.id === leave.user_id) || { profile: {} }

                return {
                    id: leave.id,
                    first_name: user.profile.first_name || 'Unknown',
                    last_name: user.profile.last_name || 'User',
                    start_day: leave.start_day,
                    start_time: leave.start_time,
                    end_day: leave.end_day,
                    type_of_leave: leave.type_of_leave,
                    status_of_leave: leave.status_of_leave,
                    authorization_hour: leave.authorization_hour,
                }
            })
        },
        isAdmin () {
            return this.page.props.auth.user_roles.includes('admin')
        },
        isProjectManager () {
            return this.page.props.auth.user_roles.includes('project_manager')
        }
    },
    created () {
        this.localLeaves = [...this.leaves]
        this.users = this.$attrs.users
        this.mappedUsers = this.mapToOptions(this.users, ['profile.first_name', 'profile.last_name'], 'id')
    },
    methods: {
        approveLeave (leaveId) {
            router.post(route('leave.approve'), { id: leaveId }, {
                preserveScroll: true,
                onSuccess: this.refreshLeaves
            })
        },
        deleteLeave (leaveId) {
            router.post(route('leave.delete'), { id: leaveId }, {
                preserveScroll: true,
                onSuccess: () => {
                    this.localLeaves = this.localLeaves.filter(leave => leave.id !== leaveId)
                }
            })
        },
        refuseLeave () {
            this.leaveForm.post(route('leave.refuse'), {
                preserveScroll: true,
                onSuccess: this.refreshLeaves
            })
        },
        refreshLeaves () {
            router.visit(route('leave.index'), {
                only: ['leaves'],
                preserveScroll: true,
                preserveState: true,
                onSuccess: (response) => {
                    this.localLeaves = response.props.leaves
                }
            })
        },
        mapToOptions (items, labelFields, valueField = 'id') {
            return items.map(item => ({
                value: item[valueField],
                label: Array.isArray(labelFields)
                    ? labelFields.map(field => this.getNestedValue(item, field)).join(' ')
                    : this.getNestedValue(item, labelFields)
            }))
        },
        onEventRender (args) {
            if (args.data.Status === 'approved') {
                if (args.data.Type === 'authorisation') {
                    args.element.style.backgroundColor = '#205fa9'
                } else if (args.data.Type === 'halfday') {
                    args.element.style.backgroundColor = '#8A2BE2'
                } else if (args.data.Type === 'sick') {
                    args.element.style.backgroundColor = '#203b48'
                } else if (args.data.Type === 'deduction') {
                    args.element.style.backgroundColor = '#FF0000'
                } else {
                    args.element.style.backgroundColor = 'green'
                }
            } else if (args.data.Status === 'pending') {
                args.element.style.backgroundColor = 'orange'
            }
        },
        getNestedValue (item, field) {
            return field.split('.').reduce((obj, key) => obj && obj[key], item)
        },
        openDialog () {
            this.showDialog = true
        },
        openRefuseDialog (id) {
            this.refusedLeave = id
            this.showRefuseDialog = true
        },
        closeDialog () {
            this.showDialog = false
            this.showRefuseDialog = false
        },
        getStartTime () {
            switch (this.leaveForm.type_of_leave) {
                case 'halfday':
                    return this.leaveForm.halfday_session === 'morning' ? '07:00' : '12:00'
                default:
                    return this.leaveForm.start_time
            }
        },
        submitLeaveRequest () {
            if (this.leaveForm.type_of_leave === 'deduction') {
                this.leaveForm.start_day = moment().format('YYYY-MM-DD')
            }

            if (this.leaveForm.type_of_leave === 'halfday' || this.leaveForm.type_of_leave === 'authorisation') {
                this.leaveForm.end_day = this.leaveForm.start_day
            }

            this.leaveForm.user_id = this.leaveForm.team_user
                ? this.leaveForm.team_user
                : this.page.props.auth.user.id

            this.leaveForm.start_time = this.getStartTime()
            this.leaveForm.authorisationHours = this.leaveForm.type_of_leave === 'authorisation'
                ? Number(this.leaveForm.authorisationHours) || 0
                : null

            router.visit(route('leave.store'), {
                method: 'POST',
                only: ['leaves'],
                data: this.leaveForm.data(),
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    this.closeDialog()
                    this.localLeaves = this.leaves
                },
            })
        },
        setEventDataSource () {
            this.eventSettings.dataSource = this.localLeaves
                .filter(leave => leave.status_of_leave.toLowerCase() === 'approved')
                .map(leave => {
                    const user = this.users.find(u => u.id === leave.user_id)
                    const userName = user ? `${user.profile.first_name.toUpperCase()} ${user.profile.last_name}` : 'Unknown User'
                    const startDate = moment(leave.start_day, 'DD/MM/YYYY')
                    const endDate = moment(leave.end_day, 'DD/MM/YYYY')

                    let subject = userName
                    if (leave.type_of_leave === 'authorisation' && leave.start_time) {
                        const hoursFormatted = Number.isInteger(leave.authorization_hour)
                            ? `${leave.authorization_hour}h`
                            : `${parseFloat(leave.authorization_hour).toFixed(1)}h`
                        subject += ` - ${leave.start_time} | (${hoursFormatted})`
                    } else if (leave.type_of_leave === 'halfday') {
                        subject += ` - ${leave.start_time === '08:00' ? 'Morning' : 'Afternoon'}`
                    }

                    return {
                        Id: leave.id,
                        Subject: subject,
                        StartTime: startDate.format('YYYY-MM-DD'),
                        EndTime: endDate.format('YYYY-MM-DD'),
                        Status: leave.status_of_leave,
                        Type: leave.type_of_leave,
                        FirstName: user?.profile?.first_name ?? '',
                        LastName: user?.profile?.last_name ?? '',
                    }
                })
        }
    },
    watch: {
        approvedLeaves: {
            handler (newLeaves) {
                this.eventSettings = { ...this.eventSettings, dataSource: newLeaves }
            },
            deep: true,
            immediate: true
        }
    },
}
</script>

<template>
    <Head title="Leave Request"/>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <div class="flex justify-between py-5 gap-2">
                        <div class="flex gap-5 overflow-x-auto">
                            <div class="manuel-item flex gap-2 items-center"><span
                                class="is-square is-green-square"></span> Vacation Leave
                            </div>
                            <div class="manuel-item flex gap-2 items-center"><span
                                class="is-square is-darkBlue-square"></span> Sick Leave
                            </div>
                            <div class="manuel-item flex gap-2 items-center"><span
                                class="is-square is-violet-square"></span> Halfday
                            </div>
                            <div class="manuel-item flex gap-2 items-center"><span
                                class="is-square is-blue-square"></span> Authorisation
                            </div>
                            <div class="manuel-item flex gap-2 items-center"><span
                                class="is-square is-orange-square"></span> Pending Request
                            </div>
                            <div class="manuel-item flex gap-2 items-center"><span
                                class="is-square is-red-square"></span> Deduction
                            </div>
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
                        <DataTable
                            ref="dt"
                            :value="mappedLeaves"
                            dataKey="id"
                            :paginator="true"
                            :rows="10"
                            v-model:selection="selectedLeaves"
                            :filters="filters"
                            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                            :rowsPerPageOptions="[5, 10, 25]"
                            currentPageReportTemplate="Showing {first} to {last} of {totalRecords} leaves"
                        >
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

                            <!-- Enable multiple selection -->
                            <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>

                            <Column field="first_name" header="First Name" :sortable="true"/>
                            <Column field="last_name" header="Last Name" :sortable="true"/>
                            <Column field="start_day" header="Start Day" :sortable="true"/>
                            <Column field="end_day" header="End Day" :sortable="true"/>
                            <Column field="type_of_leave" header="Type of Leave" :sortable="true"/>

                            <Column bodyClass="text-center" field="status_of_leave" header="Status of Leave"
                                    :sortable="true">
                                <template #body="slotProps">
                                    <Tag v-if="slotProps.data.status_of_leave === 'approved'" severity="success"
                                         value="Approved"/>
                                    <Tag v-else-if="slotProps.data.status_of_leave === 'pending'" severity="warn"
                                         value="Pending"/>
                                    <Tag v-else severity="danger" value="Refused"/>
                                </template>
                            </Column>

                            <Column field="action" header="Action" bodyClass="text-center"
                                    v-if="isProjectManager || isAdmin">
                                <template #body="slotProps">
                                    <template v-if="slotProps.data.status_of_leave === 'pending'">
                                        <PrimaryButton @click="approveLeave(slotProps.data.id)"
                                                       class="bg-blue-600 text-white mr-2">
                                            Approve
                                        </PrimaryButton>
                                        <PrimaryButton @click="openRefuseDialog(slotProps.data.id)"
                                                       class="bg-red-600 text-white mr-2">
                                            Reject
                                        </PrimaryButton>
                                    </template>
                                    <PrimaryButton @click="deleteLeave(slotProps.data.id)"
                                                   class="bg-gray-600 text-white mr-2">
                                        Remove
                                    </PrimaryButton>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>
            </div>
        </div>
        <Dialog
            v-model:visible="showDialog"
            :closable="true"
            :modal="true"
            :dismissable-mask="true"
            header="Leave Request"
            @close="closeDialog"
            class="rounded-lg shadow-lg p-5 bg-white w-[95%] sm:w-[80%] md:w-[60%] lg:w-[50%] max-w-3xl mx-auto"
        >
            <div class="space-y-4">
                <section>
                    <h3 class="text-lg font-medium mb-2">Leave Type</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        <button
                            v-for="(type, index) in leaveTypes"
                            :key="index"
                            @click="leaveForm.type_of_leave = type.value"
                            class="w-full text-center py-2 px-3 rounded-lg border hover:bg-gray-100"
                            :class="{'bg-blue-500 text-white': leaveForm.type_of_leave === type.value}"
                        >
                            {{ type.label }}
                        </button>
                    </div>
                </section>

                <section v-if="isAdmin || isProjectManager">
                    <label class="font-medium">Select User:</label>
                    <Select
                        v-model="leaveForm.team_user"
                        :options="mappedUsers"
                        :filter="true"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Choose a user"

                        class="w-full border rounded-lg p-2"
                    />
                </section>

                <section v-if="leaveForm.type_of_leave !== 'deduction'">
                    <label class="font-medium">
                        {{
                            leaveForm.type_of_leave === 'authorisation' || leaveForm.type_of_leave === 'halfday'
                                ? 'Date'
                                : 'Start Date'
                        }}
                    </label>
                    <DatePicker
                        v-model="leaveForm.start_day"
                        dateFormat="dd-MM-yy"
                        :disabled-days="[0,6]"
                        class="w-full rounded-lg p-2"
                        placeholder="Select date"
                    />

                    <div v-if="leaveForm.type_of_leave !== 'authorisation' && leaveForm.type_of_leave !== 'halfday'">
                        <label class="font-medium">End Date:</label>
                        <DatePicker
                            v-model="leaveForm.end_day"
                            dateFormat="dd-MM-yy"
                            :disabled-days="[0,6]"
                            class="w-full rounded-lg p-2"
                            :disabled="leaveForm.type_of_leave === 'halfday' || leaveForm.type_of_leave === 'authorisation'"
                            placeholder="Select end date"
                        />
                    </div>
                </section>

                <section v-if="leaveForm.type_of_leave === 'halfday'">
                    <label class="font-medium">Session:</label>
                    <Select
                        v-model="leaveForm.halfday_session"
                        :options="sessionOptions"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Choose session"
                        class="w-full border rounded-lg p-2"
                    />
                </section>

                <template v-if="leaveForm.type_of_leave === 'authorisation'">
                    <section>
                        <label class="font-medium">Time:</label>
                        <Select
                            v-model="leaveForm.start_time"
                            :options="timeOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Choose time"
                            class="w-full border rounded-lg p-2"
                        />
                    </section>

                    <section>
                        <label class="font-medium">Authorisation Hours (0.5 - 2h per Month):</label>
                        <div class="grid grid-cols-4 gap-2">
                            <button
                                v-for="hour in [0.5, 1, 1.5, 2]"
                                :key="hour"
                                @click="leaveForm.authorisationHours = hour"
                                class="w-full text-center py-2 rounded-lg border hover:bg-gray-100"
                                :class="{'bg-blue-500 text-white': leaveForm.authorisationHours === hour}"
                            >
                                {{ hour }}h
                            </button>
                        </div>
                    </section>
                </template>

                <template v-if="isAdmin && leaveForm.type_of_leave === 'deduction'">
                    <section>
                        <label class="font-medium">Deducted Leave Days:</label>
                        <InputNumber
                            v-model="leaveForm.deduction_days"
                            :minFractionDigits="2" :maxFractionDigits="5"
                            fluid
                            class="w-full border rounded-lg p-2"
                            placeholder="Enter days to deduct"
                        />
                    </section>
                </template>

                <div class="flex justify-end gap-3 mt-4">
                    <button
                        @click="submitLeaveRequest"
                        class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition duration-200">
                        Request Leave
                    </button>
                    <button
                        @click="closeDialog"
                        class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition duration-200">
                        Close
                    </button>
                </div>
            </div>
        </Dialog>
        <Dialog
            v-model:visible="showRefuseDialog"
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
        </Dialog>
    </AuthenticatedLayout>
</template>
<style>
.p-dialog-mask {
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(5px);
}
</style>
