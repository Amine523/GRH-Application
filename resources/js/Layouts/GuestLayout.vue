<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <img
                    id="background"
                    class="h-10 w-auto lg:h-32"
                    src="/images/logo-softtodo.png"
                    alt="Logo"
                />
            </Link>
        </div>

        <div
            class="mt-6 w-full px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg border-cyan-700">
            <slot />
        </div>
    </div>
</template>
