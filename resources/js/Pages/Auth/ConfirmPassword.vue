<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'; // Layout pour utilisateur non connecté (invité)
import InputError from '@/Components/InputError.vue'; // Composant pour afficher les erreurs de formulaire
import InputLabel from '@/Components/InputLabel.vue'; // Composant pour afficher les labels des inputs
import PrimaryButton from '@/Components/PrimaryButton.vue'; // Composant pour le bouton principal
import TextInput from '@/Components/TextInput.vue'; // Composant pour les champs texte
import { Head, useForm } from '@inertiajs/vue3'; // Head pour titre, useForm pour gérer formulaire avec Inertia

// Initialisation du formulaire avec un champ password vide
const form = useForm({
    password: '',
});

// Fonction déclenchée à la soumission du formulaire
const submit = () => {
    form.post(route('password.confirm'), { // Envoie une requête POST à la route Laravel pour confirmer le mot de passe
        onFinish: () => form.reset(), // Réinitialise le formulaire après l'envoi
    });
};
</script>

<template>
    <GuestLayout> <!-- Utilise le layout invité -->
        <Head title="Confirm Password" /> <!-- Titre de la page -->

        <!-- Message informatif -->
        <div class="mb-4 text-sm text-gray-600">
            This is a secure area of the application. Please confirm your
            password before continuing.
        </div>

        <!-- Formulaire de confirmation du mot de passe -->
        <form @submit.prevent="submit">
            <div>
                <InputLabel for="password" value="Password" /> <!-- Label du champ mot de passe -->

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password" 
                    required
                    autocomplete="current-password"
                    autofocus
                /><!-- Liaison bidirectionnelle avec le champ password -->

                <InputError class="mt-2" :message="form.errors.password" /> <!-- Affiche les erreurs liées au mot de passe -->
            </div>

            <!-- Bouton pour confirmer le mot de passe -->
            <div class="mt-4 flex justify-end">
                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }" 
                    :disabled="form.processing"
                ><!-- Réduit l'opacité quand le formulaire est en cours d'envoi -->  <!-- Désactive le bouton pendant le traitement -->
                    Confirm
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
