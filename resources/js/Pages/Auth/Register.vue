<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'; // Layout pour les utilisateurs invités (non connectés)
import InputError from '@/Components/InputError.vue'; // Composant pour afficher les erreurs sous les inputs
import InputLabel from '@/Components/InputLabel.vue'; // Composant label pour les champs de formulaire
import PrimaryButton from '@/Components/PrimaryButton.vue'; // Composant bouton principal
import TextInput from '@/Components/TextInput.vue'; // Composant champ texte
import { Head, Link, useForm } from '@inertiajs/vue3'; // Head pour titre page, Link pour lien Inertia, useForm pour gestion formulaire

// Initialisation du formulaire avec les champs à remplir
const form = useForm({
    name: '', // nom complet
    email: '', // email
    password: '', // mot de passe
    password_confirmation: '', // confirmation mot de passe
    role: '', // role
});

// Fonction qui gère la soumission du formulaire
const submit = () => {
    form.post(route('register'), { // envoi des données à la route Laravel 'register' via POST
        onFinish: () => form.reset('password', 'password_confirmation'), // reset les champs password après la requête
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />
 <!-- Titre de la page -->
        <form @submit.prevent="submit"> <!-- Empêche le rechargement de page et appelle submit() -->
            <!-- Champ Nom -->
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name" 
                    required
                    autofocus
                    autocomplete="name"
                /><!-- liaison bidirectionnelle avec le formulaire -->

                <InputError class="mt-2" :message="form.errors.name" /> <!-- Affiche erreur pour le champ name -->
            </div>
            <div class="mt-4">
                <InputLabel for="employee-role" value="Role" />
                <select id="employee-role" v-model="form.role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="developer">Web Developer / Full Stack Developer</option>
                    <option value="project-manager">Project Manager</option>
                    <option value="uiux">UI/UX Designer</option>
                    <option value="qa">Tester / Quality Assurance</option>
                    <option value="cto">Technical Lead / CTO</option>
                    <option value="support">Technical Support / Maintenance</option>
                    <option value="intern">Intern / Trainee</option>
                </select>
                <InputError class="mt-2" :message="form.errors.role" />
            </div>
            <!-- Champ Email -->
            <div class="mt-4">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" /> <!-- Affiche erreur email -->
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
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" /> <!-- Erreur mot de passe -->
            </div>

            <!-- Confirmation mot de passe -->
            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                /> <!-- Erreur confirmation mot de passe -->
            </div>

    
            <!-- Lien vers page login + bouton d'inscription -->
            <div class="mt-4 flex items-center justify-end">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Already registered? <!-- Déjà inscrit ? -->
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }" 
                    :disabled="form.processing" 
                ><!-- Diminue l'opacité pendant traitement -->
                <!-- Désactive le bouton pendant traitement -->
                    Register <!-- Bouton s'inscrire -->
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
