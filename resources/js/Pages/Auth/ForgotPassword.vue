<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'; // Layout pour utilisateurs non connectés (invités)
import InputError from '@/Components/InputError.vue'; // Composant pour afficher les erreurs de formulaire
import InputLabel from '@/Components/InputLabel.vue'; // Composant pour les labels de formulaire
import PrimaryButton from '@/Components/PrimaryButton.vue'; // Composant bouton principal
import TextInput from '@/Components/TextInput.vue'; // Composant champ texte
import { Head, useForm } from '@inertiajs/vue3'; // Head pour titre, useForm pour gestion formulaire Inertia

// Propriétés reçues depuis le serveur (ex: message de status)
defineProps({
    status: {
        type: String,
    },
});

// Initialisation du formulaire avec un champ email vide
const form = useForm({
    email: '',
});

// Fonction déclenchée lors de la soumission du formulaire
const submit = () => {
    form.post(route('password.email')); // Envoie le formulaire en POST vers la route Laravel pour envoyer le mail de réinitialisation
};
</script>

<template>
    <GuestLayout> <!-- Utilisation du layout invité -->
        <Head title="Forgot Password" /> <!-- Titre de la page -->

        <!-- Texte explicatif -->
        <div class="mb-4 text-sm text-gray-600">
            Forgot your password? No problem. Just let us know your email
            address and we will email you a password reset link that will allow
            you to choose a new one.
        </div>

        <!-- Affiche un message de succès si la prop status est définie -->
        <div
            v-if="status"
            class="mb-4 text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <!-- Formulaire de demande de lien de réinitialisation -->
        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" /> <!-- Label du champ email -->

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                /> <!-- Liaison bidirectionnelle avec le champ email du formulaire -->

                <InputError class="mt-2" :message="form.errors.email" /> <!-- Affiche les erreurs liées à l’email -->
            </div>

            <!-- Bouton pour envoyer la demande -->
            <div class="mt-4 flex items-center justify-end">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }" 
                    :disabled="form.processing" 
                ><!-- Réduit l’opacité lors du traitement -->
                <!-- Désactive le bouton pendant l’envoi -->
                    Email Password Reset Link
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
