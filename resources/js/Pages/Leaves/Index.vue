<script>
import { ref } from 'vue';
import { ScheduleComponent, Day, Week, WorkWeek, Month, Agenda } from '@syncfusion/ej2-vue-schedule';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import PrimaryButton from "@/Components/PrimaryButton.vue";

export default {
    name: "Index",
    components: {
        PrimaryButton,
        'ejs-schedule': ScheduleComponent,
        Head,
        AuthenticatedLayout,
        ref,
    },
    provide: {
        schedule: [Day, Month, Agenda]
    },
    data() {
        return {
            eventSettings: {
                dataSource: [
                    {
                        Id: 1,
                        Subject: 'Meeting',
                        StartTime: new Date(2023, 1, 15, 10, 0),
                        EndTime: new Date(2023, 1, 15, 12, 30)
                    },
                    {
                        Id: 2,
                        Subject: 'Conference',
                        StartTime: new Date(2023, 1, 16, 10, 0),
                        EndTime: new Date(2023, 1, 16, 12, 30)
                    },
                    {
                        Id: 3,
                        Subject: 'Workshop',
                        StartTime: new Date(2023, 1, 17, 10, 0),
                        EndTime: new Date(2023, 1, 17, 12, 30)
                    }
                ]
            },
            views: ['Month','Day', 'Agenda'], // Add 'Agenda' view here
            selectedDate: new Date(2023, 1, 15)
        };
    },
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
                    <div class="flex justify-end">
                        <PrimaryButton class="bg-green-600 text-white">
                            Add New User
                        </PrimaryButton>
                    </div>
                    <ejs-schedule
                        :event-settings="eventSettings"
                        :views="views"
                        :selected-date="selectedDate"
                        height ='550px'
                    ></ejs-schedule>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@import '@syncfusion/ej2-base/styles/material.css';
@import '@syncfusion/ej2-buttons/styles/material.css';
@import '@syncfusion/ej2-calendars/styles/material.css';
@import '@syncfusion/ej2-dropdowns/styles/material.css';
@import '@syncfusion/ej2-inputs/styles/material.css';
@import '@syncfusion/ej2-navigations/styles/material.css';
@import '@syncfusion/ej2-popups/styles/material.css';
@import '@syncfusion/ej2-vue-schedule/styles/material.css';
</style>
