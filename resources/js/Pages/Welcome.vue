<script setup>
import {Head, Link} from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
    <Head title="Welcome"/>
    <div class="bg-gray-50 text-gray/50 dark:bg-gray dark:text-white/50">
        <img
            id="background"
            class="absolute -left-20 top-0 max-w-[877px]"
            src="/storage/background.svg"
        />
        <img
            id="background"
            class="absolute -right-20 top-0 max-w-[877px] transform scale-x-[-1]"
            src="/storage/background.svg"
        />
        <div
            class="relative flex min-h-screen flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white"
        >
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <header
                    class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
                    <div class="flex lg:col-start-2 lg:justify-center">
                        <img
                            id="background"
                            class="h-40 w-auto lg:h-40"
                        src="/storage/logo-softtodo.png"
                        alt="Logo"
                        />
                    </div>
                </header>
                <nav v-if="canLogin" class="-mx-3 flex flex-1 justify-center text-lg">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="rounded-md px-3 py-2 text-gray ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-black dark:hover:text-blue-700/80 dark:focus-visible:ring-white">
                        Dashboard
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="rounded-md px-3 py-2 text-gray ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-black dark:hover:text-blue-700/80 dark:focus-visible:ring-white">
                            Log in
                        </Link>
                    </template>
                </nav>
                <footer
                    class="py-16 text-center text-sm text-gray dark:text-black/70">
                    &copy; 2024 SoftToDo. All rights reserved.
                </footer>
            </div>
        </div>
    </div>
</template>
