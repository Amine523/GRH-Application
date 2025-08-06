<script setup>
import { computed } from 'vue'; // Import de computed pour les calculs réactifs
import GuestLayout from '@/Layouts/GuestLayout.vue'; // Import du layout pour invités (non connectés)
import PrimaryButton from '@/Components/PrimaryButton.vue'; // Import du composant bouton principal
import { Head, Link, useForm } from '@inertiajs/vue3'; // Import composants Inertia : gestion du head, liens, formulaire

// Définition des props reçues
const props = defineProps({
    status: { // Chaîne indiquant le statut actuel (ex: lien de vérification envoyé)
        type: String,
    },
});

// Initialisation d'un formulaire vide avec useForm d'Inertia
const form = useForm({});

// Fonction pour envoyer la requête de renvoi du mail de vérification
const submit = () => {
    form.post(route('verification.send')); // Post vers la route 'verification.send'
};

// Computed pour savoir si un lien de vérification vient d'être envoyé
const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent', // true si status correspond à la chaîne donnée
);
</script>

<template>
    <!-- Utilisation du layout Guest (visiteur) -->
    <GuestLayout>
        <!-- Titre de la page -->
        <Head title="Email Verification" />

        <!-- Message principal d'explication -->
        <div class="mb-4 text-sm text-gray-600">
            Thanks for signing up! Before getting started, could you verify your
            email address by clicking on the link we just emailed to you? If you
            didn't receive the email, we will gladly send you another.
        </div>

        <!-- Message de confirmation que le mail a été renvoyé (affiché conditionnellement) -->
        <div
            class="mb-4 text-sm font-medium text-green-600"
            v-if="verificationLinkSent"
        >
            A new verification link has been sent to the email address you
            provided during registration.
        </div>

        <!-- Formulaire pour renvoyer le mail de vérification -->
        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <!-- Bouton principal, désactivé et opaque pendant la requête -->
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Resend Verification Email
                </PrimaryButton>

                <!-- Lien bouton pour se déconnecter (logout) -->
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Log Out
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>

