<script setup>
// Import du layout sécurisé (utilisateur connecté)
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
// Import du composant Head pour définir le titre de la page
import {Head} from '@inertiajs/vue3';

// Déclaration des props reçues depuis Laravel/Inertia
defineProps({
    recentActivities: Array,   // Liste des activités récentes des utilisateurs
    quickOverview: Object,     // Données globales pour aperçu rapide (totaux, utilisateurs inactifs, etc.)
    quickActions: Array,       // Actions rapides (non utilisées dans ce template)
    upcomingEvents: Array,     // Liste des événements à venir (ex : congés)
});
</script>

<template>
    <!-- Définition du titre de l'onglet du navigateur -->
    <Head title="Dashboard"/>

    <!-- Layout principal pour utilisateur connecté -->
    <AuthenticatedLayout>
        <!-- En-tête de la page via slot nommé -->
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800">Dashboard</h2>
        </template>

        <!-- Conteneur principal avec padding vertical -->
        <div class="py-12">
            <!-- Conteneur central avec largeur max et espacement vertical -->
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">

                <!-- Section Aperçu rapide avec 4 cartes en grille responsive -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                    <!-- Carte Total Users -->
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 shadow-lg rounded-lg p-6 text-white">
                        <h3 class="text-lg font-semibold">Total Users</h3>
                        <p class="text-3xl font-bold mt-4">{{ quickOverview.totalUsers }}</p>
                    </div>

                    <!-- Carte Active Users Today -->
                    <div class="bg-gradient-to-r from-green-500 to-teal-600 shadow-lg rounded-lg p-6 text-white">
                        <h3 class="text-lg font-semibold">Active Users Today</h3>
                        <p class="text-3xl font-bold mt-4">{{ quickOverview.activeToday }}</p>
                    </div>

                    <!-- Carte Leaves Approved -->
                    <div class="bg-gradient-to-r from-orange-500 to-yellow-600 shadow-lg rounded-lg p-6 text-white">
                        <h3 class="text-lg font-semibold">Leaves Approved</h3>
                        <p class="text-3xl font-bold mt-4">{{ quickOverview.leavesApproved }}</p>
                    </div>

                    <!-- Carte Pending Leaves -->
                    <div class="bg-gradient-to-r from-red-500 to-pink-600 shadow-lg rounded-lg p-6 text-white">
                        <h3 class="text-lg font-semibold">Pending Leaves</h3>
                        <p class="text-3xl font-bold mt-4">{{ quickOverview.pendingLeaves }}</p>
                    </div>
                </div>

                <!-- Section Utilisateurs Inactifs Aujourd’hui -->
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-800">
                        <i class="fas fa-user-slash text-red-500 mr-2"></i> Who's Off Today?
                    </h3>

                    <!-- Liste des utilisateurs inactifs -->
                    <ul class="divide-y divide-gray-200 mt-4">
                        <li
                            v-for="user in quickOverview.inactiveUsers"
                            :key="user.id"
                            class="py-3 flex items-center"
                        >
                            <!-- Photo de profil ou icône par défaut -->
                            <div
                                class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-4 overflow-hidden"
                            >
                                <img
                                    v-if="user.profile_picture"
                                    :src="user.profile_picture"
                                    alt="Profile Picture"
                                    class="w-full h-full object-cover"
                                />
                                <i v-else class="fas fa-user text-gray-400"></i>
                            </div>

                            <!-- Nom complet de l’utilisateur -->
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ user.first_name }} {{ user.last_name }}
                                </p>
                            </div>
                        </li>
                    </ul>

                    <!-- Message si aucun utilisateur inactif -->
                    <div v-if="!quickOverview.inactiveUsers.length" class="text-gray-500 text-center mt-4">
                        <i class="fas fa-info-circle mr-1"></i> All users are active today.
                    </div>
                </div>

                <!-- Section Activités récentes -->
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-800">Recent Activities</h3>

                    <!-- Liste des activités -->
                    <ul class="mt-4 space-y-3">
                        <li v-for="activity in recentActivities" :key="activity.id" class="flex items-start gap-4">
                            <!-- Photo de profil ou icône par défaut -->
                            <div
                                class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden"
                            >
                                <img
                                    v-if="activity.profile_picture"
                                    :src="activity.profile_picture"
                                    alt="Profile Picture"
                                    class="w-full h-full object-cover"
                                />
                                <i v-else class="fas fa-user text-gray-400"></i>
                            </div>

                            <!-- Détails de l’activité -->
                            <div>
                                <p class="text-gray-700 font-medium">
                                    {{ activity.activity }}
                                </p>
                                <p class="text-sm text-gray-500">{{ activity.time }}</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Section Événements de congés à venir -->
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-800">
                        <i class="fas fa-calendar-alt text-blue-500 mr-2"></i> Upcoming Leave Events
                    </h3>

                    <!-- Liste des événements -->
                    <ul class="divide-y divide-gray-200 mt-4">
                        <li
                            v-for="event in upcomingEvents"
                            :key="event.name"
                            class="py-3 flex items-center"
                        >
                            <!-- Photo de profil ou icône par défaut -->
                            <div
                                class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-4 overflow-hidden"
                            >
                                <img
                                    v-if="event.profile_picture"
                                    :src="event.profile_picture"
                                    alt="Profile Picture"
                                    class="w-full h-full object-cover"
                                />
                                <i v-else class="fas fa-user text-gray-400"></i>
                            </div>

                            <!-- Détails de l’événement -->
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ event.name }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ event.event }} - <span class="font-semibold">{{ event.date }}</span>
                                </p>
                            </div>
                        </li>
                    </ul>

                    <!-- Message si aucun événement à venir -->
                    <div v-if="!upcomingEvents.length" class="text-gray-500 text-center mt-4">
                        <i class="fas fa-info-circle mr-1"></i> No upcoming events.
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

