<script setup lang="ts">
import { Form, Head, Link, router, usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { computed, ref, watch } from 'vue';
import AuthController from '@/actions/App/Http/Controllers/AuthController';
import CampaignController from '@/actions/App/Http/Controllers/CampaignController';
import CharacterController from '@/actions/App/Http/Controllers/CharacterController';
import { dashboard } from '@/routes';
import campaigns from '@/routes/campaigns';
import characters from '@/routes/characters';
import settings from '@/routes/settings';
import type { AbilityKey, Campaign, Character, System } from '@/types';

const props = withDefaults(
    defineProps<{
        campaign: Campaign;
        isGm: boolean;
        systems?: System[];
        assignableCharacters?: Pick<
            Character,
            'id' | 'name' | 'campaign_id' | 'sheet_data'
        >[];
    }>(),
    {
        systems: () => [],
        assignableCharacters: () => [],
    },
);

const page = usePage();
const user = computed(() => page.props.auth?.user);

const partyCharacters = ref<Character[]>([
    ...(props.campaign.characters ?? []),
]);

watch(
    () => props.campaign.characters,
    (updated) => {
        partyCharacters.value = [...(updated ?? [])];
    },
    { deep: true },
);

// Real-time updates when a character in this campaign is updated
useEcho<{ character: Character }>(
    `campaign.${props.campaign.id}`,
    'CharacterUpdated',
    (payload) => {
        if (!payload?.character) {
            return;
        }
        const index = partyCharacters.value.findIndex(
            (c) => c.id === payload.character.id,
        );
        if (index >= 0) {
            partyCharacters.value[index] = {
                ...partyCharacters.value[index],
                ...payload.character,
            };
        }
    },
);

const activeActionPanel = ref<'none' | 'assign' | 'create' | 'settings'>(
    'none',
);
const confirmingDeleteCampaign = ref(false);
const copiedInviteCode = ref(false);

const abilityKeys: { key: AbilityKey; short: string }[] = [
    { key: 'str', short: 'STR' },
    { key: 'dex', short: 'DEX' },
    { key: 'con', short: 'CON' },
    { key: 'int', short: 'INT' },
    { key: 'wis', short: 'WIS' },
    { key: 'cha', short: 'CHA' },
];

const speciesOptions = [
    'Human',
    'Aasimar',
    'Dragonborn',
    'Dwarf',
    'Elf',
    'Gnome',
    'Goliath',
    'Halfling',
    'Orc',
    'Tiefling',
];

const classOptions = [
    'Barbarian',
    'Bard',
    'Cleric',
    'Druid',
    'Fighter',
    'Monk',
    'Paladin',
    'Ranger',
    'Rogue',
    'Sorcerer',
    'Warlock',
    'Wizard',
];

const backgroundOptions = [
    'Acolyte',
    'Artisan',
    'Charlatan',
    'Criminal',
    'Entertainer',
    'Farmer',
    'Guard',
    'Guide',
    'Hermit',
    'Merchant',
    'Noble',
    'Sage',
    'Sailor',
    'Scribe',
    'Soldier',
    'Wayfarer',
];

function toggleActionPanel(panel: 'assign' | 'create' | 'settings'): void {
    activeActionPanel.value =
        activeActionPanel.value === panel ? 'none' : panel;
    if (activeActionPanel.value !== 'settings') {
        confirmingDeleteCampaign.value = false;
    }
}

async function copyInviteCode(): Promise<void> {
    try {
        await navigator.clipboard.writeText(props.campaign.invite_code);
        copiedInviteCode.value = true;
        setTimeout(() => {
            copiedInviteCode.value = false;
        }, 2000);
    } catch {
        // Ignore clipboard errors
    }
}

function levelOf(char: Character): number {
    return Math.max(1, Math.min(20, Number(char.sheet_data?.level ?? 1)));
}

function profBonusOf(char: Character): number {
    return Math.ceil(levelOf(char) / 4) + 1;
}

function exhaustionOf(char: Character): number {
    return Math.max(0, Math.min(6, Number(char.sheet_data?.exhaustion ?? 0)));
}

function exhaustionPenaltyOf(char: Character): number {
    return exhaustionOf(char) * 2;
}

function abilityScoreOf(char: Character, key: AbilityKey): number {
    return Number(char.sheet_data?.abilities?.[key] ?? 10);
}

function abilityModOf(char: Character, key: AbilityKey): number {
    return Math.floor((abilityScoreOf(char, key) - 10) / 2);
}

function formatSigned(val: number): string {
    return val >= 0 ? `+${val}` : `${val}`;
}

function passivePerceptionOf(char: Character): number {
    const wisMod = abilityModOf(char, 'wis');
    const profLevel = char.sheet_data?.skills?.perception ?? 'none';
    const mult =
        profLevel === 'expertise' ? 2 : profLevel === 'proficient' ? 1 : 0;
    return 10 + wisMod + profBonusOf(char) * mult - exhaustionPenaltyOf(char);
}

function initiativeOf(char: Character): number {
    return abilityModOf(char, 'dex') - exhaustionPenaltyOf(char);
}

function effectiveSpeedOf(char: Character): number {
    const baseSpeed = Number(char.sheet_data?.speed ?? 30);
    return Math.max(0, baseSpeed - exhaustionOf(char) * 5);
}

function spellSaveDcOf(char: Character): number | null {
    const ability = char.sheet_data?.spellcasting_ability;
    if (!ability || ability === 'none') {
        return null;
    }
    return (
        8 +
        profBonusOf(char) +
        abilityModOf(char, ability) -
        exhaustionPenaltyOf(char)
    );
}

function hpPercentageOf(char: Character): number {
    const current = Math.max(0, Number(char.sheet_data?.hp?.current ?? 0));
    const max = Math.max(1, Number(char.sheet_data?.hp?.max ?? 1));
    return Math.min(100, Math.round((current / max) * 100));
}

const highestPassivePerception = computed(() => {
    if (partyCharacters.value.length === 0) {
        return null;
    }
    return Math.max(...partyCharacters.value.map(passivePerceptionOf));
});

const averagePartyLevel = computed(() => {
    if (partyCharacters.value.length === 0) {
        return null;
    }
    const sum = partyCharacters.value.reduce((acc, c) => acc + levelOf(c), 0);
    return Math.round((sum / partyCharacters.value.length) * 10) / 10;
});

const inspiredCount = computed(
    () =>
        partyCharacters.value.filter((c) =>
            Boolean(c.sheet_data?.heroic_inspiration),
        ).length,
);
</script>

<template>
    <div
        class="flex min-h-screen flex-col bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]"
    >
        <Head :title="`${props.campaign.name} - Campaign Dashboard`" />

        <header
            class="border-b border-[#e3e3e0] bg-white px-4 py-3 sm:px-6 sm:py-4 dark:border-[#3E3E3A] dark:bg-[#161615]"
        >
            <div
                class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-x-4 gap-y-2.5"
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
            class="mx-auto w-full max-w-6xl flex-1 space-y-5 px-4 py-5 sm:space-y-6 sm:p-6"
        >
            <!-- Campaign Header Card -->
            <section
                class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <div
                    class="flex flex-col justify-between gap-4 md:flex-row md:items-start"
                >
                    <div class="min-w-0 flex-1">
                        <div
                            class="flex flex-wrap items-center gap-1.5 sm:gap-2"
                        >
                            <Link
                                :href="campaigns.index()"
                                class="text-xs font-medium tracking-wider text-[#706f6c] uppercase transition-colors hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                            >
                                &larr; Campaigns
                            </Link>
                            <span class="text-[#e3e3e0] dark:text-[#3E3E3A]">
                                /
                            </span>
                            <span
                                v-if="props.isGm"
                                class="inline-flex items-center rounded-xs bg-[#1b1b18] px-2 py-0.5 text-[11px] font-semibold tracking-wide text-white uppercase dark:bg-[#EDEDEC] dark:text-[#1b1b18]"
                            >
                                GM View
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center rounded-xs border border-[#f53003]/40 bg-[#f53003]/10 px-2 py-0.5 text-[11px] font-semibold tracking-wide text-[#f53003] uppercase dark:border-[#FF4433]/40 dark:bg-[#FF4433]/10 dark:text-[#FF4433]"
                            >
                                Player View
                            </span>
                            <span
                                v-if="props.campaign.system"
                                class="inline-flex items-center rounded-xs border border-[#e3e3e0] px-2 py-0.5 text-[11px] font-medium text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                            >
                                {{ props.campaign.system.name }}
                            </span>
                            <span
                                class="rounded-full border border-[#e3e3e0] px-2.5 py-0.5 text-[11px] font-medium text-[#706f6c] capitalize dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                            >
                                {{ props.campaign.status }}
                            </span>
                        </div>

                        <h1
                            class="mt-2 text-xl font-semibold tracking-tight sm:text-2xl"
                        >
                            {{ props.campaign.name }}
                        </h1>
                        <p
                            v-if="props.campaign.description"
                            class="mt-1 max-w-2xl text-sm text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            {{ props.campaign.description }}
                        </p>
                    </div>

                    <div
                        class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center sm:gap-2.5"
                    >
                        <button
                            type="button"
                            class="cursor-pointer rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-center font-mono text-xs font-medium transition-colors hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:hover:border-[#EDEDEC]"
                            @click="copyInviteCode"
                        >
                            <span v-if="copiedInviteCode">
                                Invite Code Copied!
                            </span>
                            <span v-else>
                                Invite Code: {{ props.campaign.invite_code }}
                            </span>
                        </button>

                        <button
                            v-if="props.assignableCharacters.length > 0"
                            type="button"
                            class="cursor-pointer rounded-sm border border-[#e3e3e0] bg-white px-3.5 py-2 text-center text-xs font-medium transition-colors hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#161615] dark:hover:border-[#EDEDEC]"
                            @click="toggleActionPanel('assign')"
                        >
                            Assign Existing Character
                        </button>

                        <button
                            v-if="props.isGm"
                            type="button"
                            class="cursor-pointer rounded-sm border px-3.5 py-2 text-center text-xs font-medium transition-colors"
                            :class="
                                activeActionPanel === 'settings'
                                    ? 'border-[#1b1b18] bg-[#1b1b18] text-white dark:border-[#EDEDEC] dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                    : 'border-[#e3e3e0] bg-white hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#161615] dark:hover:border-[#EDEDEC]'
                            "
                            @click="toggleActionPanel('settings')"
                        >
                            Campaign Settings
                        </button>

                        <button
                            type="button"
                            class="cursor-pointer rounded-sm border border-black bg-[#1b1b18] px-3.5 py-2.5 text-center text-xs font-medium text-white transition-colors hover:bg-black sm:py-2 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                            @click="toggleActionPanel('create')"
                        >
                            + New Character in Campaign
                        </button>
                    </div>
                </div>

                <!-- Campaign Roster Strip -->
                <div
                    class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1.5 border-t border-[#e3e3e0] pt-3.5 text-xs text-[#706f6c] sm:mt-5 sm:pt-4 dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                >
                    <span v-if="props.campaign.gm">
                        DM:
                        <strong
                            class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                        >
                            {{ props.campaign.gm.username }}
                        </strong>
                    </span>
                    <span class="hidden sm:inline">&middot;</span>
                    <span>
                        Players ({{ props.campaign.players?.length ?? 0 }}):
                        <strong
                            v-if="
                                props.campaign.players &&
                                props.campaign.players.length > 0
                            "
                            class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                        >
                            {{
                                props.campaign.players
                                    .map((p) => p.username)
                                    .join(', ')
                            }}
                        </strong>
                        <span v-else class="italic">No players joined yet</span>
                    </span>
                </div>
            </section>

            <!-- Assign Existing Character Panel -->
            <section
                v-if="activeActionPanel === 'assign'"
                class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-base font-semibold">
                        Assign an Existing Character to
                        {{ props.campaign.name }}
                    </h2>
                    <button
                        type="button"
                        class="shrink-0 cursor-pointer py-1 text-xs text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                        @click="activeActionPanel = 'none'"
                    >
                        Close
                    </button>
                </div>

                <Form
                    v-bind="
                        CampaignController.assignCharacter.form(
                            props.campaign.id,
                        )
                    "
                    @success="activeActionPanel = 'none'"
                    v-slot="{ errors, processing }"
                    class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center"
                >
                    <select
                        name="character_id"
                        required
                        class="block w-full flex-1 rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3.5 py-2.5 text-sm text-[#1b1b18] sm:py-2 dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]"
                    >
                        <option
                            v-for="c in props.assignableCharacters"
                            :key="c.id"
                            :value="c.id"
                        >
                            {{ c.name }} (Lvl
                            {{ c.sheet_data?.level ?? 1 }}
                            {{ c.sheet_data?.species ?? 'Human' }}
                            {{ c.sheet_data?.class ?? 'Adventurer' }})
                        </option>
                    </select>
                    <p
                        v-if="errors.character_id"
                        class="text-xs text-[#f53003] dark:text-[#FF4433]"
                    >
                        {{ errors.character_id }}
                    </p>
                    <button
                        type="submit"
                        :disabled="processing"
                        class="w-full cursor-pointer rounded-sm border border-black bg-[#1b1b18] px-5 py-2.5 text-xs font-medium text-white hover:bg-black disabled:opacity-60 sm:w-auto sm:py-2 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A]"
                    >
                        Assign to Campaign
                    </button>
                </Form>
            </section>

            <!-- Create Character Directly in Campaign Panel -->
            <section
                v-if="activeActionPanel === 'create'"
                class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-base font-semibold">
                        Create Character in {{ props.campaign.name }}
                    </h2>
                    <button
                        type="button"
                        class="shrink-0 cursor-pointer py-1 text-xs text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                        @click="activeActionPanel = 'none'"
                    >
                        Close
                    </button>
                </div>

                <Form
                    v-bind="CharacterController.store.form()"
                    v-slot="{ errors, processing }"
                    class="mt-4 space-y-4"
                >
                    <input
                        type="hidden"
                        name="campaign_id"
                        :value="props.campaign.id"
                    />
                    <input
                        type="hidden"
                        name="system_id"
                        :value="props.campaign.system_id"
                    />

                    <div class="grid gap-3.5 sm:grid-cols-2 lg:grid-cols-5">
                        <div class="sm:col-span-2 lg:col-span-2">
                            <label class="block text-xs font-medium">
                                Character name
                            </label>
                            <input
                                v-focus
                                name="name"
                                type="text"
                                required
                                placeholder="e.g. Kaelen Dawnbringer"
                                class="mt-1 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                            />
                            <p
                                v-if="errors.name"
                                class="mt-1 text-xs text-[#f53003] dark:text-[#FF4433]"
                            >
                                {{ errors.name }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium">
                                Species (2024)
                            </label>
                            <select
                                name="species"
                                class="mt-1 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                            >
                                <option
                                    v-for="sp in speciesOptions"
                                    :key="sp"
                                    :value="sp"
                                >
                                    {{ sp }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium">
                                Class
                            </label>
                            <select
                                name="class"
                                class="mt-1 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                            >
                                <option
                                    v-for="cls in classOptions"
                                    :key="cls"
                                    :value="cls"
                                >
                                    {{ cls }}
                                </option>
                            </select>
                        </div>

                        <div class="sm:col-span-2 lg:col-span-1">
                            <label class="block text-xs font-medium">
                                Background
                            </label>
                            <select
                                name="background"
                                class="mt-1 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                            >
                                <option
                                    v-for="bg in backgroundOptions"
                                    :key="bg"
                                    :value="bg"
                                >
                                    {{ bg }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            type="submit"
                            :disabled="processing"
                            class="w-full cursor-pointer rounded-sm border border-black bg-[#1b1b18] px-5 py-2.5 text-xs font-medium text-white hover:bg-black disabled:opacity-60 sm:w-auto sm:py-2 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A]"
                        >
                            Create &amp; open sheet
                        </button>
                    </div>
                </Form>
            </section>

            <!-- Campaign Settings Panel (GM Only) -->
            <section
                v-if="props.isGm && activeActionPanel === 'settings'"
                class="space-y-6 rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold">
                            Campaign Settings
                        </h2>
                        <p
                            class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Update campaign details, status, invite code, or
                            permanently delete this campaign.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="shrink-0 cursor-pointer py-1 text-xs text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                        @click="toggleActionPanel('settings')"
                    >
                        Close
                    </button>
                </div>

                <Form
                    v-bind="CampaignController.update.form(props.campaign.id)"
                    v-slot="{ errors, processing, recentlySuccessful }"
                    class="space-y-4"
                >
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="sm:col-span-1">
                            <label
                                for="edit_campaign_name"
                                class="block text-xs font-medium"
                            >
                                Campaign name
                            </label>
                            <input
                                id="edit_campaign_name"
                                name="name"
                                type="text"
                                required
                                :value="props.campaign.name"
                                class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm text-[#1b1b18] focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC]"
                                :aria-invalid="Boolean(errors.name)"
                            />
                            <p
                                v-if="errors.name"
                                class="mt-1 text-xs text-[#f53003] dark:text-[#FF4433]"
                            >
                                {{ errors.name }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="edit_campaign_status"
                                class="block text-xs font-medium"
                            >
                                Status
                            </label>
                            <select
                                id="edit_campaign_status"
                                name="status"
                                :value="props.campaign.status"
                                class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm text-[#1b1b18] focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC]"
                            >
                                <option value="active">Active</option>
                                <option value="paused">Paused</option>
                                <option value="completed">Completed</option>
                                <option value="archived">Archived</option>
                            </select>
                            <p
                                v-if="errors.status"
                                class="mt-1 text-xs text-[#f53003] dark:text-[#FF4433]"
                            >
                                {{ errors.status }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="edit_campaign_system"
                                class="block text-xs font-medium"
                            >
                                Game system
                            </label>
                            <select
                                id="edit_campaign_system"
                                name="system_id"
                                :value="props.campaign.system_id"
                                class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm text-[#1b1b18] focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC]"
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
                                class="mt-1 text-xs text-[#f53003] dark:text-[#FF4433]"
                            >
                                {{ errors.system_id }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <label
                            for="edit_campaign_description"
                            class="block text-xs font-medium"
                        >
                            Description
                            <span
                                class="font-normal text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                (optional)
                            </span>
                        </label>
                        <textarea
                            id="edit_campaign_description"
                            name="description"
                            rows="3"
                            :value="props.campaign.description ?? ''"
                            placeholder="A brief pitch or setting summary for your players..."
                            class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm text-[#1b1b18] focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC]"
                        />
                        <p
                            v-if="errors.description"
                            class="mt-1 text-xs text-[#f53003] dark:text-[#FF4433]"
                        >
                            {{ errors.description }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="regenerate_invite_code"
                            class="inline-flex cursor-pointer items-center gap-2 text-xs text-[#706f6c] select-none dark:text-[#A1A09A]"
                        >
                            <input
                                id="regenerate_invite_code"
                                name="regenerate_invite_code"
                                type="checkbox"
                                value="1"
                                class="h-4 w-4 rounded-xs border-[#e3e3e0] text-[#1b1b18] focus:ring-0 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                            />
                            <span>
                                Generate a new invite code (invalidates
                                <code class="font-mono font-semibold">{{
                                    props.campaign.invite_code
                                }}</code
                                >)
                            </span>
                        </label>
                    </div>

                    <div
                        class="flex flex-col items-stretch gap-2.5 sm:flex-row sm:items-center sm:gap-3"
                    >
                        <button
                            type="submit"
                            :disabled="processing"
                            class="cursor-pointer rounded-sm border border-black bg-[#1b1b18] px-5 py-2.5 text-xs font-medium text-white transition-colors hover:bg-black disabled:opacity-60 sm:py-2 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                        >
                            <span v-if="processing">Saving...</span>
                            <span v-else>Save campaign details</span>
                        </button>

                        <span
                            v-if="recentlySuccessful"
                            class="text-xs font-medium text-emerald-600 dark:text-emerald-400"
                        >
                            Campaign updated!
                        </span>
                    </div>
                </Form>

                <!-- Danger Zone: Delete Campaign -->
                <div
                    class="flex flex-col items-start justify-between gap-3 border-t border-[#e3e3e0] pt-5 sm:flex-row sm:items-center dark:border-[#3E3E3A]"
                >
                    <div>
                        <h3
                            class="text-sm font-semibold text-[#f53003] dark:text-[#FF4433]"
                        >
                            Delete Campaign
                        </h3>
                        <p
                            class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Permanently delete this campaign. Party characters
                            will not be deleted and will become standalone
                            sheets.
                        </p>
                    </div>

                    <button
                        v-if="!confirmingDeleteCampaign"
                        type="button"
                        class="w-full shrink-0 cursor-pointer rounded-sm border border-[#f53003] px-4 py-2 text-center text-xs font-medium text-[#f53003] transition-colors hover:bg-[#f53003] hover:text-white sm:w-auto dark:border-[#FF4433] dark:text-[#FF4433] dark:hover:bg-[#FF4433] dark:hover:text-white"
                        @click="confirmingDeleteCampaign = true"
                    >
                        Delete campaign
                    </button>

                    <div
                        v-else
                        class="flex w-full shrink-0 items-center gap-2 sm:w-auto"
                    >
                        <Form
                            v-bind="
                                CampaignController.destroy.form(
                                    props.campaign.id,
                                )
                            "
                            v-slot="{ processing }"
                            class="flex-1 sm:flex-initial"
                        >
                            <button
                                type="submit"
                                :disabled="processing"
                                class="w-full cursor-pointer rounded-sm bg-[#f53003] px-4 py-2 text-xs font-medium text-white transition-opacity hover:opacity-90 disabled:opacity-50 sm:w-auto dark:bg-[#FF4433]"
                            >
                                <span v-if="processing">Deleting...</span>
                                <span v-else>Confirm delete campaign</span>
                            </button>
                        </Form>
                        <button
                            type="button"
                            class="cursor-pointer rounded-sm border border-[#e3e3e0] px-3 py-2 text-xs font-medium transition-colors hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#EDEDEC]"
                            @click="confirmingDeleteCampaign = false"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </section>

            <!-- GM Party Overview Metrics -->
            <section class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div
                    class="rounded-lg bg-white p-3.5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-4 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <span
                        class="block text-[10px] font-medium tracking-wider text-[#706f6c] uppercase sm:text-[11px] dark:text-[#A1A09A]"
                    >
                        Party Characters
                    </span>
                    <span class="mt-1 block text-xl font-bold sm:text-2xl">
                        {{ partyCharacters.length }}
                    </span>
                </div>

                <div
                    class="rounded-lg bg-white p-3.5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-4 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <span
                        class="block text-[10px] font-medium tracking-wider text-[#706f6c] uppercase sm:text-[11px] dark:text-[#A1A09A]"
                    >
                        Average Level
                    </span>
                    <span class="mt-1 block text-xl font-bold sm:text-2xl">
                        {{ averagePartyLevel ?? '—' }}
                    </span>
                </div>

                <div
                    class="rounded-lg bg-white p-3.5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-4 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <span
                        class="block text-[10px] font-medium tracking-wider text-[#706f6c] uppercase sm:text-[11px] dark:text-[#A1A09A]"
                    >
                        Max Passive Perc.
                    </span>
                    <span class="mt-1 block text-xl font-bold sm:text-2xl">
                        {{ highestPassivePerception ?? '—' }}
                    </span>
                </div>

                <div
                    class="rounded-lg bg-white p-3.5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-4 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <span
                        class="block text-[10px] font-medium tracking-wider text-[#706f6c] uppercase sm:text-[11px] dark:text-[#A1A09A]"
                    >
                        Heroic Inspiration
                    </span>
                    <span class="mt-1 block text-xl font-bold sm:text-2xl">
                        {{ inspiredCount }} / {{ partyCharacters.length }}
                    </span>
                </div>
            </section>

            <!-- Party Character Tracker Grid -->
            <section class="space-y-4">
                <div
                    class="flex flex-col justify-between gap-1 sm:flex-row sm:items-center"
                >
                    <h2 class="text-base font-semibold sm:text-lg">
                        Party Character Tracker
                    </h2>
                </div>

                <div
                    v-if="partyCharacters.length === 0"
                    class="rounded-lg bg-white p-6 text-center shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-10 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <p class="text-sm font-medium">
                        No characters are assigned to this campaign yet.
                    </p>
                    <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                        Create a new character in this campaign or assign an
                        existing character above.
                    </p>
                </div>

                <div v-else class="grid gap-4 sm:gap-5 lg:grid-cols-2">
                    <article
                        v-for="char in partyCharacters"
                        :key="char.id"
                        class="flex flex-col justify-between rounded-lg bg-white p-4 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                    >
                        <div class="space-y-3.5 sm:space-y-4">
                            <!-- Card Header: Avatar, Name, Player, Full Sheet Link -->
                            <div
                                class="flex flex-wrap items-start justify-between gap-3"
                            >
                                <div
                                    class="flex min-w-0 flex-1 items-center gap-3"
                                >
                                    <img
                                        v-if="char.avatar_url"
                                        :src="char.avatar_url"
                                        :alt="char.name"
                                        class="h-11 w-11 shrink-0 rounded-full border border-[#e3e3e0] object-cover sm:h-12 sm:w-12 dark:border-[#3E3E3A]"
                                    />
                                    <div
                                        v-else
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] text-base font-semibold uppercase sm:h-12 sm:w-12 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                    >
                                        {{ char.name.charAt(0) }}
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div
                                            class="flex flex-wrap items-center gap-1.5 sm:gap-2"
                                        >
                                            <h3
                                                class="truncate text-base font-semibold"
                                            >
                                                {{ char.name }}
                                            </h3>
                                            <span
                                                v-if="char.user"
                                                class="rounded-xs border border-[#e3e3e0] px-1.5 py-0.5 text-[10px] font-medium text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                                            >
                                                @{{ char.user.username }}
                                            </span>
                                        </div>
                                        <p
                                            class="truncate text-xs text-[#706f6c] dark:text-[#A1A09A]"
                                        >
                                            Lvl {{ levelOf(char) }}
                                            {{
                                                char.sheet_data?.species ??
                                                'Human'
                                            }}
                                            {{
                                                char.sheet_data?.class ??
                                                'Adventurer'
                                            }}
                                            <span
                                                v-if="char.sheet_data?.subclass"
                                            >
                                                ({{ char.sheet_data.subclass }})
                                            </span>
                                            &middot;
                                            {{
                                                char.sheet_data?.background ??
                                                'Soldier'
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <Link
                                    :href="characters.show(char.id)"
                                    class="shrink-0 rounded-sm border border-[#e3e3e0] px-2.5 py-1.5 text-xs font-medium transition-colors hover:border-[#1b1b18] sm:px-3 dark:border-[#3E3E3A] dark:hover:border-[#EDEDEC]"
                                >
                                    Open Sheet &rarr;
                                </Link>
                            </div>

                            <!-- HP Bar (Read-Only) -->
                            <div
                                class="rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] p-3 sm:p-3.5 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                            >
                                <div
                                    class="flex flex-wrap items-center justify-between gap-2"
                                >
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                        >
                                            HP
                                        </span>
                                        <span class="text-sm font-bold">
                                            {{
                                                char.sheet_data?.hp?.current ??
                                                10
                                            }}
                                            /
                                            {{ char.sheet_data?.hp?.max ?? 10 }}
                                        </span>
                                        <span
                                            v-if="
                                                (char.sheet_data?.hp?.temp ??
                                                    0) > 0
                                            "
                                            class="rounded-xs bg-sky-500/15 px-1.5 py-0.5 text-[11px] font-semibold text-sky-600 dark:text-sky-400"
                                        >
                                            +{{ char.sheet_data?.hp?.temp }}
                                            Temp
                                        </span>
                                    </div>

                                    <span
                                        class="font-mono text-xs font-semibold text-[#706f6c] dark:text-[#A1A09A]"
                                    >
                                        {{ hpPercentageOf(char) }}%
                                    </span>
                                </div>

                                <!-- Visual HP Progress Bar -->
                                <div
                                    class="mt-2 h-2 w-full overflow-hidden rounded-full bg-[#e3e3e0] dark:bg-[#3E3E3A]"
                                >
                                    <div
                                        class="h-full transition-all duration-300"
                                        :class="
                                            hpPercentageOf(char) <= 25
                                                ? 'bg-[#f53003] dark:bg-[#FF4433]'
                                                : 'bg-[#1b1b18] dark:bg-[#EDEDEC]'
                                        "
                                        :style="{
                                            width: `${hpPercentageOf(char)}%`,
                                        }"
                                    />
                                </div>
                            </div>

                            <!-- Core Combat & Passive Stats Row -->
                            <div
                                class="grid grid-cols-3 gap-2 text-center sm:grid-cols-5"
                            >
                                <div
                                    class="rounded-sm border border-[#e3e3e0] p-2 dark:border-[#3E3E3A]"
                                >
                                    <span
                                        class="block text-[10px] text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                    >
                                        AC
                                    </span>
                                    <span class="text-sm font-bold">
                                        {{ char.sheet_data?.armor_class ?? 10 }}
                                    </span>
                                </div>

                                <div
                                    class="rounded-sm border border-[#e3e3e0] p-2 dark:border-[#3E3E3A]"
                                >
                                    <span
                                        class="block text-[10px] text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                    >
                                        Init
                                    </span>
                                    <span class="text-sm font-bold">
                                        {{ formatSigned(initiativeOf(char)) }}
                                    </span>
                                </div>

                                <div
                                    class="rounded-sm border border-[#e3e3e0] p-2 dark:border-[#3E3E3A]"
                                >
                                    <span
                                        class="block text-[10px] text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                    >
                                        Speed
                                    </span>
                                    <span class="text-sm font-bold">
                                        {{ effectiveSpeedOf(char) }} ft
                                    </span>
                                </div>

                                <div
                                    class="rounded-sm border border-[#e3e3e0] p-2 dark:border-[#3E3E3A]"
                                >
                                    <span
                                        class="block text-[10px] text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                    >
                                        Pass. Perc
                                    </span>
                                    <span class="text-sm font-bold">
                                        {{ passivePerceptionOf(char) }}
                                    </span>
                                </div>

                                <div
                                    class="col-span-2 rounded-sm border border-[#e3e3e0] p-2 sm:col-span-1 dark:border-[#3E3E3A]"
                                >
                                    <span
                                        class="block text-[10px] text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                    >
                                        Spell DC
                                    </span>
                                    <span class="text-sm font-bold">
                                        {{ spellSaveDcOf(char) ?? '—' }}
                                    </span>
                                </div>
                            </div>

                            <!-- 6 Ability Scores Compact Strip -->
                            <div
                                class="grid grid-cols-3 gap-1.5 text-center sm:grid-cols-6"
                            >
                                <div
                                    v-for="ab in abilityKeys"
                                    :key="ab.key"
                                    class="rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] px-1.5 py-1.5 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                >
                                    <span
                                        class="block text-[10px] font-medium text-[#706f6c] dark:text-[#A1A09A]"
                                    >
                                        {{ ab.short }}
                                    </span>
                                    <span class="block text-xs font-bold">
                                        {{
                                            formatSigned(
                                                abilityModOf(char, ab.key) -
                                                    exhaustionPenaltyOf(char),
                                            )
                                        }}
                                    </span>
                                    <span
                                        class="block text-[10px] text-[#706f6c] dark:text-[#A1A09A]"
                                    >
                                        ({{ abilityScoreOf(char, ab.key) }})
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer: Heroic Inspiration, 2024 Exhaustion & Weapon Masteries (Read-Only) -->
                        <div
                            class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-[#e3e3e0] pt-3 text-xs dark:border-[#3E3E3A]"
                        >
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold"
                                    :class="
                                        char.sheet_data?.heroic_inspiration
                                            ? 'bg-[#f53003] text-white shadow-xs dark:bg-[#FF4433]'
                                            : 'border border-[#e3e3e0] text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]'
                                    "
                                >
                                    {{
                                        char.sheet_data?.heroic_inspiration
                                            ? '★ Inspired'
                                            : 'No Inspiration'
                                    }}
                                </span>

                                <div
                                    class="flex items-center gap-1.5 rounded-full border border-[#e3e3e0] px-2.5 py-0.5 text-[11px] dark:border-[#3E3E3A]"
                                >
                                    <span
                                        class="text-[#706f6c] dark:text-[#A1A09A]"
                                    >
                                        Exhaustion:
                                    </span>
                                    <strong
                                        :class="
                                            exhaustionOf(char) > 0
                                                ? 'text-[#f53003] dark:text-[#FF4433]'
                                                : ''
                                        "
                                    >
                                        {{ exhaustionOf(char) }}
                                    </strong>
                                    <span
                                        v-if="exhaustionPenaltyOf(char) > 0"
                                        class="text-[10px] font-semibold text-[#f53003] dark:text-[#FF4433]"
                                    >
                                        (-{{ exhaustionPenaltyOf(char) }})
                                    </span>
                                </div>
                            </div>

                            <div
                                v-if="
                                    char.sheet_data?.weapon_masteries &&
                                    char.sheet_data.weapon_masteries.length > 0
                                "
                                class="flex flex-wrap items-center gap-1"
                            >
                                <span
                                    v-for="m in char.sheet_data
                                        .weapon_masteries"
                                    :key="m"
                                    class="rounded-xs bg-[#1b1b18]/10 px-1.5 py-0.5 text-[10px] font-medium dark:bg-[#EDEDEC]/10"
                                >
                                    {{ m }}
                                </span>
                            </div>
                        </div>
                    </article>
                </div>
            </section>
        </main>
    </div>
</template>
