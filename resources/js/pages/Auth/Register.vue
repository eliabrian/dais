<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import AuthController from '@/actions/App/Http/Controllers/AuthController';
import { home, login } from '@/routes';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-[#FDFDFC] px-4 py-6 text-[#1b1b18] sm:p-6 dark:bg-[#0a0a0a] dark:text-[#EDEDEC]"
    >
        <Head title="Register" />

        <div class="w-full max-w-md">
            <div
                class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-8 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <div class="mb-6">
                    <Link
                        :href="home()"
                        class="inline-flex items-center py-1 text-xs font-medium tracking-wider text-[#706f6c] uppercase transition-colors hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                    >
                        &larr; Back to home
                    </Link>
                    <h1
                        class="mt-2 text-xl font-semibold tracking-tight sm:mt-3 sm:text-2xl"
                    >
                        Create an account
                    </h1>
                    <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Enter your details below to get started.
                    </p>
                </div>

                <Form
                    v-bind="AuthController.register.form()"
                    :reset-on-error="['password', 'password_confirmation']"
                    v-slot="{ errors, processing }"
                    class="space-y-5"
                >
                    <div>
                        <label
                            for="username"
                            class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                        >
                            Username
                        </label>
                        <input
                            id="username"
                            v-focus
                            name="username"
                            type="text"
                            autocomplete="username"
                            required
                            placeholder="adventurer"
                            class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3.5 py-2 text-sm text-[#1b1b18] placeholder-[#706f6c]/60 shadow-2xs transition-colors focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:placeholder-[#A1A09A]/50 dark:focus:border-[#EDEDEC]"
                            :aria-invalid="Boolean(errors.username)"
                        />
                        <p
                            v-if="errors.username"
                            class="mt-1.5 text-xs text-[#f53003] dark:text-[#FF4433]"
                        >
                            {{ errors.username }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="email"
                            class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                        >
                            Email address
                        </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            placeholder="you@example.com"
                            class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3.5 py-2 text-sm text-[#1b1b18] placeholder-[#706f6c]/60 shadow-2xs transition-colors focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:placeholder-[#A1A09A]/50 dark:focus:border-[#EDEDEC]"
                            :aria-invalid="Boolean(errors.email)"
                        />
                        <p
                            v-if="errors.email"
                            class="mt-1.5 text-xs text-[#f53003] dark:text-[#FF4433]"
                        >
                            {{ errors.email }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="password"
                            class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                        >
                            Password
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
                            Confirm password
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

                    <button
                        type="submit"
                        :disabled="processing"
                        class="flex w-full cursor-pointer items-center justify-center rounded-sm border border-black bg-[#1b1b18] px-5 py-2 text-sm font-medium text-white transition-colors hover:border-black hover:bg-black disabled:cursor-not-allowed disabled:opacity-60 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                    >
                        <span v-if="processing">Creating account...</span>
                        <span v-else>Create account</span>
                    </button>
                </Form>

                <p
                    class="mt-6 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]"
                >
                    Already have an account?
                    <Link
                        :href="login()"
                        class="font-medium text-[#f53003] underline underline-offset-4 hover:opacity-90 dark:text-[#FF4433]"
                    >
                        Sign in
                    </Link>
                </p>
            </div>
        </div>
    </div>
</template>
