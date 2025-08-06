<script setup>
// Import des fonctionnalités et composants nécessaires
import { ref } from 'vue';
import InputError from '@/Components/InputError.vue';        // Affiche les messages d'erreur
import InputLabel from '@/Components/InputLabel.vue';        // Labels pour les champs
import PrimaryButton from '@/Components/PrimaryButton.vue';  // Boutons primaires stylisés
import TextInput from '@/Components/TextInput.vue';          // Champs input stylisés
import { useForm, usePage } from '@inertiajs/vue3';          // Gestion formulaire + accès aux props Inertia

// Récupération de l'utilisateur connecté depuis les props Inertia
const user = usePage().props.auth.user;

// Déstructuration des infos utilisateur récupérées depuis la page (profil)
const { first_name, last_name, address, phone_number, profile_picture } = usePage().props;

// Initialisation du formulaire avec les données actuelles (ou chaînes vides)
const form = useForm({
    first_name: first_name || '',
    last_name: last_name || '',
    address: address || '',
    phone_number: phone_number || '',
    profile_picture: null,    // Pour le fichier image uploadé (input type file)
    profile_file: null,       // Apparemment non utilisé dans ce code, pourrait être supprimé
    _method : 'patch'         // Pour indiquer la méthode HTTP PATCH (mise à jour)
});

// Booléen pour activer/désactiver le mode édition des champs
const isEditing = ref(false);

// Active le mode édition (autorise la modification des champs)
const enableEditing = () => {
    isEditing.value = true;
};

// Soumission du formulaire vers la route profile.update
const submitForm = () => {
    form.post(route('profile.update'), {
        forceFormData: true, // Important pour envoyer les fichiers via FormData
        onSuccess: () => {
            isEditing.value = false; // Désactive mode édition après succès
        }
    });
};
</script>

<template>
    <section>
        <!-- En-tête avec titre et description -->
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Update your account's profile information and email address.
            </p>
        </header>

        <!-- Formulaire -->
        <form @submit.prevent="submitForm" class="mt-3 space-y-3">

            <!-- Ligne pour prénom et nom -->
            <div class="flex space-x-4">
                <!-- Prénom -->
                <div class="w-1/2">
                    <InputLabel for="first_name" value="First Name" />

                    <TextInput
                        id="first_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.first_name"
                        :disabled="!isEditing"               
                        :class="!isEditing ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : ''"
                        autofocus
                        autocomplete="first_name"
                    />     <!-- Désactive quand pas en édition -->

                    <InputError class="mt-2" :message="form.errors.first_name" />
                </div>

                <!-- Nom -->
                <div class="w-1/2">
                    <InputLabel for="last_name" value="Last Name" />

                    <TextInput
                        id="last_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.last_name"
                        :disabled="!isEditing"
                        :class="!isEditing ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : ''"
                        autocomplete="last_name"
                    />

                    <InputError class="mt-2" :message="form.errors.last_name" />
                </div>
            </div>

            <!-- Ligne pour téléphone, adresse, et upload photo -->
            <div class="flex space-x-4">

                <!-- Téléphone -->
                <div class="w-1/3">
                    <InputLabel for="phone_number" value="Phone Number" />

                    <TextInput
                        id="phone_number"
                        type="tel"
                        class="mt-1 block w-full"
                        v-model="form.phone_number"
                        :disabled="!isEditing"
                        :class="!isEditing ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : ''"
                        autocomplete="phone"
                    />

                    <InputError class="mt-2" :message="form.errors.phone_number" />
                </div>

                <!-- Adresse -->
                <div class="w-1/3">
                    <InputLabel for="address" value="Address" />

                    <TextInput
                        id="address"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.address"
                        :disabled="!isEditing"
                        :class="!isEditing ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : ''"
                        autocomplete="address"
                    />

                    <InputError class="mt-2" :message="form.errors.address" />
                </div>

                <!-- Upload photo profil -->
                <div class="w-1/3">
                    <InputLabel for="profile_picture" value="Profile Picture" />

                    <!-- input file pour sélectionner une image -->
                    <input
                        id="profile_picture"
                        type="file"
                        class="mt-1 block w-full"
                        @change="event => form.profile_picture = event.target.files[0]" 
                        :disabled="!isEditing"
                    /> <!-- Mise à jour du fichier dans form -->

                    <InputError class="mt-2" :message="form.errors.profile_picture" />
                </div>
            </div>

            <!-- Boutons -->
            <div class="flex items-center gap-4">
                <!-- Bouton sauvegarder, désactivé si en traitement ou si pas en édition -->
                <PrimaryButton :disabled="form.processing || !isEditing" type="submit">
                    Save
                </PrimaryButton>

                <!-- Bouton éditer, désactivé si déjà en édition ou en traitement -->
                <PrimaryButton
                    type="button"
                    @click="enableEditing"
                    :disabled="isEditing || form.processing"
                    class="bg-blue-600 hover:bg-blue-700 focus:ring-blue-500"
                >
                    Edit
                </PrimaryButton>

                <!-- Message de succès après sauvegarde -->
                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition ease-in-out"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Saved successfully!
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>

