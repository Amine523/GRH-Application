<?php

// Importation des contrôleurs d'authentification nécessaires pour gérer les routes liées à la connexion, inscription, mot de passe, etc.
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;

// Import de la façade Route pour définir les routes HTTP
use Illuminate\Support\Facades\Route;

// Groupe de routes accessibles uniquement aux utilisateurs non authentifiés (invités)
Route::middleware('guest')->group(function () {
    // Affiche le formulaire d'inscription
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    // Traite la soumission du formulaire d'inscription (création d'un nouvel utilisateur)
    Route::post('register', [RegisteredUserController::class, 'store']);

    // Affiche le formulaire de connexion
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    // Traite la soumission du formulaire de connexion (authentification)
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Affiche le formulaire pour demander un lien de réinitialisation de mot de passe
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    // Envoie un e-mail contenant le lien de réinitialisation de mot de passe
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    // Affiche le formulaire de saisie du nouveau mot de passe via le token reçu par e-mail
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    // Traite la soumission du formulaire de nouveau mot de passe
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

// Groupe de routes accessibles uniquement aux utilisateurs authentifiés (connectés)
Route::middleware('auth')->group(function () {
    // Affiche la page demandant à l'utilisateur de vérifier son e-mail
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    // Route pour vérifier l'e-mail via un lien signé et limité en fréquence (throttle)
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    // Envoie une nouvelle notification de vérification d'e-mail, limitée à 6 envois par minute
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Affiche le formulaire demandant la confirmation du mot de passe (pour actions sensibles)
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    // Traite la confirmation du mot de passe soumise par l'utilisateur
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    // Met à jour le mot de passe de l'utilisateur connecté
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    // Déconnecte (logout) l'utilisateur actuel
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
