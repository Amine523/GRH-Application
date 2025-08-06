<script setup>
// Import des composants réutilisables pour les inputs et les erreurs
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

// Import de useForm d'Inertia pour gérer le formulaire et ses erreurs
import { useForm } from '@inertiajs/vue3';

// Import de ref pour référencer les inputs et gérer le focus
import { ref } from 'vue';

// Références aux champs input pour pouvoir les manipuler (focus) en cas d'erreur
const passwordInput = ref(null);
const currentPasswordInput = ref(null);

// Initialisation du formulaire avec les champs requis pour la mise à jour du mot de passe
const form = useForm({
    current_password: '',       // Mot de passe actuel
    password: '',               // Nouveau mot de passe
    password_confirmation: '',  // Confirmation du nouveau mot de passe
});

// Fonction appelée à la soumission du formulaire
const updatePassword = () => {
    // Envoi de la requête PUT vers la route password.update (Laravel)
    form.put(route('password.update'), {
        preserveScroll: true, // Garde la position de scroll après la requête

        onSuccess: () => form.reset(), // Réinitialise le formulaire si succès

        onError: () => {
            // Si erreur sur le champ password, reset uniquement password et confirmation + focus dessus
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            // Si erreur sur le champ current_password, reset uniquement current_password + focus dessus
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <!-- En-tête avec titre et description -->
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Update Password
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Ensure your account is using a long, random password to stay secure.
            </p>
        </header>

        <!-- Formulaire de mise à jour du mot de passe -->
        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">

            <!-- Conteneur flex pour aligner horizontalement les champs current_password et password -->
            <div class="flex space-x-4">
                <!-- Champ Mot de passe actuel, occupe la moitié de la largeur -->
                <div class="flex-1">
                    <InputLabel for="current_password" value="Current Password" />
<TextInput
    id="current_password"
    ref="currentPasswordInput"
    v-model="form.current_password"
    type="password"
    class="mt-1 block w-full"
    autocomplete="current-password"
/>


                    <!-- Affiche les erreurs liées à current_password -->
                    <InputError :message="form.errors.current_password" class="mt-2" />
                </div>

                <!-- Champ Nouveau mot de passe, moitié de largeur -->
                <div class="flex-1">
                    <InputLabel for="password" value="New Password" />

                    <TextInput
                        id="password"
                        ref="passwordInput"                
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        autocomplete="new-password"
                    /> <!-- Reference pour focus si erreur -->

                    <!-- Affiche les erreurs liées au nouveau mot de passe -->
                    <InputError :message="form.errors.password" class="mt-2" />
                </div>
            </div>

            <!-- Conteneur flex pour champ confirmation mot de passe -->
            <div class="flex space-x-4">
                <div class="flex-1">
                    <InputLabel for="password_confirmation" value="Confirm Password" />

                    <TextInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        class="mt-1 block w-full"
                        autocomplete="new-password"
                    />

                    <!-- Affiche les erreurs de confirmation -->
                    <InputError :message="form.errors.password_confirmation" class="mt-2" />
                </div>
            </div>

            <!-- Bouton de soumission et message de succès -->
            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <!-- Transition pour afficher/disparaitre le message "Saved." -->
                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <!-- Message affiché seulement après un succès récent -->
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>

