<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AuthController from '@/actions/App/Http/Controllers/AuthController';
import campaigns from '@/routes/campaigns';
import characters from '@/routes/characters';
import settings from '@/routes/settings';
import type { Campaign } from '@/types';

const props = withDefaults(
    defineProps<{
        gmCampaigns?: Campaign[];
        playerCampaigns?: Campaign[];
    }>(),
    {
        gmCampaigns: () => [],
        playerCampaigns: () => [],
    },
);

const page = usePage();
const user = computed(() => page.props.auth?.user);
</script>

<template>
    <div
        class="flex min-h-screen flex-col bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]"
    >
        <Head title="Dashboard" />

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
                    <span
                        class="rounded-xs bg-[#1b1b18]/5 px-2 py-1.5 text-xs font-semibold tracking-tight sm:bg-transparent sm:p-0 sm:text-base dark:bg-[#EDEDEC]/10 sm:dark:bg-transparent"
                    >
                        Dashboard
                    </span>
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
                    <Link
                        :href="settings.edit()"
                        class="rounded-xs px-2 py-1.5 text-xs text-[#706f6c] transition-colors hover:text-[#1b1b18] sm:p-0 sm:text-sm dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                    >
                        Settings
                    </Link>
                </nav>

                <div class="order-2 flex items-center gap-3 sm:gap-4">
                    <Link
                        v-if="user"
                        :href="settings.edit()"
                        class="flex items-center gap-2 transition-opacity hover:opacity-80 sm:gap-2.5"
                    >
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
                    </Link>

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
            class="mx-auto w-full max-w-5xl flex-1 space-y-5 px-4 py-5 sm:space-y-6 sm:p-6"
        >
            <div
                class="flex flex-col justify-between gap-4 rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:flex-row sm:items-center sm:p-8 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <div>
                    <h1 class="text-lg font-semibold sm:text-xl">
                        Welcome<span v-if="user">, {{ user.username }}</span
                        >!
                    </h1>
                    <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Manage your TTRPG campaigns as a DM or player.
                    </p>
                </div>

                <Link
                    :href="campaigns.index()"
                    class="inline-flex w-full items-center justify-center rounded-sm border border-black bg-[#1b1b18] px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-black sm:w-auto sm:py-2 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                >
                    View all campaigns &rarr;
                </Link>
            </div>

            <div class="grid gap-4 sm:gap-6 md:grid-cols-2">
                <!-- DM Campaigns Summary -->
                <section
                    class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="text-base font-semibold">
                            Your Campaigns as DM
                        </h2>
                        <span
                            class="shrink-0 rounded-full border border-[#e3e3e0] px-2.5 py-0.5 text-xs font-medium text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                        >
                            {{ props.gmCampaigns.length }}
                        </span>
                    </div>

                    <p
                        v-if="props.gmCampaigns.length === 0"
                        class="mt-4 text-sm text-[#706f6c] dark:text-[#A1A09A]"
                    >
                        You are not DMing any campaigns yet.
                    </p>

                    <ul
                        v-else
                        class="mt-4 divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]"
                    >
                        <li
                            v-for="campaign in props.gmCampaigns"
                            :key="campaign.id"
                            class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0"
                        >
                            <div class="min-w-0 flex-1">
                                <Link
                                    :href="campaigns.show(campaign.id)"
                                    class="block truncate text-sm font-medium hover:underline"
                                >
                                    {{ campaign.name }}
                                </Link>
                                <p
                                    class="truncate text-xs text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    {{ campaign.system?.name ?? 'System' }}
                                    &middot;
                                    {{ campaign.players_count ?? 0 }} players
                                </p>
                            </div>
                            <span
                                class="shrink-0 rounded-full border border-[#e3e3e0] px-2 py-0.5 text-[11px] text-[#706f6c] capitalize dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                            >
                                {{ campaign.status }}
                            </span>
                        </li>
                    </ul>
                </section>

                <!-- Player Campaigns Summary -->
                <section
                    class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="text-base font-semibold">
                            Joined as Player
                        </h2>
                        <span
                            class="shrink-0 rounded-full border border-[#e3e3e0] px-2.5 py-0.5 text-xs font-medium text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                        >
                            {{ props.playerCampaigns.length }}
                        </span>
                    </div>

                    <p
                        v-if="props.playerCampaigns.length === 0"
                        class="mt-4 text-sm text-[#706f6c] dark:text-[#A1A09A]"
                    >
                        You haven't joined any campaigns as a player yet.
                    </p>

                    <ul
                        v-else
                        class="mt-4 divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]"
                    >
                        <li
                            v-for="campaign in props.playerCampaigns"
                            :key="campaign.id"
                            class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0"
                        >
                            <div class="min-w-0 flex-1">
                                <Link
                                    :href="campaigns.show(campaign.id)"
                                    class="block truncate text-sm font-medium hover:underline"
                                >
                                    {{ campaign.name }}
                                </Link>
                                <p
                                    class="truncate text-xs text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    {{ campaign.system?.name ?? 'System' }}
                                    <span v-if="campaign.gm">
                                        &middot; DM: {{ campaign.gm.username }}
                                    </span>
                                </p>
                            </div>
                            <span
                                class="shrink-0 rounded-full border border-[#e3e3e0] px-2 py-0.5 text-[11px] text-[#706f6c] capitalize dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                            >
                                {{ campaign.status }}
                            </span>
                        </li>
                    </ul>
                </section>
            </div>
        </main>
    </div>
</template>
