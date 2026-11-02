<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AuthController from '@/actions/App/Http/Controllers/AuthController';
import CampaignController from '@/actions/App/Http/Controllers/CampaignController';
import { dashboard } from '@/routes';
import campaigns from '@/routes/campaigns';
import characters from '@/routes/characters';
import settings from '@/routes/settings';
import type { Campaign, System } from '@/types';

const props = withDefaults(
    defineProps<{
        systems?: System[];
        gmCampaigns: Campaign[];
        playerCampaigns: Campaign[];
    }>(),
    {
        systems: () => [],
    },
);

const page = usePage();
const user = computed(() => page.props.auth?.user);

const activeTab = ref<'all' | 'gm' | 'player'>('all');
const activePanel = ref<'none' | 'create' | 'join'>('none');
const copiedCodeId = ref<string | null>(null);

const defaultSystemId = computed(() => {
    const dnd = props.systems.find((sys) => sys.code === 'dnd5e_2024');
    return dnd?.id ?? props.systems[0]?.id ?? '';
});

const showGmSection = computed(
    () => activeTab.value === 'all' || activeTab.value === 'gm',
);
const showPlayerSection = computed(
    () => activeTab.value === 'all' || activeTab.value === 'player',
);

function togglePanel(panel: 'create' | 'join'): void {
    activePanel.value = activePanel.value === panel ? 'none' : panel;
}

async function copyInviteCode(campaign: Campaign): Promise<void> {
    try {
        await navigator.clipboard.writeText(campaign.invite_code);
        copiedCodeId.value = campaign.id;
        setTimeout(() => {
            if (copiedCodeId.value === campaign.id) {
                copiedCodeId.value = null;
            }
        }, 2000);
    } catch {
        // Ignore clipboard errors if blocked
    }
}
</script>

