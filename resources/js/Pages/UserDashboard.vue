<script setup>
// Import du layout qui sécurise la page (utilisateur connecté)
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
// Import du composant Head pour définir le titre de la page
import { Head } from '@inertiajs/vue3';

// Import du composant Pie de vue-chartjs pour afficher un graphique en camembert
import { Pie } from 'vue-chartjs';

// Import des modules nécessaires de chart.js
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    ArcElement,
} from 'chart.js';

// Enregistrement des composants Chart.js nécessaires pour le pie chart
ChartJS.register(Title, Tooltip, Legend, ArcElement);

// Définition des props reçues du backend via Inertia
defineProps({
    leaveCredits: Object, // Objet contenant les données des crédits de congés (sick, vacation, etc.)
});
</script>

<template>
    <!-- Titre de la page dans l'onglet du navigateur -->
    <Head title="User Dashboard" />

    <!-- Layout principal pour utilisateur connecté -->
    <AuthenticatedLayout>
        <!-- Slot header pour titre de la page -->
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800">User Dashboard</h2>
        </template>

        <!-- Conteneur principal avec padding vertical -->
        <div class="py-12">
            <!-- Centrage horizontal, padding responsive et espacement vertical -->
            <div class="mx-auto sm:px-6 lg:px-8 space-y-8">

                <!-- Section graphique circulaire -->
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-800">Leave Credit Overview</h3>

                    <!-- Conteneur pour fixer la taille max du graphique -->
                    <div class="mt-6 flex justify-center items-center">
                        <div style="max-width: 300px; max-height: 300px;">
                            <Pie
                                :data="{
                  // Les labels correspondant aux types de congés affichés dans la légende
                  labels: ['Sick', 'Vacation', 'Authorization', 'Half Day', 'Remaining Credit'],
                  datasets: [
                    {
                      // Valeurs pour chaque type de congé provenant de la prop leaveCredits
                      data: [
                        leaveCredits.sick,
                        leaveCredits.vacation,
                        leaveCredits.authorization,
                        leaveCredits.halfDay,
                        leaveCredits.remaining,
                      ],
                      // Couleurs attribuées à chaque portion du graphique
                      backgroundColor: [
                        '#ff0d3e',  // rouge vif pour Sick
                        '#0099ff',  // bleu vif pour Vacation
                        '#FFCE56',  // jaune pour Authorization
                        '#fa7101',  // orange pour Half Day
                        '#288e1b',  // vert pour Remaining Credit
                      ],
                    },
                  ],
                }"
                                :options="{
                  // Rendre le graphique responsive et conserver le ratio
                  responsive: true,
                  maintainAspectRatio: false,
                  plugins: {
                    // Position de la légende en haut
                    legend: {
                      position: 'top',
                    },
                  },
                }"
                            />
                        </div>
                    </div>
                </div>

                <!-- Section d'information utilisateur -->
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <!-- Message de bienvenue affichant le nom de l'utilisateur connecté -->
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

