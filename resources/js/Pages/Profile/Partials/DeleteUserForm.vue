<script setup>
// Importation des composants nécessaires depuis le dossier Components
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

// Importation de fonctions et hooks nécessaires depuis Inertia.js et Vue
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

// Référence pour gérer l'affichage de la fenêtre modale
const confirmingUserDeletion = ref(false);

// Référence vers l'input du mot de passe
const passwordInput = ref(null);

// Initialisation du formulaire avec un champ "password"
const form = useForm({
    password: '',
});

// Fonction appelée lorsqu'on clique sur "Supprimer le compte"
const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true; // Affiche la fenêtre modale

    // Donne le focus à l'input du mot de passe après le rendu de la modale
    nextTick(() => passwordInput.value.focus());
};

// Fonction pour supprimer le compte utilisateur
const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true, // Garde le scroll à la même position
        onSuccess: () => closeModal(), // Ferme la modale si suppression réussie
        onError: () => passwordInput.value.focus(), // Redonne le focus si erreur
        onFinish: () => form.reset(), // Réinitialise le formulaire
    });
};

// Fonction pour fermer la fenêtre modale et réinitialiser le formulaire
const closeModal = () => {
    confirmingUserDeletion.value = false; // Cache la modale
    form.clearErrors(); // Supprime les erreurs affichées
    form.reset(); // Réinitialise les champs
};
</script>

<template>
    <!-- Section principale avec espacement vertical -->
    <section class="space-y-6">
        <!-- En-tête avec le titre et la description -->
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Delete Account <!-- Titre : Supprimer le compte -->
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                <!-- Message d’avertissement -->
                Once your account is deleted, all of its resources and data will
                be permanently deleted. Before deleting your account, please
                download any data or information that you wish to retain.
            </p>
        </header>

        <!-- Bouton pour ouvrir la modale de confirmation -->
        <DangerButton @click="confirmUserDeletion">Delete Account</DangerButton>

        <!-- Fenêtre modale affichée uniquement si "confirmingUserDeletion" est vrai -->
        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <!-- Titre dans la modale -->
                <h2 class="text-lg font-medium text-gray-900">
                    Are you sure you want to delete your account?
                </h2>

                <!-- Message d'avertissement supplémentaire -->
                <p class="mt-1 text-sm text-gray-600">
                    Once your account is deleted, all of its resources and data
                    will be permanently deleted. Please enter your password to
                    confirm you would like to permanently delete your account.
                </p>

                <!-- Champ mot de passe -->
                <div class="mt-6">
                    <!-- Label masqué (pour l'accessibilité) -->
                    <InputLabel
                        for="password"
                        value="Password"
                        class="sr-only"
                    />

                    <!-- Champ de saisie du mot de passe -->
                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-3/4"
                        placeholder="Password"
                        @keyup.enter="deleteUser" />
                        <!-- Supprime si entrée pressée -->
                    

                    <!-- Affiche les erreurs du mot de passe -->
                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <!-- Boutons d'action : Annuler / Supprimer -->
                <div class="mt-6 flex justify-end">
                    <!-- Bouton Annuler -->
                    <SecondaryButton @click="closeModal">
                        Cancel
                    </SecondaryButton>

                    <!-- Bouton Supprimer -->
     <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >Delete Account</DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>