<template>
    <div
        class="flex min-h-screen flex-col bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]"
    >
        <Head title="Campaigns" />

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
                        class="rounded-xs bg-[#1b1b18]/5 px-2 py-1.5 text-xs font-semibold tracking-tight sm:bg-transparent sm:p-0 sm:text-base dark:bg-[#EDEDEC]/10 sm:dark:bg-transparent"
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
            class="mx-auto w-full max-w-5xl flex-1 space-y-6 px-4 py-5 sm:space-y-8 sm:p-6"
        >
            <div
                class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"
            >
                <div>
                    <h1
                        class="text-xl font-semibold tracking-tight sm:text-2xl"
                    >
                        Your Campaigns
                    </h1>
                    <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Create a new campaign as a DM or join a party with an
                        invite code.
                    </p>
                </div>

                <div
                    class="grid w-full grid-cols-2 gap-2 sm:flex sm:w-auto sm:flex-wrap sm:items-center sm:gap-2.5"
                >
                    <button
                        type="button"
                        class="cursor-pointer rounded-sm border border-[#e3e3e0] bg-white px-3.5 py-2.5 text-center text-xs font-medium transition-colors hover:border-[#1b1b18] sm:px-4 sm:py-2 dark:border-[#3E3E3A] dark:bg-[#161615] dark:hover:border-[#EDEDEC]"
                        @click="togglePanel('join')"
                    >
                        Join with Code
                    </button>
                    <button
                        type="button"
                        class="cursor-pointer rounded-sm border border-black bg-[#1b1b18] px-3.5 py-2.5 text-center text-xs font-medium text-white transition-colors hover:bg-black sm:px-4 sm:py-2 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                        @click="togglePanel('create')"
                    >
                        + Create Campaign
                    </button>
                </div>
            </div>

            <!-- Create Campaign Panel -->
            <section
                v-if="activePanel === 'create'"
                class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold">
                            Create a New Campaign (as DM / GM)
                        </h2>
                        <p
                            class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            An invite code will be generated automatically so
                            your players can join.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="shrink-0 cursor-pointer py-1 text-xs text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                        @click="activePanel = 'none'"
                    >
                        Close
                    </button>
                </div>

                <Form
                    v-bind="CampaignController.store.form()"
                    reset-on-success
                    @success="activePanel = 'none'"
                    v-slot="{ errors, processing }"
                    class="mt-5 space-y-4"
                >
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label
                                for="campaign_name"
                                class="block text-sm font-medium"
                            >
                                Campaign name
                            </label>
                            <input
                                id="campaign_name"
                                v-focus
                                name="name"
                                type="text"
                                required
                                placeholder="e.g. Shadows of Phandalin"
                                class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3.5 py-2 text-sm text-[#1b1b18] placeholder-[#706f6c]/60 shadow-2xs transition-colors focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:placeholder-[#A1A09A]/50 dark:focus:border-[#EDEDEC]"
                                :aria-invalid="Boolean(errors.name)"
                            />
                            <p
                                v-if="errors.name"
                                class="mt-1.5 text-xs text-[#f53003] dark:text-[#FF4433]"
                            >
                                {{ errors.name }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="system_id"
                                class="block text-sm font-medium"
                            >
                                Game system
                            </label>
                            <select
                                id="system_id"
                                name="system_id"
                                :value="defaultSystemId"
                                class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3.5 py-2 text-sm text-[#1b1b18] shadow-2xs transition-colors focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC]"
                            >
                                <option
                                    v-for="sys in props.systems"
                                    :key="sys.id"
                                    :value="sys.id"
                                >
                                    {{ sys.name }}
                                </option>
                            </select>
                            <p
                                v-if="errors.system_id"
                                class="mt-1.5 text-xs text-[#f53003] dark:text-[#FF4433]"
                            >
                                {{ errors.system_id }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <label
                            for="campaign_description"
                            class="block text-sm font-medium"
                        >
                            Description
                            <span
                                class="font-normal text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                (optional)
                            </span>
                        </label>
                        <textarea
                            id="campaign_description"
                            name="description"
                            rows="3"
                            placeholder="A brief pitch or setting summary for your players..."
                            class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3.5 py-2 text-sm text-[#1b1b18] placeholder-[#706f6c]/60 shadow-2xs transition-colors focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:placeholder-[#A1A09A]/50 dark:focus:border-[#EDEDEC]"
                        />
                        <p
                            v-if="errors.description"
                            class="mt-1.5 text-xs text-[#f53003] dark:text-[#FF4433]"
                        >
                            {{ errors.description }}
                        </p>
                    </div>

                    <div
                        class="flex flex-col items-stretch gap-2.5 sm:flex-row sm:items-center sm:gap-3"
                    >
                        <button
                            type="submit"
                            :disabled="processing"
                            class="cursor-pointer rounded-sm border border-black bg-[#1b1b18] px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-black disabled:opacity-60 sm:py-2 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                        >
                            <span v-if="processing">Creating...</span>
                            <span v-else>Create campaign</span>
                        </button>
                        <button
                            type="button"
                            class="cursor-pointer rounded-sm border border-[#e3e3e0] px-4 py-2.5 text-sm font-medium transition-colors hover:border-[#1b1b18] sm:py-2 dark:border-[#3E3E3A] dark:hover:border-[#EDEDEC]"
                            @click="activePanel = 'none'"
                        >
                            Cancel
                        </button>
                    </div>
                </Form>
            </section>

            <!-- Join Campaign Panel -->
            <section
                v-if="activePanel === 'join'"
                class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold">
                            Join a Campaign as Player
                        </h2>
                        <p
                            class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Enter the 8-character invite code shared by your DM.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="shrink-0 cursor-pointer py-1 text-xs text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                        @click="activePanel = 'none'"
                    >
                        Close
                    </button>
                </div>

                <Form
                    v-bind="CampaignController.join.form()"
                    reset-on-success
                    @success="activePanel = 'none'"
                    v-slot="{ errors, processing }"
                    class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-start"
                >
                    <div class="flex-1">
                        <label for="invite_code" class="sr-only">
                            Invite code
                        </label>
                        <input
                            id="invite_code"
                            v-focus
                            name="invite_code"
                            type="text"
                            required
                            placeholder="e.g. A1B2C3D4"
                            class="block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3.5 py-2.5 font-mono text-sm tracking-wider text-[#1b1b18] uppercase placeholder-[#706f6c]/60 shadow-2xs transition-colors focus:border-[#1b1b18] focus:outline-none sm:py-2 dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:placeholder-[#A1A09A]/50 dark:focus:border-[#EDEDEC]"
                            :aria-invalid="Boolean(errors.invite_code)"
                        />
                        <p
                            v-if="errors.invite_code"
                            class="mt-1.5 text-xs text-[#f53003] dark:text-[#FF4433]"
                        >
                            {{ errors.invite_code }}
                        </p>
                    </div>

                    <div
                        class="grid grid-cols-2 gap-2 sm:flex sm:items-center sm:gap-2"
                    >
                        <button
                            type="submit"
                            :disabled="processing"
                            class="cursor-pointer rounded-sm border border-black bg-[#1b1b18] px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-black disabled:opacity-60 sm:py-2 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                        >
                            <span v-if="processing">Joining...</span>
                            <span v-else>Join campaign</span>
                        </button>
                        <button
                            type="button"
                            class="cursor-pointer rounded-sm border border-[#e3e3e0] px-4 py-2.5 text-sm font-medium transition-colors hover:border-[#1b1b18] sm:py-2 dark:border-[#3E3E3A] dark:hover:border-[#EDEDEC]"
                            @click="activePanel = 'none'"
                        >
                            Cancel
                        </button>
                    </div>
                </Form>
            </section>

            <!-- Role Filter Tabs -->
            <div class="flex items-center justify-between">
                <div
                    class="grid w-full grid-cols-3 rounded-sm border border-[#e3e3e0] bg-white p-1 sm:inline-flex sm:w-auto dark:border-[#3E3E3A] dark:bg-[#161615]"
                >
                    <button
                        type="button"
                        class="cursor-pointer rounded-xs px-2.5 py-1.5 text-center text-xs font-medium transition-colors sm:px-3"
                        :class="
                            activeTab === 'all'
                                ? 'bg-[#1b1b18] text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                : 'text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]'
                        "
                        @click="activeTab = 'all'"
                    >
                        All ({{
                            props.gmCampaigns.length +
                            props.playerCampaigns.length
                        }})
                    </button>
                    <button
                        type="button"
                        class="cursor-pointer rounded-xs px-2.5 py-1.5 text-center text-xs font-medium transition-colors sm:px-3"
                        :class="
                            activeTab === 'gm'
                                ? 'bg-[#1b1b18] text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                : 'text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]'
                        "
                        @click="activeTab = 'gm'"
                    >
                        DM / GM ({{ props.gmCampaigns.length }})
                    </button>
                    <button
                        type="button"
                        class="cursor-pointer rounded-xs px-2.5 py-1.5 text-center text-xs font-medium transition-colors sm:px-3"
                        :class="
                            activeTab === 'player'
                                ? 'bg-[#1b1b18] text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                : 'text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]'
                        "
                        @click="activeTab = 'player'"
                    >
                        Player ({{ props.playerCampaigns.length }})
                    </button>
                </div>
            </div>

            <!-- Campaigns as DM / GM -->
            <section v-if="showGmSection" class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-base font-semibold sm:text-lg">
                            Campaigns as DM / GM
                        </h2>
                        <span
                            class="rounded-full border border-[#e3e3e0] bg-white px-2.5 py-0.5 text-xs font-medium text-[#706f6c] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#A1A09A]"
                        >
                            {{ props.gmCampaigns.length }}
                        </span>
                    </div>
                </div>

                <div
                    v-if="props.gmCampaigns.length === 0"
                    class="rounded-lg bg-white p-6 text-center shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-8 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <p class="text-sm font-medium">
                        You aren't running any campaigns as a DM yet.
                    </p>
                    <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                        Click "+ Create Campaign" above to launch your first
                        adventure.
                    </p>
                </div>

                <div v-else class="grid gap-4 sm:grid-cols-2">
                    <article
                        v-for="campaign in props.gmCampaigns"
                        :key="campaign.id"
                        class="flex flex-col justify-between rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                    >
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div
                                        class="flex flex-wrap items-center gap-1.5 sm:gap-2"
                                    >
                                        <span
                                            class="inline-flex items-center rounded-xs bg-[#1b1b18] px-2 py-0.5 text-[11px] font-semibold tracking-wide text-white uppercase dark:bg-[#EDEDEC] dark:text-[#1b1b18]"
                                        >
                                            DM
                                        </span>
                                        <span
                                            v-if="campaign.system"
                                            class="inline-flex items-center rounded-xs border border-[#e3e3e0] px-2 py-0.5 text-[11px] font-medium text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                                        >
                                            {{ campaign.system.name }}
                                        </span>
                                    </div>
                                    <Link
                                        :href="campaigns.show(campaign.id)"
                                        class="mt-2 block text-base font-semibold hover:underline"
                                    >
                                        {{ campaign.name }}
                                    </Link>
                                </div>

                                <span
                                    class="shrink-0 rounded-full border border-[#e3e3e0] px-2.5 py-0.5 text-[11px] font-medium text-[#706f6c] capitalize dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                                >
                                    {{ campaign.status }}
                                </span>
                            </div>

                            <p
                                v-if="campaign.description"
                                class="mt-2 line-clamp-2 text-sm text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                {{ campaign.description }}
                            </p>
                            <p
                                v-else
                                class="mt-2 text-sm text-[#706f6c]/60 italic dark:text-[#A1A09A]/60"
                            >
                                No description provided.
                            </p>
                        </div>

                        <div
                            class="mt-5 flex flex-wrap items-center justify-between gap-2.5 border-t border-[#e3e3e0] pt-4 text-xs text-[#706f6c] sm:mt-6 dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                        >
                            <div class="flex items-center gap-3 sm:gap-4">
                                <span>
                                    {{ campaign.players_count ?? 0 }}
                                    {{
                                        (campaign.players_count ?? 0) === 1
                                            ? 'player'
                                            : 'players'
                                    }}
                                </span>
                                <span>
                                    {{ campaign.characters_count ?? 0 }}
                                    {{
                                        (campaign.characters_count ?? 0) === 1
                                            ? 'character'
                                            : 'characters'
                                    }}
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    class="cursor-pointer rounded-xs border border-[#e3e3e0] px-2 py-1 font-mono text-[11px] transition-colors hover:border-[#1b1b18] hover:text-[#1b1b18] sm:py-0.5 dark:border-[#3E3E3A] dark:hover:border-[#EDEDEC] dark:hover:text-[#EDEDEC]"
                                    @click="copyInviteCode(campaign)"
                                >
                                    <span v-if="copiedCodeId === campaign.id">
                                        Copied!
                                    </span>
                                    <span v-else>
                                        Code: {{ campaign.invite_code }}
                                    </span>
                                </button>
                                <Link
                                    :href="campaigns.show(campaign.id)"
                                    class="rounded-xs bg-[#1b1b18] px-2.5 py-1 text-[11px] font-medium text-white hover:opacity-90 sm:py-0.5 dark:bg-[#EDEDEC] dark:text-[#1b1b18]"
                                >
                                    Open &rarr;
                                </Link>
                            </div>
                        </div>
                    </article>
                </div>
            </section>

            <!-- Campaigns as Player -->
            <section v-if="showPlayerSection" class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-base font-semibold sm:text-lg">
                            Campaigns as Player
                        </h2>
                        <span
                            class="rounded-full border border-[#e3e3e0] bg-white px-2.5 py-0.5 text-xs font-medium text-[#706f6c] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#A1A09A]"
                        >
                            {{ props.playerCampaigns.length }}
                        </span>
                    </div>
                </div>

                <div
                    v-if="props.playerCampaigns.length === 0"
                    class="rounded-lg bg-white p-6 text-center shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-8 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <p class="text-sm font-medium">
                        You haven't joined any campaigns as a player yet.
                    </p>
                    <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                        Click "Join with Code" above to enter an invite code
                        from your DM.
                    </p>
                </div>

                <div v-else class="grid gap-4 sm:grid-cols-2">
                    <article
                        v-for="campaign in props.playerCampaigns"
                        :key="campaign.id"
                        class="flex flex-col justify-between rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                    >
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div
                                        class="flex flex-wrap items-center gap-1.5 sm:gap-2"
                                    >
                                        <span
                                            class="inline-flex items-center rounded-xs border border-[#f53003]/40 bg-[#f53003]/10 px-2 py-0.5 text-[11px] font-semibold tracking-wide text-[#f53003] uppercase dark:border-[#FF4433]/40 dark:bg-[#FF4433]/10 dark:text-[#FF4433]"
                                        >
                                            Player
                                        </span>
                                        <span
                                            v-if="campaign.system"
                                            class="inline-flex items-center rounded-xs border border-[#e3e3e0] px-2 py-0.5 text-[11px] font-medium text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                                        >
                                            {{ campaign.system.name }}
                                        </span>
                                    </div>
                                    <Link
                                        :href="campaigns.show(campaign.id)"
                                        class="mt-2 block text-base font-semibold hover:underline"
                                    >
                                        {{ campaign.name }}
                                    </Link>
                                </div>

                                <span
                                    class="shrink-0 rounded-full border border-[#e3e3e0] px-2.5 py-0.5 text-[11px] font-medium text-[#706f6c] capitalize dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                                >
                                    {{ campaign.status }}
                                </span>
                            </div>

                            <p
                                v-if="campaign.description"
                                class="mt-2 line-clamp-2 text-sm text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                {{ campaign.description }}
                            </p>
                            <p
                                v-else
                                class="mt-2 text-sm text-[#706f6c]/60 italic dark:text-[#A1A09A]/60"
                            >
                                No description provided.
                            </p>
                        </div>

                        <div
                            class="mt-5 flex flex-wrap items-center justify-between gap-2.5 border-t border-[#e3e3e0] pt-4 text-xs text-[#706f6c] sm:mt-6 dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                        >
                            <span v-if="campaign.gm" class="truncate">
                                DM:
                                <strong
                                    class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                                >
                                    {{ campaign.gm.username }}
                                </strong>
                            </span>
                            <span v-else>DM: Unknown</span>

                            <div class="flex items-center gap-3">
                                <span>
                                    {{ campaign.players_count ?? 0 }}
                                    {{
                                        (campaign.players_count ?? 0) === 1
                                            ? 'player'
                                            : 'players'
                                    }}
                                </span>
                                <Link
                                    :href="campaigns.show(campaign.id)"
                                    class="rounded-xs bg-[#1b1b18] px-2.5 py-1 text-[11px] font-medium text-white hover:opacity-90 sm:py-0.5 dark:bg-[#EDEDEC] dark:text-[#1b1b18]"
                                >
                                    Open &rarr;
                                </Link>
                            </div>
                        </div>
                    </article>
                </div>
            </section>
        </main>
    </div>
</template>
