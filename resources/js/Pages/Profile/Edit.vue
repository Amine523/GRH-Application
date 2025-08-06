<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // Import du layout pour les utilisateurs authentifiés
import DeleteUserForm from './Partials/DeleteUserForm.vue'; // Import du composant formulaire suppression compte
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue'; // Import du formulaire de mise à jour mot de passe
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue'; // Import du formulaire mise à jour profil
import { Head } from '@inertiajs/vue3'; // Import composant Head pour gérer le titre de la page

// Définition des props reçues par ce composant
defineProps({
    mustVerifyEmail: { // Booléen indiquant si l'utilisateur doit vérifier son email
        type: Boolean,
    },
    status: { // Chaîne de caractères pour afficher un message de statut (ex: succès de mise à jour)
        type: String,
    },
});
</script>

<template>
    <!-- Titre de la page dans l'onglet navigateur -->
    <Head title="Profile" />

    <!-- Utilisation du layout avec une section header -->
    <AuthenticatedLayout>
        <template #header>
            <!-- Titre visible en haut de la page -->
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Profile
            </h2>
        </template>

        <div class="py-12"> <!-- Padding vertical pour espacer le contenu -->
            <div class="mx-auto space-y-6 sm:px-6 lg:px-8"> <!-- Conteneur centré avec espacements -->
                
                <!-- Section mise à jour des informations du profil -->
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                     <!-- Passage de la prop -->
                </div>

                <!-- Section mise à jour du mot de passe -->
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <UpdatePasswordForm />
                </div>

                <!-- (Optionnel) Section suppression du compte utilisateur -->
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>


