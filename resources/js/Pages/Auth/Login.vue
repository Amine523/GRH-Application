<script setup>
import Checkbox from '@/Components/Checkbox.vue'; // Composant case à cocher
import GuestLayout from '@/Layouts/GuestLayout.vue'; // Layout pour utilisateur non connecté (invité)
import InputError from '@/Components/InputError.vue'; // Composant affichage erreurs formulaire
import InputLabel from '@/Components/InputLabel.vue'; // Composant label champ formulaire
import PrimaryButton from '@/Components/PrimaryButton.vue'; // Composant bouton principal
import TextInput from '@/Components/TextInput.vue'; // Composant input texte
import { Head, Link, useForm } from '@inertiajs/vue3'; // Head pour titre page, Link pour navigation Inertia, useForm pour gestion formulaire

// Props reçues depuis le serveur
defineProps({
    canResetPassword: { // Indique si on peut réinitialiser le mot de passe (affiche lien)
        type: Boolean,
    },
    status: { // Message de status à afficher (ex: succès connexion)
        type: String,
    },
});

// Initialisation du formulaire avec champs et valeurs initiales
const form = useForm({
    email: '', // Email utilisateur
    password: '', // Mot de passe
    remember: false, // Case "se souvenir de moi"
});

// Fonction appelée à la soumission du formulaire
const submit = () => {
    form.post(route('login'), { // Envoie via POST vers la route login de Laravel
        onFinish: () => form.reset('password'), // Reset le champ mot de passe après soumission (pour vider)
    });
};
</script>

<template>
    <GuestLayout> <!-- Utilisation du layout invité -->
        <Head title="Log in" /> <!-- Titre de la page -->

        <!-- Décorations graphiques d’arrière-plan -->
        <div class="bg-gray-50 text-gray/50 dark:bg-gray dark:text-white/50">
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
        </div>

        <!-- Affichage d’un message de status si existant -->
        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <!-- Formulaire de connexion -->
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
                /><!-- Liaison bidirectionnelle avec form.email -->

                <InputError class="mt-2" :message="form.errors.email" /> <!-- Affichage erreur email -->
            </div>

            <!-- Champ Mot de passe -->
            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" /> <!-- Affichage erreur mot de passe -->
            </div>

            <!-- Checkbox "Se souvenir de moi" -->
            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600">Remember me</span>
                </label>
            </div>

            <!-- Actions en bas du formulaire -->
            <div class="mt-4 flex items-center justify-end">
                <!-- Lien "Mot de passe oublié" si la réinitialisation est activée -->
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Forgot your password?
                </Link>

                <!-- Bouton de connexion -->
                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }" 
                    :disabled="form.processing" 
                ><!-- Diminue opacité lors du traitement -->
                <!-- Désactive bouton pendant requête -->
                    Log in
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
