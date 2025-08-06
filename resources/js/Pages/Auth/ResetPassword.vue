<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'; // Layout pour utilisateur non connecté (invité)
import InputError from '@/Components/InputError.vue'; // Composant affichant un message d'erreur sous un input
import InputLabel from '@/Components/InputLabel.vue'; // Composant label pour un champ de formulaire
import PrimaryButton from '@/Components/PrimaryButton.vue'; // Composant bouton principal
import TextInput from '@/Components/TextInput.vue'; // Composant champ texte
import { Head, useForm } from '@inertiajs/vue3'; // Head pour titre de page, useForm pour gestion formulaire

// Définition des props reçues du backend (Laravel)
const props = defineProps({
    email: { // email envoyé par le backend pour préremplir le champ
        type: String,
        required: true,
    },
    token: { // token de réinitialisation du mot de passe
        type: String,
        required: true,
    },
});

// Initialisation du formulaire avec les champs nécessaires
const form = useForm({
    token: props.token, // token pour valider la réinitialisation
    email: props.email, // email prérempli
    password: '', // nouveau mot de passe
    password_confirmation: '', // confirmation du nouveau mot de passe
});

// Fonction de soumission du formulaire
const submit = () => {
    form.post(route('password.store'), { // envoi POST vers la route Laravel 'password.store'
        onFinish: () => form.reset('password', 'password_confirmation'), // reset des champs password après la requête
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password" /> <!-- Titre de la page -->

        <form @submit.prevent="submit">
            <!-- Champ Email -->
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email" 
                    required
                    autofocus
                    autocomplete="username"
                /><!-- liaison bidirectionnelle avec le formulaire -->

                <InputError class="mt-2" :message="form.errors.email" /> <!-- Affiche les erreurs liées à l'email -->
            </div>

            <!-- Champ Nouveau mot de passe -->
            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" /> <!-- Affiche erreurs mot de passe -->
            </div>

            <!-- Confirmation du nouveau mot de passe -->
            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirm Password" />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password_confirmation" /> <!-- Erreurs confirmation -->
            </div>

            <!-- Bouton soumettre -->
            <div class="mt-4 flex items-center justify-end">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }" 
                    :disabled="form.processing"
                > <!-- rend le bouton semi-transparent pendant traitement -->
                 <!-- désactive le bouton pendant traitement -->
                    Reset Password
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

