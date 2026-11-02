<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AuthController from '@/actions/App/Http/Controllers/AuthController';
import CharacterController from '@/actions/App/Http/Controllers/CharacterController';
import { dashboard } from '@/routes';
import campaigns from '@/routes/campaigns';
import characters from '@/routes/characters';
import settings from '@/routes/settings';
import type { Character, CharacterCampaignRef, System } from '@/types';

const props = withDefaults(
    defineProps<{
        characters: Character[];
        systems?: System[];
        availableCampaigns?: CharacterCampaignRef[];
    }>(),
    {
        systems: () => [],
        availableCampaigns: () => [],
    },
);

const page = usePage();
const user = computed(() => page.props.auth?.user);

const showCreatePanel = ref(false);

const defaultSystemId = computed(() => {
    const dnd = props.systems.find((sys) => sys.code === 'dnd5e_2024');
    return dnd?.id ?? props.systems[0]?.id ?? '';
});

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

function proficiencyBonusFor(level?: number): number {
    const lvl = Math.max(1, Math.min(20, level ?? 1));
    return Math.ceil(lvl / 4) + 1;
}
</script>

<template>
    <div
        class="flex min-h-screen flex-col bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]"
    >
        <Head title="Characters" />

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
                        class="rounded-xs bg-[#1b1b18]/5 px-2 py-1.5 text-xs font-semibold tracking-tight sm:bg-transparent sm:p-0 sm:text-base dark:bg-[#EDEDEC]/10 sm:dark:bg-transparent"
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
                        Your Characters
                    </h1>
                    <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Create and manage your character sheets for D&amp;D 5.5E
                        (2024) SRD and other systems.
                    </p>
                </div>

                <button
                    type="button"
                    class="w-full cursor-pointer rounded-sm border border-black bg-[#1b1b18] px-4 py-2.5 text-center text-xs font-medium text-white transition-colors hover:bg-black sm:w-auto sm:py-2 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                    @click="showCreatePanel = !showCreatePanel"
                >
                    + Create Character
                </button>
            </div>

            <!-- Create Character Panel -->
            <section
                v-if="showCreatePanel"
                class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold">
                            Create a New Character
                        </h2>
                        <p
                            class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Initialize a D&amp;D 5.5E (2024) SRD character sheet
                            and customize their stats right after.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="shrink-0 cursor-pointer py-1 text-xs text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                        @click="showCreatePanel = false"
                    >
                        Close
                    </button>
                </div>

                <Form
                    v-bind="CharacterController.store.form()"
                    v-slot="{ errors, processing }"
                    class="mt-5 space-y-4"
                >
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label
                                for="char_name"
                                class="block text-sm font-medium"
                            >
                                Character name
                            </label>
                            <input
                                id="char_name"
                                v-focus
                                name="name"
                                type="text"
                                required
                                placeholder="e.g. Seraphina Vale"
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
                                for="char_avatar_url"
                                class="block text-sm font-medium"
                            >
                                Avatar URL
                                <span
                                    class="font-normal text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    (optional)
                                </span>
                            </label>
                            <input
                                id="char_avatar_url"
                                name="avatar_url"
                                type="url"
                                placeholder="https://example.com/portrait.png"
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

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label
                                for="char_system_id"
                                class="block text-sm font-medium"
                            >
                                Game system
                            </label>
                            <select
                                id="char_system_id"
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

                        <div>
                            <label
                                for="char_campaign_id"
                                class="block text-sm font-medium"
                            >
                                Campaign
                                <span
                                    class="font-normal text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    (optional)
                                </span>
                            </label>
                            <select
                                id="char_campaign_id"
                                name="campaign_id"
                                class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3.5 py-2 text-sm text-[#1b1b18] shadow-2xs transition-colors focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC]"
                            >
                                <option value="">
                                    Standalone (No campaign)
                                </option>
                                <option
                                    v-for="camp in props.availableCampaigns"
                                    :key="camp.id"
                                    :value="camp.id"
                                >
                                    {{ camp.name }}
                                </option>
                            </select>
                            <p
                                v-if="errors.campaign_id"
                                class="mt-1.5 text-xs text-[#f53003] dark:text-[#FF4433]"
                            >
                                {{ errors.campaign_id }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        <div>
                            <label
                                for="char_species"
                                class="block text-sm font-medium"
                            >
                                Species (2024)
                            </label>
                            <select
                                id="char_species"
                                name="species"
                                class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3.5 py-2 text-sm text-[#1b1b18] shadow-2xs transition-colors focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC]"
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
                            <label
                                for="char_class"
                                class="block text-sm font-medium"
                            >
                                Class
                            </label>
                            <select
                                id="char_class"
                                name="class"
                                class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3.5 py-2 text-sm text-[#1b1b18] shadow-2xs transition-colors focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC]"
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

                        <div>
                            <label
                                for="char_background"
                                class="block text-sm font-medium"
                            >
                                Background (2024)
                            </label>
                            <select
                                id="char_background"
                                name="background"
                                class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3.5 py-2 text-sm text-[#1b1b18] shadow-2xs transition-colors focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC]"
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

                        <div>
                            <label
                                for="char_level"
                                class="block text-sm font-medium"
                            >
                                Starting level
                            </label>
                            <input
                                id="char_level"
                                name="level"
                                type="number"
                                min="1"
                                max="20"
                                value="1"
                                class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3.5 py-2 text-sm text-[#1b1b18] shadow-2xs transition-colors focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC]"
                            />
                        </div>
                    </div>

                    <div
                        class="flex flex-col items-stretch gap-2.5 pt-2 sm:flex-row sm:items-center sm:gap-3"
                    >
                        <button
                            type="submit"
                            :disabled="processing"
                            class="cursor-pointer rounded-sm border border-black bg-[#1b1b18] px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-black disabled:opacity-60 sm:py-2 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                        >
                            <span v-if="processing">Creating...</span>
                            <span v-else>Create &amp; open sheet</span>
                        </button>
                        <button
                            type="button"
                            class="cursor-pointer rounded-sm border border-[#e3e3e0] px-4 py-2.5 text-sm font-medium transition-colors hover:border-[#1b1b18] sm:py-2 dark:border-[#3E3E3A] dark:hover:border-[#EDEDEC]"
                            @click="showCreatePanel = false"
                        >
                            Cancel
                        </button>
                    </div>
                </Form>
            </section>

            <!-- Characters Grid -->
            <div
                v-if="props.characters.length === 0"
                class="rounded-lg bg-white p-6 text-center shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-10 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <p class="text-sm font-medium">
                    You haven't created any characters yet.
                </p>
                <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                    Click "+ Create Character" above to build your first
                    character.
                </p>
            </div>

            <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="char in props.characters"
                    :key="char.id"
                    :href="characters.show(char.id)"
                    class="group flex flex-col justify-between rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] transition-all hover:shadow-[inset_0px_0px_0px_1px_#1b1b18] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] dark:hover:shadow-[inset_0px_0px_0px_1px_#EDEDEC]"
                >
                    <div>
                        <div class="flex items-center gap-3.5">
                            <img
                                v-if="char.avatar_url"
                                :src="char.avatar_url"
                                :alt="char.name"
                                class="h-12 w-12 shrink-0 rounded-full border border-[#e3e3e0] object-cover dark:border-[#3E3E3A]"
                            />
                            <div
                                v-else
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] text-base font-semibold uppercase dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                            >
                                {{ char.name.charAt(0) }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <h2
                                    class="truncate text-base font-semibold group-hover:underline"
                                >
                                    {{ char.name }}
                                </h2>
                                <p
                                    class="truncate text-xs text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    Level {{ char.sheet_data?.level ?? 1 }}
                                    {{ char.sheet_data?.species ?? 'Human' }}
                                    {{ char.sheet_data?.class ?? 'Adventurer' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-1.5">
                            <span
                                v-if="char.system"
                                class="inline-flex items-center rounded-xs border border-[#e3e3e0] px-2 py-0.5 text-[11px] font-medium text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                            >
                                {{ char.system.name }}
                            </span>
                            <span
                                v-if="char.campaign"
                                class="inline-flex items-center rounded-xs bg-[#1b1b18] px-2 py-0.5 text-[11px] font-medium text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]"
                            >
                                {{ char.campaign.name }}
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center rounded-xs border border-dashed border-[#e3e3e0] px-2 py-0.5 text-[11px] text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                            >
                                Standalone
                            </span>
                        </div>
                    </div>

                    <div
                        class="mt-6 grid grid-cols-3 gap-2 border-t border-[#e3e3e0] pt-4 text-center dark:border-[#3E3E3A]"
                    >
                        <div>
                            <span
                                class="block text-[10px] font-medium tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                            >
                                HP
                            </span>
                            <span class="text-xs font-semibold">
                                {{ char.sheet_data?.hp?.current ?? 10 }} /
                                {{ char.sheet_data?.hp?.max ?? 10 }}
                            </span>
                        </div>
                        <div>
                            <span
                                class="block text-[10px] font-medium tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                            >
                                AC
                            </span>
                            <span class="text-xs font-semibold">
                                {{ char.sheet_data?.armor_class ?? 10 }}
                            </span>
                        </div>
                        <div>
                            <span
                                class="block text-[10px] font-medium tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                            >
                                Prof
                            </span>
                            <span class="text-xs font-semibold">
                                +{{
                                    proficiencyBonusFor(char.sheet_data?.level)
                                }}
                            </span>
                        </div>
                    </div>
                </Link>
            </div>
        </main>
    </div>
</template>
