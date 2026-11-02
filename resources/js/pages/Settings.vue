<script setup lang="ts">
import { Form, Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AuthController from '@/actions/App/Http/Controllers/AuthController';
import SettingsController from '@/actions/App/Http/Controllers/SettingsController';
import { dashboard } from '@/routes';
import campaigns from '@/routes/campaigns';
import characters from '@/routes/characters';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const avatarUrlInput = ref(user.value?.avatar_url ?? '');
const avatarLoadError = ref(false);
const currentTheme = ref<'light' | 'dark'>(
    user.value?.theme === 'dark' ? 'dark' : 'light',
);
const isUpdatingTheme = ref(false);
const confirmingDeletion = ref(false);

watch(
    () => user.value?.avatar_url,
    (newVal) => {
        avatarUrlInput.value = newVal ?? '';
        avatarLoadError.value = false;
    },
);

watch(
    () => user.value?.theme,
    (newTheme) => {
        if (newTheme === 'light' || newTheme === 'dark') {
            currentTheme.value = newTheme;
            document.documentElement.classList.toggle(
                'dark',
                newTheme === 'dark',
            );
        }
    },
    { immediate: true },
);

watch(avatarUrlInput, () => {
    avatarLoadError.value = false;
});

function selectTheme(nextTheme: 'light' | 'dark'): void {
    if (currentTheme.value === nextTheme || isUpdatingTheme.value) {
        return;
    }

    currentTheme.value = nextTheme;
    document.documentElement.classList.toggle('dark', nextTheme === 'dark');
    isUpdatingTheme.value = true;

    router.patch(
        SettingsController.updateTheme.url(),
        { theme: nextTheme },
        {
            preserveScroll: true,
            onFinish: () => {
                isUpdatingTheme.value = false;
            },
        },
    );
}

function toggleTheme(): void {
    selectTheme(currentTheme.value === 'dark' ? 'light' : 'dark');
}
</script>

<template>
    <div
        class="flex min-h-screen flex-col bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]"
    >
        <Head title="User Settings" />

        <header
            class="border-b border-[#e3e3e0] bg-white px-4 py-3 sm:px-6 sm:py-4 dark:border-[#3E3E3A] dark:bg-[#161615]"
        >
            <div
                class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-x-4 gap-y-2.5"
            >
                <span
                    class="order-1 text-sm font-bold tracking-wider uppercase sm:hidden"
                >
                    Dais
                </span>

                <nav
                    class="order-3 grid w-full grid-cols-4 gap-1 border-t border-[#e3e3e0] pt-2.5 text-center sm:order-1 sm:flex sm:w-auto sm:items-center sm:gap-6 sm:border-t-0 sm:pt-0 dark:border-[#3E3E3A]"
                >
                    <Link
                        :href="dashboard()"
                        class="rounded-xs px-2 py-1.5 text-xs text-[#706f6c] transition-colors hover:text-[#1b1b18] sm:p-0 sm:text-sm dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                    >
                        Dashboard
                    </Link>
                    <Link
                        :href="campaigns.index()"
                        class="rounded-xs px-2 py-1.5 text-xs text-[#706f6c] transition-colors hover:text-[#1b1b18] sm:p-0 sm:text-sm dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                    >
                        Campaigns
                    </Link>
                    <Link
                        :href="characters.index()"
                        class="rounded-xs px-2 py-1.5 text-xs text-[#706f6c] transition-colors hover:text-[#1b1b18] sm:p-0 sm:text-sm dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                    >
                        Characters
                    </Link>
                    <span
                        class="rounded-xs bg-[#1b1b18]/5 px-2 py-1.5 text-xs font-semibold tracking-tight sm:bg-transparent sm:p-0 sm:text-base dark:bg-[#EDEDEC]/10 sm:dark:bg-transparent"
                    >
                        Settings
                    </span>
                </nav>

                <div class="order-2 flex items-center gap-3 sm:gap-4">
                    <div v-if="user" class="flex items-center gap-2 sm:gap-2.5">
                        <img
                            v-if="user.avatar_url"
                            :src="user.avatar_url"
                            :alt="user.username"
                            class="h-7 w-7 shrink-0 rounded-full border border-[#e3e3e0] object-cover dark:border-[#3E3E3A]"
                        />
                        <span
                            v-else
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] text-xs font-semibold uppercase dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                        >
                            {{ user.username.charAt(0) }}
                        </span>
                        <span
                            class="max-w-[110px] truncate text-sm text-[#706f6c] sm:max-w-none dark:text-[#A1A09A]"
                        >
                            {{ user.username }}
                        </span>
                    </div>

                    <Form
                        v-bind="AuthController.logout.form()"
                        v-slot="{ processing }"
                    >
                        <button
                            type="submit"
                            :disabled="processing"
                            class="cursor-pointer rounded-sm border border-[#e3e3e0] px-3 py-1.5 text-xs font-medium transition-colors hover:border-[#1b1b18] disabled:opacity-50 dark:border-[#3E3E3A] dark:hover:border-[#EDEDEC]"
                        >
                            Log out
                        </button>
                    </Form>
                </div>
            </div>
        </header>

        <main
            class="mx-auto w-full max-w-3xl flex-1 space-y-5 px-4 py-5 sm:space-y-6 sm:p-6"
        >
            <div>
                <Link
                    :href="dashboard()"
                    class="inline-flex items-center py-1 text-xs font-medium tracking-wider text-[#706f6c] uppercase transition-colors hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                >
                    &larr; Back to dashboard
                </Link>
                <h1
                    class="mt-1 text-xl font-semibold tracking-tight sm:mt-2 sm:text-2xl"
                >
                    Account Settings
                </h1>
                <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Manage your theme preference, profile avatar, and account
                    security.
                </p>
            </div>

            <!-- Theme Toggle Section -->
            <section
                class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-8 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base font-semibold sm:text-lg">
                            Appearance
                        </h2>
                        <p
                            class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Switch between light and dark theme.
                        </p>
                    </div>

                    <div class="flex shrink-0 items-center gap-3">
                        <span
                            class="text-xs font-medium tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            {{ currentTheme }}
                        </span>
                        <button
                            type="button"
                            role="switch"
                            :aria-checked="currentTheme === 'dark'"
                            :disabled="isUpdatingTheme"
                            aria-label="Toggle theme"
                            class="relative inline-flex h-7 w-13 shrink-0 cursor-pointer items-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] p-0.5 transition-colors disabled:opacity-60 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                            @click="toggleTheme"
                        >
                            <span
                                class="inline-flex h-5.5 w-5.5 items-center justify-center rounded-full bg-[#1b1b18] text-white transition-transform dark:translate-x-6 dark:bg-[#EDEDEC] dark:text-[#1b1b18]"
                            >
                                <!-- Sun icon for light -->
                                <svg
                                    v-if="currentTheme === 'light'"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                    class="h-3.5 w-3.5"
                                >
                                    <path
                                        d="M10 2a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 10 2ZM10 15a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 10 15ZM10 7a3 3 0 1 0 0 6 3 3 0 0 0 0-6ZM15.657 5.404a.75.75 0 1 0-1.06-1.06l-1.061 1.06a.75.75 0 0 0 1.06 1.06l1.06-1.06ZM6.464 14.596a.75.75 0 1 0-1.06-1.06l-1.06 1.06a.75.75 0 0 0 1.06 1.06l1.06-1.06ZM18 10a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5A.75.75 0 0 1 18 10ZM5 10a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5A.75.75 0 0 1 5 10ZM14.596 15.657a.75.75 0 0 0 1.06-1.06l-1.06-1.061a.75.75 0 1 0-1.06 1.06l1.06 1.06ZM5.404 6.464a.75.75 0 0 0 1.06-1.06l-1.06-1.06a.75.75 0 1 0-1.061 1.06l1.06 1.06Z"
                                    />
                                </svg>
                                <!-- Moon icon for dark -->
                                <svg
                                    v-else
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                    class="h-3.5 w-3.5"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M7.455 2.004a.75.75 0 0 1 .26.77 7 7 0 0 0 9.958 7.967.75.75 0 0 1 1.067.853A8.5 8.5 0 1 1 6.647 1.921a.75.75 0 0 1 .808.083Z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
            </section>

            <!-- Avatar Settings -->
            <section
                class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-8 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <h2 class="text-base font-semibold sm:text-lg">Avatar</h2>
                <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Provide an image URL for your profile avatar.
                </p>

                <Form
                    v-bind="SettingsController.updateProfile.form()"
                    v-slot="{ errors, processing, recentlySuccessful }"
                    class="mt-5 space-y-5 sm:mt-6"
                >
                    <div
                        class="flex flex-col items-start gap-4 sm:flex-row sm:items-center"
                    >
                        <img
                            v-if="avatarUrlInput && !avatarLoadError"
                            :src="avatarUrlInput"
                            alt="Avatar preview"
                            class="h-16 w-16 shrink-0 rounded-full border border-[#e3e3e0] object-cover dark:border-[#3E3E3A]"
                            @error="avatarLoadError = true"
                        />
                        <div
                            v-else
                            class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] text-lg font-semibold uppercase dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                        >
                            {{ user?.username?.charAt(0) ?? '?' }}
                        </div>

                        <div class="w-full min-w-0 flex-1">
                            <label
                                for="avatar_url"
                                class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                            >
                                Avatar URL
                            </label>
                            <input
                                id="avatar_url"
                                v-model="avatarUrlInput"
                                name="avatar_url"
                                type="url"
                                placeholder="https://example.com/avatar.png"
                                class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3.5 py-2 text-sm text-[#1b1b18] placeholder-[#706f6c]/60 shadow-2xs transition-colors focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:placeholder-[#A1A09A]/50 dark:focus:border-[#EDEDEC]"
                                :aria-invalid="Boolean(errors.avatar_url)"
                            />
                            <p
                                v-if="errors.avatar_url"
                                class="mt-1.5 text-xs text-[#f53003] dark:text-[#FF4433]"
                            >
                                {{ errors.avatar_url }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 sm:gap-4">
                        <button
                            type="submit"
                            :disabled="processing"
                            class="w-full cursor-pointer rounded-sm border border-black bg-[#1b1b18] px-5 py-2.5 text-sm font-medium text-white transition-colors hover:border-black hover:bg-black disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto sm:py-2 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                        >
                            <span v-if="processing">Saving...</span>
                            <span v-else>Save avatar</span>
                        </button>

                        <span
                            v-if="recentlySuccessful"
                            class="text-sm text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Saved.
                        </span>
                    </div>
                </Form>
            </section>

            <!-- Password Settings -->
            <section
                class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-8 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <h2 class="text-base font-semibold sm:text-lg">
                    Change Password
                </h2>
                <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Ensure your account is using a strong password to stay
                    secure.
                </p>

                <Form
                    v-bind="SettingsController.updatePassword.form()"
                    reset-on-success
                    :reset-on-error="[
                        'current_password',
                        'password',
                        'password_confirmation',
                    ]"
                    v-slot="{ errors, processing, recentlySuccessful }"
                    class="mt-5 space-y-5 sm:mt-6"
                >
                    <div>
                        <label
                            for="current_password"
                            class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                        >
                            Current password
                        </label>
                        <input
                            id="current_password"
                            name="current_password"
                            type="password"
                            autocomplete="current-password"
                            required
                            placeholder="••••••••"
                            class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3.5 py-2 text-sm text-[#1b1b18] placeholder-[#706f6c]/60 shadow-2xs transition-colors focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:placeholder-[#A1A09A]/50 dark:focus:border-[#EDEDEC]"
                            :aria-invalid="Boolean(errors.current_password)"
                        />
                        <p
                            v-if="errors.current_password"
                            class="mt-1.5 text-xs text-[#f53003] dark:text-[#FF4433]"
                        >
                            {{ errors.current_password }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="password"
                            class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                        >
                            New password
                        </label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            required
                            placeholder="••••••••"
                            class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3.5 py-2 text-sm text-[#1b1b18] placeholder-[#706f6c]/60 shadow-2xs transition-colors focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:placeholder-[#A1A09A]/50 dark:focus:border-[#EDEDEC]"
                            :aria-invalid="Boolean(errors.password)"
                        />
                        <p
                            v-if="errors.password"
                            class="mt-1.5 text-xs text-[#f53003] dark:text-[#FF4433]"
                        >
                            {{ errors.password }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="password_confirmation"
                            class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                        >
                            Confirm new password
                        </label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            required
                            placeholder="••••••••"
                            class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3.5 py-2 text-sm text-[#1b1b18] placeholder-[#706f6c]/60 shadow-2xs transition-colors focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:placeholder-[#A1A09A]/50 dark:focus:border-[#EDEDEC]"
                            :aria-invalid="
                                Boolean(errors.password_confirmation)
                            "
                        />
                        <p
                            v-if="errors.password_confirmation"
                            class="mt-1.5 text-xs text-[#f53003] dark:text-[#FF4433]"
                        >
                            {{ errors.password_confirmation }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 sm:gap-4">
                        <button
                            type="submit"
                            :disabled="processing"
                            class="w-full cursor-pointer rounded-sm border border-black bg-[#1b1b18] px-5 py-2.5 text-sm font-medium text-white transition-colors hover:border-black hover:bg-black disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto sm:py-2 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                        >
                            <span v-if="processing">Updating...</span>
                            <span v-else>Update password</span>
                        </button>

                        <span
                            v-if="recentlySuccessful"
                            class="text-sm text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Password updated.
                        </span>
                    </div>
                </Form>
            </section>

            <!-- Delete Account Section -->
            <section
                class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(245,48,3,0.25)] sm:p-8 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_rgba(255,68,51,0.3)]"
            >
                <h2
                    class="text-base font-semibold text-[#f53003] sm:text-lg dark:text-[#FF4433]"
                >
                    Delete Account
                </h2>
                <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Once your account is deleted, all of its resources and data
                    will be permanently deleted.
                </p>

                <div v-if="!confirmingDeletion" class="mt-5 sm:mt-6">
                    <button
                        type="button"
                        class="w-full cursor-pointer rounded-sm border border-[#f53003] bg-[#f53003] px-5 py-2.5 text-sm font-medium text-white transition-opacity hover:opacity-90 sm:w-auto sm:py-2 dark:border-[#FF4433] dark:bg-[#FF4433]"
                        @click="confirmingDeletion = true"
                    >
                        Delete account
                    </button>
                </div>

                <Form
                    v-else
                    v-bind="SettingsController.destroy.form()"
                    :reset-on-error="['password']"
                    v-slot="{ errors, processing }"
                    class="mt-5 space-y-4 sm:mt-6"
                >
                    <div>
                        <label
                            for="delete_password"
                            class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                        >
                            Enter your password to confirm account deletion
                        </label>
                        <input
                            id="delete_password"
                            v-focus
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            placeholder="••••••••"
                            class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3.5 py-2 text-sm text-[#1b1b18] placeholder-[#706f6c]/60 shadow-2xs transition-colors focus:border-[#f53003] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:placeholder-[#A1A09A]/50 dark:focus:border-[#FF4433]"
                            :aria-invalid="Boolean(errors.password)"
                        />
                        <p
                            v-if="errors.password"
                            class="mt-1.5 text-xs text-[#f53003] dark:text-[#FF4433]"
                        >
                            {{ errors.password }}
                        </p>
                    </div>

                    <div
                        class="flex flex-col items-stretch gap-2.5 sm:flex-row sm:items-center sm:gap-3"
                    >
                        <button
                            type="submit"
                            :disabled="processing"
                            class="cursor-pointer rounded-sm border border-[#f53003] bg-[#f53003] px-5 py-2.5 text-sm font-medium text-white transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60 sm:py-2 dark:border-[#FF4433] dark:bg-[#FF4433]"
                        >
                            <span v-if="processing">Deleting...</span>
                            <span v-else>Confirm delete account</span>
                        </button>

                        <button
                            type="button"
                            :disabled="processing"
                            class="cursor-pointer rounded-sm border border-[#e3e3e0] px-4 py-2.5 text-sm font-medium transition-colors hover:border-[#1b1b18] sm:py-2 dark:border-[#3E3E3A] dark:hover:border-[#EDEDEC]"
                            @click="confirmingDeletion = false"
                        >
                            Cancel
                        </button>
                    </div>
                </Form>
            </section>
        </main>
    </div>
</template>
