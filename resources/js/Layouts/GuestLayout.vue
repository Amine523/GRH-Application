<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue'; // Import du composant logo (non utilisé ici)
import { Link } from '@inertiajs/vue3'; // Import du composant Link pour les liens Inertia
</script>

<template>
    <!-- Conteneur principal centré, en colonne, avec hauteur minimale de l'écran et fond gris -->
    <div
        class="flex min-h-screen flex-col items-center bg-gray pt-6 sm:justify-center sm:pt-0"
    >
        <!-- Zone logo cliquable qui redirige vers la page d'accueil -->
        <div>
            <Link href="/">
                <img
                    id="background"
                    class="h-10 w-auto lg:h-32"  
                    src="/images/logo-softtodo.png" 
                    alt="Logo" 
                /><!-- Hauteur 10 unités sur petits écrans, 32 sur grands écrans -->
                <!-- Source de l'image logo -->
                 <!-- Texte alternatif pour accessibilité -->
            </Link>
        </div>

        <!-- Conteneur pour le contenu inséré via slot -->
        <div
            class="mt-6 w-full px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg border-cyan-700">
            <slot /> <!-- Permet d'insérer un contenu dynamique dans ce composant -->
        </div>
    </div>
</template>

