<script setup>
// Import des composants Head (gestion balise <head>) et Link (liens Inertia)
import {Head, Link} from '@inertiajs/vue3';

// Définition des props reçues par ce composant
defineProps({
    // Booléen indiquant si l'on peut afficher le lien de connexion
    canLogin: {
        type: Boolean,
    },
    // Booléen indiquant si l'on peut afficher le lien d'inscription
    canRegister: {
        type: Boolean,
    },
    // Version Laravel (string, obligatoire)
    laravelVersion: {
        type: String,
        required: true,
    },
    // Version PHP (string, obligatoire)
    phpVersion: {
        type: String,
        required: true,
    },
});

// Fonction appelée en cas d'erreur de chargement d'image
function handleImageError() {
    // Cache certains éléments visuels en ajoutant la classe CSS "!hidden"
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
    <!-- Définit le titre de la page dans l’onglet navigateur -->
    <Head title="Welcome"/>

    <!-- Conteneur principal avec couleurs et styles (clair/sombre) -->
    <div class="bg-gray-50 text-gray/50 dark:bg-gray dark:text-white/50">
        <!-- Images de fond symétriques, positionnées absolument avec z-index négatif -->
        <img
            id="background"
            class="absolute -left-20 top-0 max-w-[877px]"
            src="/images/background.svg"
            style="z-index: -1;"
        />
        <img
            id="background"
            class="absolute -right-20 top-0 max-w-[877px] transform scale-x-[-1]"
            src="/images/background.svg"
            style="z-index: -1;"
        />

        <!-- Conteneur central aligné verticalement et horizontalement -->
        <div
            class="relative flex min-h-screen flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white"
        >
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <!-- En-tête avec grille : logo centré sur grand écran -->
                <header class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
                    <div class="flex lg:col-start-2 lg:justify-center">
                        <!-- Logo SoftToDo -->
                        <img
                            id="background"
                            class="h-40 w-auto lg:h-40"
                            src="/images/logo-softtodo.png"
                            alt="Logo"
                        />
                    </div>
                </header>

                <!-- Barre de navigation conditionnelle : affichée seulement si canLogin vrai -->
                <nav v-if="canLogin" class="-mx-3 flex flex-1 justify-center text-lg">
                    <!-- Si utilisateur connecté (présence de $page.props.auth.user) -->
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="rounded-md px-3 py-2 text-gray ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-black dark:hover:text-blue-700/80 dark:focus-visible:ring-white"
                    >
                        Dashboard
                    </Link>

                    <!-- Sinon (non connecté) affiche un lien vers la page login -->
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="rounded-md px-3 py-2 text-gray ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-black dark:hover:text-blue-700/80 dark:focus-visible:ring-white"
                        >
                            Log in
                        </Link>
                    </template>
                </nav>

                <!-- Pied de page simple centré avec copyright -->
                <footer class="py-16 text-center text-sm text-gray dark:text-black/70">
                    &copy; 2025 SoftToDo. All rights reserved.
                </footer>
            </div>
        </div>
    </div>
</template>

