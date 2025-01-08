<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Pie } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    ArcElement,
} from 'chart.js';

// Register chart.js components
ChartJS.register(Title, Tooltip, Legend, ArcElement);

defineProps({
    leaveCredits: Object, // Contains data for the pie chart
});
</script>

<template>
    <Head title="User Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800">User Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto sm:px-6 lg:px-8 space-y-8">

                <!-- Pie Chart Section (Smaller Chart) -->
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-800">Leave Credit Overview</h3>
                    <div class="mt-6 flex justify-center items-center">
                        <div style="max-width: 300px; max-height: 300px;">
                            <Pie
                                :data="{
                  labels: ['Sick', 'Vacation', 'Authorization', 'Half Day', 'Remaining Credit'],
                  datasets: [
                    {
                      data: [
                        leaveCredits.sick,
                        leaveCredits.vacation,
                        leaveCredits.authorization,
                        leaveCredits.halfDay,
                        leaveCredits.remaining,
                      ],
                      backgroundColor: [
                        '#ff0d3e',
                        '#0099ff',
                        '#FFCE56',
                        '#fa7101',
                        '#288e1b',
                      ],
                    },
                  ],
                }"
                                :options="{
                  responsive: true,
                  maintainAspectRatio: false,
                  plugins: {
                    legend: {
                      position: 'top',
                    },
                  },
                }"
                            />
                        </div>
                    </div>
                </div>

                <!-- User Info Section -->
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-800">Welcome, {{ $page.props.auth.user.name }}</h3>
                    <p class="mt-4 text-gray-600">
                        Here is an overview of your leave credits and activities. Use this dashboard to monitor
                        your remaining leave balances.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
