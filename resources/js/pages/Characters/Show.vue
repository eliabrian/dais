<script setup lang="ts">
import { Form, Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import AuthController from '@/actions/App/Http/Controllers/AuthController';
import CharacterController from '@/actions/App/Http/Controllers/CharacterController';
import { dashboard } from '@/routes';
import campaigns from '@/routes/campaigns';
import characters from '@/routes/characters';
import settings from '@/routes/settings';
import type {
    AbilityKey,
    Character,
    CharacterCampaignRef,
    CompendiumEntry,
    CompendiumType,
    SkillProficiency,
} from '@/types';

const props = withDefaults(
    defineProps<{
        character: Character;
        canEdit: boolean;
        availableCampaigns?: CharacterCampaignRef[];
        compendiumEntries?: CompendiumEntry[];
    }>(),
    {
        availableCampaigns: () => [],
        compendiumEntries: () => [],
    },
);

type SheetTab =
    | 'overview'
    | 'features'
    | 'equipments'
    | 'spells'
    | 'inventory'
    | 'journal';

const activeTab = ref<SheetTab>('overview');

const tabs: { key: SheetTab; label: string }[] = [
    { key: 'overview', label: 'Overview' },
    { key: 'features', label: 'Features' },
    { key: 'equipments', label: 'Equipments' },
    { key: 'spells', label: 'Spell' },
    { key: 'inventory', label: 'Inventory' },
    { key: 'journal', label: 'Journal' },
];

const page = usePage();
const user = computed(() => page.props.auth?.user);

// Play Mode vs Edit Stats Mode
// In Play Mode (default): Core build stats (Abilities, Skills, Saving Throws, Max HP, Level/Origins) are safely locked.
// Quick Edit is enabled for live session fields: Current/Temp HP, Hit Dice remaining, Death Saves, Exhaustion, Inspiration,
// textboxes (Features, Proficiencies, Armor, Weapons, Spells, Notes, Equipment), and Coins.
const isEditing = ref(false);
const canModify = computed(() => props.canEdit && isEditing.value);

const name = ref(props.character.name);
const avatarUrl = ref(props.character.avatar_url ?? '');
const campaignId = ref(props.character.campaign_id ?? '');
const isSaving = ref(false);
const savedRecently = ref(false);
const confirmingDelete = ref(false);

const sd = props.character.sheet_data ?? {};

const sheet = reactive({
    species: sd.species ?? 'Human',
    class: sd.class ?? 'Fighter',
    subclass: sd.subclass ?? '',
    background: sd.background ?? 'Soldier',
    origin_feat: sd.origin_feat ?? 'Alert',
    level: Number(sd.level ?? 1),
    xp: Number(sd.xp ?? 0),
    armor_class: Number(sd.armor_class ?? 10),
    armor_equipped: sd.armor_equipped ?? '',
    speed: Number(sd.speed ?? 30),
    size: sd.size ?? 'Medium',
    heroic_inspiration: Boolean(sd.heroic_inspiration ?? false),
    exhaustion: Number(sd.exhaustion ?? 0),
    hp: {
        current: Number(sd.hp?.current ?? 10),
        max: Number(sd.hp?.max ?? 10),
        temp: Number(sd.hp?.temp ?? 0),
    },
    hit_dice: {
        total: sd.hit_dice?.total ?? '1d10',
        remaining: Number(sd.hit_dice?.remaining ?? 1),
    },
    death_saves: {
        successes: Number(sd.death_saves?.successes ?? 0),
        failures: Number(sd.death_saves?.failures ?? 0),
    },
    abilities: {
        str: Number(sd.abilities?.str ?? 10),
        dex: Number(sd.abilities?.dex ?? 10),
        con: Number(sd.abilities?.con ?? 10),
        int: Number(sd.abilities?.int ?? 10),
        wis: Number(sd.abilities?.wis ?? 10),
        cha: Number(sd.abilities?.cha ?? 10),
    } as Record<AbilityKey, number>,
    saving_throws: Array.isArray(sd.saving_throws)
        ? ([...sd.saving_throws] as AbilityKey[])
        : ([] as AbilityKey[]),
    skills: { ...sd.skills } as Record<string, SkillProficiency>,
    weapon_masteries: Array.isArray(sd.weapon_masteries)
        ? [...sd.weapon_masteries]
        : ([] as string[]),
    weapons_attacks: sd.weapons_attacks ?? '',
    features_traits: sd.features_traits ?? '',
    proficiencies: sd.proficiencies ?? '',
    coins: {
        cp: Number(sd.coins?.cp ?? 0),
        sp: Number(sd.coins?.sp ?? 0),
        ep: Number(sd.coins?.ep ?? 0),
        gp: Number(sd.coins?.gp ?? 0),
        pp: Number(sd.coins?.pp ?? 0),
    },
    equipment: sd.equipment ?? '',
    spellcasting_ability: (sd.spellcasting_ability ?? 'none') as
        | 'none'
        | 'int'
        | 'wis'
        | 'cha',
    spells: sd.spells ?? '',
    notes: sd.notes ?? '',
});

const selectedCampaignName = computed(() => {
    if (!campaignId.value) {
        return 'Standalone (No campaign)';
    }
    const camp = props.availableCampaigns?.find(
        (c) => c.id === campaignId.value,
    );
    return camp ? camp.name : 'Standalone (No campaign)';
});

const abilityDefinitions: { key: AbilityKey; label: string; short: string }[] =
    [
        { key: 'str', label: 'Strength', short: 'STR' },
        { key: 'dex', label: 'Dexterity', short: 'DEX' },
        { key: 'con', label: 'Constitution', short: 'CON' },
        { key: 'int', label: 'Intelligence', short: 'INT' },
        { key: 'wis', label: 'Wisdom', short: 'WIS' },
        { key: 'cha', label: 'Charisma', short: 'CHA' },
    ];

const skillDefinitions: {
    key: string;
    label: string;
    ability: AbilityKey;
}[] = [
    { key: 'acrobatics', label: 'Acrobatics', ability: 'dex' },
    { key: 'animal_handling', label: 'Animal Handling', ability: 'wis' },
    { key: 'arcana', label: 'Arcana', ability: 'int' },
    { key: 'athletics', label: 'Athletics', ability: 'str' },
    { key: 'deception', label: 'Deception', ability: 'cha' },
    { key: 'history', label: 'History', ability: 'int' },
    { key: 'insight', label: 'Insight', ability: 'wis' },
    { key: 'intimidation', label: 'Intimidation', ability: 'cha' },
    { key: 'investigation', label: 'Investigation', ability: 'int' },
    { key: 'medicine', label: 'Medicine', ability: 'wis' },
    { key: 'nature', label: 'Nature', ability: 'int' },
    { key: 'perception', label: 'Perception', ability: 'wis' },
    { key: 'performance', label: 'Performance', ability: 'cha' },
    { key: 'persuasion', label: 'Persuasion', ability: 'cha' },
    { key: 'religion', label: 'Religion', ability: 'int' },
    { key: 'sleight_of_hand', label: 'Sleight of Hand', ability: 'dex' },
    { key: 'stealth', label: 'Stealth', ability: 'dex' },
    { key: 'survival', label: 'Survival', ability: 'wis' },
];

const masteryProperties = [
    'Cleave',
    'Graze',
    'Nick',
    'Push',
    'Sap',
    'Slow',
    'Topple',
    'Vex',
];

const coinDefinitions: {
    key: 'cp' | 'sp' | 'ep' | 'gp' | 'pp';
    label: string;
    short: string;
}[] = [
    { key: 'cp', label: 'Copper', short: 'CP' },
    { key: 'sp', label: 'Silver', short: 'SP' },
    { key: 'ep', label: 'Electrum', short: 'EP' },
    { key: 'gp', label: 'Gold', short: 'GP' },
    { key: 'pp', label: 'Platinum', short: 'PP' },
];

// D&D 5.5E (2024) SRD Rules Calculations
const proficiencyBonus = computed(() => {
    const lvl = Math.max(1, Math.min(20, Number(sheet.level) || 1));
    return Math.ceil(lvl / 4) + 1;
});

// 2024 Exhaustion Rule: -2 per exhaustion level to all d20 Tests & Spell Save DC, -5 ft speed per level
const exhaustionPenalty = computed(
    () => Math.max(0, Math.min(6, Number(sheet.exhaustion) || 0)) * 2,
);

const effectiveSpeed = computed(() =>
    Math.max(
        0,
        (Number(sheet.speed) || 0) -
            Math.max(0, Math.min(6, Number(sheet.exhaustion) || 0)) * 5,
    ),
);

function abilityMod(key: AbilityKey): number {
    const score = Number(sheet.abilities[key] ?? 10);
    return Math.floor((score - 10) / 2);
}

function formatSigned(val: number): string {
    return val >= 0 ? `+${val}` : `${val}`;
}

function isSaveProficient(key: AbilityKey): boolean {
    return sheet.saving_throws.includes(key);
}

function toggleSaveProficiency(key: AbilityKey): void {
    if (!canModify.value) {
        return;
    }
    const idx = sheet.saving_throws.indexOf(key);
    if (idx >= 0) {
        sheet.saving_throws.splice(idx, 1);
    } else {
        sheet.saving_throws.push(key);
    }
}

function savingThrowBonus(key: AbilityKey): number {
    const base = abilityMod(key);
    const prof = isSaveProficient(key) ? proficiencyBonus.value : 0;
    return base + prof - exhaustionPenalty.value;
}

function skillProficiencyLevel(skillKey: string): SkillProficiency {
    return sheet.skills[skillKey] ?? 'none';
}

function cycleSkillProficiency(skillKey: string): void {
    if (!canModify.value) {
        return;
    }
    const current = skillProficiencyLevel(skillKey);
    if (current === 'none') {
        sheet.skills[skillKey] = 'proficient';
    } else if (current === 'proficient') {
        sheet.skills[skillKey] = 'expertise';
    } else {
        sheet.skills[skillKey] = 'none';
    }
}

function skillBonus(skillKey: string, ability: AbilityKey): number {
    const base = abilityMod(ability);
    const profLevel = skillProficiencyLevel(skillKey);
    const mult =
        profLevel === 'expertise' ? 2 : profLevel === 'proficient' ? 1 : 0;
    return base + proficiencyBonus.value * mult - exhaustionPenalty.value;
}

const initiativeBonus = computed(
    () => abilityMod('dex') - exhaustionPenalty.value,
);

const passivePerception = computed(() => 10 + skillBonus('perception', 'wis'));

const spellSaveDc = computed(() => {
    if (sheet.spellcasting_ability === 'none') {
        return null;
    }
    return (
        8 +
        proficiencyBonus.value +
        abilityMod(sheet.spellcasting_ability) -
        exhaustionPenalty.value
    );
});

const spellAttackBonus = computed(() => {
    if (sheet.spellcasting_ability === 'none') {
        return null;
    }
    return (
        proficiencyBonus.value +
        abilityMod(sheet.spellcasting_ability) -
        exhaustionPenalty.value
    );
});

// Quick-Edit Death Saves: Click circle 1, 2, or 3 to set or unset
function setDeathSaves(type: 'successes' | 'failures', count: number): void {
    if (!props.canEdit) {
        return;
    }
    if (sheet.death_saves[type] === count) {
        sheet.death_saves[type] = count - 1;
    } else {
        sheet.death_saves[type] = count;
    }
}

function toggleWeaponMastery(mastery: string): void {
    if (!props.canEdit) {
        return;
    }
    const idx = sheet.weapon_masteries.indexOf(mastery);
    if (idx >= 0) {
        sheet.weapon_masteries.splice(idx, 1);
    } else {
        sheet.weapon_masteries.push(mastery);
    }
}

// 2024 SRD Compendium Picker & Auto-Calculator
const compendiumModalOpen = ref(false);
const compendiumSearch = ref('');
const compendiumFilterType = ref<CompendiumType | 'all'>('weapon');
const compendiumNotice = ref<string | null>(null);

function openCompendium(type: CompendiumType | 'all' = 'weapon'): void {
    compendiumFilterType.value = type;
    compendiumSearch.value = '';
    compendiumModalOpen.value = true;
}

function closeCompendium(): void {
    compendiumModalOpen.value = false;
    compendiumSearch.value = '';
}

const filteredCompendiumEntries = computed(() => {
    let list = props.compendiumEntries ?? [];
    if (compendiumFilterType.value !== 'all') {
        list = list.filter((e) => e.type === compendiumFilterType.value);
    }
    const q = compendiumSearch.value.trim().toLowerCase();
    if (!q) {
        return list;
    }
    return list.filter(
        (e) =>
            e.name.toLowerCase().includes(q) ||
            (e.summary && e.summary.toLowerCase().includes(q)) ||
            (e.description && e.description.toLowerCase().includes(q)),
    );
});

function flashNotice(msg: string): void {
    compendiumNotice.value = msg;
    setTimeout(() => {
        if (compendiumNotice.value === msg) {
            compendiumNotice.value = null;
        }
    }, 3500);
}

function addCompendiumEntry(entry: CompendiumEntry): void {
    if (!props.canEdit) {
        return;
    }

    if (entry.type === 'weapon') {
        let ability: AbilityKey = 'str';
        if (entry.data?.ability === 'dex_or_str') {
            ability = abilityMod('dex') >= abilityMod('str') ? 'dex' : 'str';
        } else if (entry.data?.ability === 'dex') {
            ability = 'dex';
        }

        const atkBonus =
            abilityMod(ability) +
            proficiencyBonus.value -
            exhaustionPenalty.value;
        const dmgMod = abilityMod(ability);
        const dmgSign = dmgMod >= 0 ? `+${dmgMod}` : `${dmgMod}`;
        const masteryText = entry.data?.mastery
            ? ` (${entry.data.mastery} mastery)`
            : '';
        const propsText = entry.data?.properties?.length
            ? ` [${entry.data.properties.join(', ')}]`
            : '';

        const line = `${entry.name} (${entry.data?.category ?? 'Weapon'}): ${formatSigned(atkBonus)} to hit, ${entry.data?.damage ?? '1d8'}${dmgSign} ${entry.data?.damage_type ?? ''}${masteryText}${propsText}`;

        sheet.weapons_attacks = sheet.weapons_attacks
            ? `${sheet.weapons_attacks}\n${line}`
            : line;

        if (
            entry.data?.mastery &&
            !sheet.weapon_masteries.includes(entry.data.mastery)
        ) {
            sheet.weapon_masteries.push(entry.data.mastery);
        }

        flashNotice(`Added ${entry.name} to weapons & attacks!`);
    } else if (entry.type === 'armor') {
        if (entry.data?.category === 'Shield') {
            const line = `${entry.name} (+2 AC, 6 lb)`;
            sheet.armor_equipped = sheet.armor_equipped
                ? `${sheet.armor_equipped}\n${line}`
                : line;
            sheet.armor_class = (sheet.armor_class || 10) + 2;
            flashNotice('Equipped Shield (+2 AC)!');
        } else {
            const baseAc = Number(entry.data?.ac_base ?? 11);
            let calculatedAc = baseAc;
            if (entry.data?.dex_bonus === 'full') {
                calculatedAc += abilityMod('dex');
            } else if (entry.data?.dex_bonus === 'max_2') {
                calculatedAc += Math.min(2, Math.max(0, abilityMod('dex')));
            }
            sheet.armor_class = calculatedAc;

            const stealthNote = entry.data?.stealth_disadv
                ? ', Stealth Disadvantage'
                : '';
            const strNote = entry.data?.str_req
                ? `, Str ${entry.data.str_req} req`
                : '';
            const line = `${entry.name} (AC ${baseAc}${stealthNote}${strNote}, ${entry.data?.weight ?? ''})`;

            sheet.armor_equipped = sheet.armor_equipped
                ? `${sheet.armor_equipped}\n${line}`
                : line;
            flashNotice(
                `Equipped ${entry.name} (AC calculated to ${calculatedAc})!`,
            );
        }
    } else if (entry.type === 'spell') {
        let spellDetail = '';
        if (entry.data?.attack_type) {
            const atk =
                spellAttackBonus.value !== null
                    ? formatSigned(spellAttackBonus.value)
                    : formatSigned(proficiencyBonus.value + abilityMod('int'));
            spellDetail = ` · Spell Attack: ${atk} to hit`;
        }
        if (entry.data?.save_ability) {
            const dc =
                spellSaveDc.value !== null
                    ? spellSaveDc.value
                    : 8 + proficiencyBonus.value + abilityMod('int');
            spellDetail += ` · Save: DC ${dc} ${String(entry.data.save_ability).toUpperCase()}`;
        }

        const levelLabel =
            entry.data?.level === 0
                ? 'Cantrip'
                : `Level ${entry.data?.level ?? 1}`;
        const line = `${entry.name} (${levelLabel} ${entry.data?.school ?? ''}): ${entry.data?.casting_time ?? '1 Action'}, ${entry.data?.range ?? 'Touch'}${spellDetail}. ${entry.summary ?? ''}`;

        sheet.spells = sheet.spells ? `${sheet.spells}\n${line}` : line;
        flashNotice(`Added ${entry.name} to spells!`);
    } else if (entry.type === 'gear') {
        const line = `${entry.name}: ${entry.summary ?? ''}`;
        sheet.equipment = sheet.equipment
            ? `${sheet.equipment}\n${line}`
            : line;
        flashNotice(`Added ${entry.name} to inventory!`);
    }
}

function saveCharacter(): void {
    if (!props.canEdit || isSaving.value) {
        return;
    }

    isSaving.value = true;

    router.patch(
        CharacterController.update.url(props.character.id),
        {
            name: name.value,
            avatar_url: avatarUrl.value || null,
            campaign_id: campaignId.value || null,
            sheet_data: {
                ...sheet,
                hp: { ...sheet.hp },
                hit_dice: { ...sheet.hit_dice },
                death_saves: { ...sheet.death_saves },
                abilities: { ...sheet.abilities },
                saving_throws: [...sheet.saving_throws],
                skills: { ...sheet.skills },
                weapon_masteries: [...sheet.weapon_masteries],
                coins: { ...sheet.coins },
            },
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                savedRecently.value = true;
                setTimeout(() => {
                    savedRecently.value = false;
                }, 2500);
            },
            onFinish: () => {
                isSaving.value = false;
            },
        },
    );
}
</script>

<template>
    <div
        class="flex min-h-screen flex-col bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]"
    >
        <Head :title="`${name} - Character Sheet`" />

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
            class="mx-auto w-full max-w-6xl flex-1 space-y-5 px-4 py-5 sm:space-y-6 sm:p-6"
        >
            <!-- Top Bar: Back Link, Play / Edit Stats Mode Toggle & Save Button -->
            <div
                class="sticky top-0 z-10 -mx-4 flex flex-wrap items-center justify-between gap-3 border-b border-[#e3e3e0]/80 bg-[#FDFDFC]/95 px-4 py-2.5 backdrop-blur-xs sm:static sm:mx-0 sm:border-b-0 sm:bg-transparent sm:p-0 dark:border-[#3E3E3A]/80 dark:bg-[#0a0a0a]/95 sm:dark:bg-transparent"
            >
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <Link
                        :href="characters.index()"
                        class="text-xs font-medium tracking-wider text-[#706f6c] uppercase transition-colors hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                    >
                        &larr; All characters
                    </Link>
                    <span
                        v-if="props.character.system"
                        class="rounded-xs border border-[#e3e3e0] bg-white px-2 py-0.5 text-[11px] font-medium text-[#706f6c] sm:text-xs dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#A1A09A]"
                    >
                        {{ props.character.system.name }}
                    </span>
                </div>

                <div
                    v-if="props.canEdit"
                    class="flex flex-wrap items-center gap-2 sm:gap-3"
                >
                    <!-- Mode Toggle: Play Mode vs Edit Stats Mode -->
                    <div
                        class="inline-flex rounded-sm border border-[#e3e3e0] bg-white p-0.5 shadow-xs dark:border-[#3E3E3A] dark:bg-[#161615]"
                        role="group"
                        aria-label="Sheet display mode"
                    >
                        <button
                            type="button"
                            class="flex cursor-pointer items-center gap-1.5 rounded-xs px-2.5 py-1 text-xs font-semibold transition-colors"
                            :class="
                                !isEditing
                                    ? 'bg-[#1b1b18] text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                    : 'text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]'
                            "
                            @click="isEditing = false"
                        >
                            <svg
                                class="h-3.5 w-3.5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            <span>Play Mode</span>
                        </button>
                        <button
                            type="button"
                            class="flex cursor-pointer items-center gap-1.5 rounded-xs px-2.5 py-1 text-xs font-semibold transition-colors"
                            :class="
                                isEditing
                                    ? 'bg-[#1b1b18] text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                    : 'text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]'
                            "
                            @click="isEditing = true"
                        >
                            <svg
                                class="h-3.5 w-3.5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                />
                            </svg>
                            <span>Edit Stats</span>
                        </button>
                    </div>

                    <span
                        v-if="savedRecently"
                        class="text-xs font-medium text-emerald-600 dark:text-emerald-400"
                    >
                        Sheet saved!
                    </span>

                    <button
                        type="button"
                        :disabled="isSaving"
                        class="cursor-pointer rounded-sm border border-black bg-[#1b1b18] px-3.5 py-1.5 text-xs font-medium text-white transition-colors hover:bg-black disabled:opacity-60 sm:px-4 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                        @click="saveCharacter"
                    >
                        <span v-if="isSaving">Saving...</span>
                        <span v-else>Save sheet</span>
                    </button>
                </div>
            </div>

            <!-- Edit Stats Notice Banner -->
            <div
                v-if="isEditing"
                class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-2.5 text-xs text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/15 dark:text-amber-200"
            >
                <div class="flex items-center gap-2">
                    <span
                        class="inline-block h-2 w-2 animate-pulse rounded-full bg-amber-500"
                    />
                    <span>
                        <strong>Edit Stats Mode active:</strong> Core ability
                        scores, skill proficiencies, saving throws, max HP, and
                        character build are unlocked for editing.
                    </span>
                </div>
                <button
                    type="button"
                    class="cursor-pointer font-semibold underline transition-opacity hover:opacity-80"
                    @click="isEditing = false"
                >
                    Done editing stats
                </button>
            </div>

            <!-- Compact Character Header & Tab Navigation -->
            <section
                class="rounded-lg bg-white p-4 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex min-w-0 flex-1 items-center gap-3.5">
                        <img
                            v-if="avatarUrl"
                            :src="avatarUrl"
                            :alt="name"
                            class="h-12 w-12 shrink-0 rounded-full border border-[#e3e3e0] object-cover sm:h-14 sm:w-14 dark:border-[#3E3E3A]"
                        />
                        <span
                            v-else
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] text-base font-bold uppercase sm:h-14 sm:w-14 sm:text-lg dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                        >
                            {{ name ? name.charAt(0) : '?' }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <!-- Play Mode: Clean name display -->
                            <div v-if="!canModify">
                                <h1
                                    class="truncate text-lg font-bold sm:text-xl"
                                >
                                    {{ name || 'Unnamed Character' }}
                                </h1>
                            </div>
                            <!-- Edit Stats Mode: Name input -->
                            <input
                                v-else
                                v-model="name"
                                type="text"
                                placeholder="Character Name"
                                class="block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-1.5 text-base font-bold focus:border-[#1b1b18] focus:outline-none sm:text-lg dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:focus:border-[#EDEDEC]"
                            />

                            <p
                                class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                <span
                                    class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"
                                >
                                    Level {{ sheet.level }}
                                </span>
                                <span>&bull;</span>
                                <span>{{ sheet.species || 'Species' }}</span>
                                <span>&bull;</span>
                                <span>
                                    {{ sheet.class || 'Class' }}
                                    <template v-if="sheet.subclass">
                                        ({{ sheet.subclass }})
                                    </template>
                                </span>
                                <template v-if="sheet.background">
                                    <span>&bull;</span>
                                    <span>{{ sheet.background }}</span>
                                </template>
                            </p>
                        </div>
                    </div>

                    <!-- Campaign -->
                    <div class="w-full sm:w-56">
                        <span
                            class="block text-[11px] font-medium text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Campaign
                        </span>
                        <!-- Play Mode -->
                        <div
                            v-if="!canModify"
                            class="mt-1 truncate text-xs font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"
                        >
                            {{ selectedCampaignName }}
                        </div>
                        <!-- Edit Stats Mode -->
                        <select
                            v-else
                            v-model="campaignId"
                            class="mt-1 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-xs font-medium focus:border-[#1b1b18] focus:outline-none sm:py-1.5 dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:focus:border-[#EDEDEC]"
                        >
                            <option value="">Standalone (No campaign)</option>
                            <option
                                v-for="camp in props.availableCampaigns"
                                :key="camp.id"
                                :value="camp.id"
                            >
                                {{ camp.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- 6-Tab Navigation Bar -->
                <div
                    class="mt-5 grid grid-cols-3 gap-1.5 border-t border-[#e3e3e0] pt-4 sm:grid-cols-6 dark:border-[#3E3E3A]"
                    role="tablist"
                    aria-label="Character sheet sections"
                >
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        type="button"
                        role="tab"
                        :aria-selected="activeTab === tab.key"
                        class="cursor-pointer rounded-sm border px-2.5 py-2 text-center text-xs font-semibold transition-colors"
                        :class="
                            activeTab === tab.key
                                ? 'border-[#1b1b18] bg-[#1b1b18] text-white dark:border-[#EDEDEC] dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                : 'border-[#e3e3e0] bg-[#FDFDFC] text-[#706f6c] hover:border-[#1b1b18] hover:text-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#A1A09A] dark:hover:border-[#EDEDEC] dark:hover:text-[#EDEDEC]'
                        "
                        @click="activeTab = tab.key"
                    >
                        {{ tab.label }}
                    </button>
                </div>
            </section>

            <!-- TAB 1: OVERVIEW (Stats, Skills, HP, AC, Saving Throws, Combat Vitals) -->
            <div
                v-show="activeTab === 'overview'"
                class="space-y-5 sm:space-y-6"
            >
                <!-- Core Derived & Combat Stats Strip -->
                <section
                    class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7"
                >
                    <div
                        class="rounded-lg bg-white p-3.5 text-center shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-4 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                    >
                        <span
                            class="block text-[10px] font-medium tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Proficiency
                        </span>
                        <span class="mt-1 block text-xl font-bold">
                            +{{ proficiencyBonus }}
                        </span>
                    </div>

                    <div
                        class="rounded-lg bg-white p-3.5 text-center shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-4 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                    >
                        <span
                            class="block text-[10px] font-medium tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Armor Class
                        </span>
                        <!-- AC base: View in Play Mode, Input in Edit Stats Mode -->
                        <span
                            v-if="!canModify"
                            class="mt-1 block text-2xl font-bold"
                        >
                            {{ sheet.armor_class }}
                        </span>
                        <input
                            v-else
                            v-model.number="sheet.armor_class"
                            type="number"
                            class="mt-1 w-16 rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] py-0.5 text-center text-lg font-bold dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                        />
                    </div>

                    <div
                        class="rounded-lg bg-white p-3.5 text-center shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-4 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                    >
                        <span
                            class="block text-[10px] font-medium tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Initiative
                        </span>
                        <span class="mt-1 block text-xl font-bold">
                            {{ formatSigned(initiativeBonus) }}
                        </span>
                    </div>

                    <div
                        class="rounded-lg bg-white p-3.5 text-center shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-4 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                    >
                        <span
                            class="block text-[10px] font-medium tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Speed
                        </span>
                        <div v-if="!canModify">
                            <span class="mt-1 block text-xl font-bold">
                                {{ effectiveSpeed }} ft
                            </span>
                            <span
                                v-if="exhaustionPenalty > 0"
                                class="text-[10px] text-[#f53003] dark:text-[#FF4433]"
                            >
                                (base {{ sheet.speed }} ft)
                            </span>
                        </div>
                        <input
                            v-else
                            v-model.number="sheet.speed"
                            type="number"
                            step="5"
                            class="mt-1 w-16 rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] py-0.5 text-center text-lg font-bold dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                        />
                    </div>

                    <div
                        class="rounded-lg bg-white p-3.5 text-center shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-4 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                    >
                        <span
                            class="block text-[10px] font-medium tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Passive Perc.
                        </span>
                        <span class="mt-1 block text-xl font-bold">
                            {{ passivePerception }}
                        </span>
                    </div>

                    <!-- Heroic Inspiration: Quick Toggle Always Active -->
                    <div
                        class="rounded-lg bg-white p-3.5 text-center shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-4 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                    >
                        <span
                            class="block text-[10px] font-medium tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Heroic Insp.
                        </span>
                        <button
                            type="button"
                            :disabled="!props.canEdit"
                            class="mt-1.5 inline-flex cursor-pointer items-center rounded-full px-3 py-1 text-xs font-semibold transition-colors disabled:cursor-default sm:py-0.5"
                            :class="
                                sheet.heroic_inspiration
                                    ? 'bg-[#f53003] text-white shadow-xs dark:bg-[#FF4433]'
                                    : 'border border-[#e3e3e0] text-[#706f6c] hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:text-[#A1A09A] dark:hover:border-[#EDEDEC]'
                            "
                            @click="
                                sheet.heroic_inspiration =
                                    !sheet.heroic_inspiration
                            "
                        >
                            {{ sheet.heroic_inspiration ? 'Inspired' : 'None' }}
                        </button>
                    </div>

                    <!-- Exhaustion (0-6): Quick Edit Stepper Always Active -->
                    <div
                        class="col-span-2 rounded-lg bg-white p-3.5 text-center shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:col-span-2 sm:p-4 lg:col-span-1 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                    >
                        <span
                            class="block text-[10px] font-medium tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Exhaustion (0-6)
                        </span>
                        <div
                            class="mt-1.5 flex items-center justify-center gap-1.5"
                        >
                            <button
                                type="button"
                                :disabled="
                                    !props.canEdit || sheet.exhaustion <= 0
                                "
                                class="flex h-6 w-6 cursor-pointer items-center justify-center rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] text-xs font-bold transition-colors hover:border-[#1b1b18] disabled:cursor-default disabled:opacity-30 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                @click="
                                    sheet.exhaustion = Math.max(
                                        0,
                                        sheet.exhaustion - 1,
                                    )
                                "
                            >
                                &minus;
                            </button>
                            <span
                                class="w-6 text-center text-lg font-bold"
                                :class="
                                    sheet.exhaustion > 0
                                        ? 'text-[#f53003] dark:text-[#FF4433]'
                                        : ''
                                "
                            >
                                {{ sheet.exhaustion }}
                            </span>
                            <button
                                type="button"
                                :disabled="
                                    !props.canEdit || sheet.exhaustion >= 6
                                "
                                class="flex h-6 w-6 cursor-pointer items-center justify-center rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] text-xs font-bold transition-colors hover:border-[#1b1b18] disabled:cursor-default disabled:opacity-30 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                @click="
                                    sheet.exhaustion = Math.min(
                                        6,
                                        sheet.exhaustion + 1,
                                    )
                                "
                            >
                                +
                            </button>
                            <span
                                v-if="exhaustionPenalty > 0"
                                class="text-xs font-semibold text-[#f53003] dark:text-[#FF4433]"
                            >
                                (-{{ exhaustionPenalty }})
                            </span>
                        </div>
                    </div>
                </section>

                <!-- Hit Points, Hit Dice & Death Saves -->
                <section class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
                    <!-- Hit Points: Quick Edit Current & Temp HP, Max HP protected in Edit Stats Mode -->
                    <div
                        class="rounded-lg bg-white p-4 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:col-span-2 sm:p-5 md:col-span-1 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                    >
                        <h2
                            class="text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Hit Points
                        </h2>
                        <div
                            class="mt-3 grid grid-cols-3 gap-2.5 text-center sm:gap-3"
                        >
                            <!-- Current HP: Quick Edit with Stepper & Input -->
                            <div>
                                <label
                                    class="block text-[11px] text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    Current
                                </label>
                                <div
                                    class="mt-1 flex items-center justify-center gap-1"
                                >
                                    <button
                                        type="button"
                                        :disabled="
                                            !props.canEdit ||
                                            sheet.hp.current <= 0
                                        "
                                        class="flex h-7 w-6 cursor-pointer items-center justify-center rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] text-xs font-bold hover:border-[#1b1b18] disabled:cursor-default disabled:opacity-30 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                        @click="
                                            sheet.hp.current = Math.max(
                                                0,
                                                sheet.hp.current - 1,
                                            )
                                        "
                                    >
                                        &minus;
                                    </button>
                                    <input
                                        v-model.number="sheet.hp.current"
                                        :disabled="!props.canEdit"
                                        type="number"
                                        class="w-14 rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] py-1 text-center text-base font-bold text-emerald-600 focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-emerald-400"
                                    />
                                    <button
                                        type="button"
                                        :disabled="!props.canEdit"
                                        class="flex h-7 w-6 cursor-pointer items-center justify-center rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] text-xs font-bold hover:border-[#1b1b18] disabled:cursor-default disabled:opacity-30 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                        @click="
                                            sheet.hp.current =
                                                sheet.hp.current + 1
                                        "
                                    >
                                        +
                                    </button>
                                </div>
                            </div>

                            <!-- Max HP: Protected from accidental editing in Play Mode -->
                            <div>
                                <label
                                    class="block text-[11px] text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    Max
                                </label>
                                <span
                                    v-if="!canModify"
                                    class="mt-1 block py-1 text-center text-lg font-bold sm:text-xl"
                                >
                                    {{ sheet.hp.max }}
                                </span>
                                <input
                                    v-else
                                    v-model.number="sheet.hp.max"
                                    type="number"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] py-1 text-center text-base font-bold dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                />
                            </div>

                            <!-- Temp HP: Quick Edit with Stepper & Input -->
                            <div>
                                <label
                                    class="block text-[11px] text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    Temp
                                </label>
                                <div
                                    class="mt-1 flex items-center justify-center gap-1"
                                >
                                    <button
                                        type="button"
                                        :disabled="
                                            !props.canEdit || sheet.hp.temp <= 0
                                        "
                                        class="flex h-7 w-6 cursor-pointer items-center justify-center rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] text-xs font-bold hover:border-[#1b1b18] disabled:cursor-default disabled:opacity-30 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                        @click="
                                            sheet.hp.temp = Math.max(
                                                0,
                                                sheet.hp.temp - 1,
                                            )
                                        "
                                    >
                                        &minus;
                                    </button>
                                    <input
                                        v-model.number="sheet.hp.temp"
                                        :disabled="!props.canEdit"
                                        type="number"
                                        min="0"
                                        class="w-14 rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] py-1 text-center text-base font-bold text-blue-600 focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-blue-400"
                                    />
                                    <button
                                        type="button"
                                        :disabled="!props.canEdit"
                                        class="flex h-7 w-6 cursor-pointer items-center justify-center rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] text-xs font-bold hover:border-[#1b1b18] disabled:cursor-default disabled:opacity-30 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                        @click="
                                            sheet.hp.temp = sheet.hp.temp + 1
                                        "
                                    >
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hit Dice: Quick Edit Remaining, Total Pool in Edit Stats Mode -->
                    <div
                        class="rounded-lg bg-white p-4 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-5 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                    >
                        <h2
                            class="text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Hit Dice
                        </h2>
                        <div class="mt-3 grid grid-cols-2 gap-3 text-center">
                            <div>
                                <label
                                    class="block text-[11px] text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    Remaining
                                </label>
                                <div
                                    class="mt-1 flex items-center justify-center gap-1"
                                >
                                    <button
                                        type="button"
                                        :disabled="
                                            !props.canEdit ||
                                            sheet.hit_dice.remaining <= 0
                                        "
                                        class="flex h-7 w-6 cursor-pointer items-center justify-center rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] text-xs font-bold hover:border-[#1b1b18] disabled:cursor-default disabled:opacity-30 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                        @click="
                                            sheet.hit_dice.remaining = Math.max(
                                                0,
                                                sheet.hit_dice.remaining - 1,
                                            )
                                        "
                                    >
                                        &minus;
                                    </button>
                                    <input
                                        v-model.number="
                                            sheet.hit_dice.remaining
                                        "
                                        :disabled="!props.canEdit"
                                        type="number"
                                        min="0"
                                        class="w-14 rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] py-1 text-center text-base font-bold dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                    />
                                    <button
                                        type="button"
                                        :disabled="!props.canEdit"
                                        class="flex h-7 w-6 cursor-pointer items-center justify-center rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] text-xs font-bold hover:border-[#1b1b18] disabled:cursor-default disabled:opacity-30 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                        @click="
                                            sheet.hit_dice.remaining =
                                                sheet.hit_dice.remaining + 1
                                        "
                                    >
                                        +
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label
                                    class="block text-[11px] text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    Total Pool
                                </label>
                                <span
                                    v-if="!canModify"
                                    class="mt-1 block py-1 text-center text-base font-bold sm:text-lg"
                                >
                                    {{ sheet.hit_dice.total || '—' }}
                                </span>
                                <input
                                    v-else
                                    v-model="sheet.hit_dice.total"
                                    type="text"
                                    placeholder="e.g. 3d10"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] py-1 text-center text-base font-bold dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Death Saves: 1-Click Interactive Dots Always Active -->
                    <div
                        class="rounded-lg bg-white p-4 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-5 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                    >
                        <h2
                            class="text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Death Saves
                        </h2>
                        <div class="mt-3 grid grid-cols-2 gap-3 text-center">
                            <div>
                                <label
                                    class="block text-[11px] text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    Successes ({{ sheet.death_saves.successes }}
                                    / 3)
                                </label>
                                <div
                                    class="mt-2.5 flex items-center justify-center gap-2"
                                >
                                    <button
                                        v-for="i in 3"
                                        :key="`succ-${i}`"
                                        type="button"
                                        :disabled="!props.canEdit"
                                        class="h-5 w-5 cursor-pointer rounded-full border transition-all disabled:cursor-default"
                                        :class="
                                            i <= sheet.death_saves.successes
                                                ? 'border-emerald-600 bg-emerald-500 shadow-xs'
                                                : 'border-[#e3e3e0] bg-transparent hover:border-emerald-400 dark:border-[#3E3E3A]'
                                        "
                                        :title="`Death Save Success ${i}`"
                                        @click="setDeathSaves('successes', i)"
                                    />
                                </div>
                            </div>
                            <div>
                                <label
                                    class="block text-[11px] text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    Failures ({{ sheet.death_saves.failures }} /
                                    3)
                                </label>
                                <div
                                    class="mt-2.5 flex items-center justify-center gap-2"
                                >
                                    <button
                                        v-for="i in 3"
                                        :key="`fail-${i}`"
                                        type="button"
                                        :disabled="!props.canEdit"
                                        class="h-5 w-5 cursor-pointer rounded-full border transition-all disabled:cursor-default"
                                        :class="
                                            i <= sheet.death_saves.failures
                                                ? 'border-[#f53003] bg-[#f53003] shadow-xs'
                                                : 'border-[#e3e3e0] bg-transparent hover:border-[#f53003] dark:border-[#3E3E3A]'
                                        "
                                        :title="`Death Save Failure ${i}`"
                                        @click="setDeathSaves('failures', i)"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 6 Ability Scores & Saving Throws (Protected in Play Mode, Unlocked in Edit Stats Mode) -->
                <section
                    class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6"
                >
                    <div
                        v-for="ab in abilityDefinitions"
                        :key="ab.key"
                        class="flex flex-col items-center justify-between rounded-lg bg-white p-3.5 text-center shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-4 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                    >
                        <span
                            class="text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            {{ ab.label }}
                        </span>

                        <!-- Modifier -->
                        <span class="my-2 text-2xl font-bold">
                            {{
                                formatSigned(
                                    abilityMod(ab.key) - exhaustionPenalty,
                                )
                            }}
                        </span>

                        <!-- Score: Readout in Play Mode, Input in Edit Stats Mode -->
                        <div
                            v-if="!canModify"
                            class="rounded-xs bg-[#FDFDFC] px-2 py-0.5 text-xs text-[#706f6c] dark:bg-[#0a0a0a] dark:text-[#A1A09A]"
                        >
                            Score:
                            <strong
                                class="text-[#1b1b18] dark:text-[#EDEDEC]"
                                >{{ sheet.abilities[ab.key] }}</strong
                            >
                        </div>
                        <input
                            v-else
                            v-model.number="sheet.abilities[ab.key]"
                            type="number"
                            min="1"
                            max="30"
                            class="w-16 rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] py-1 text-center text-xs font-semibold dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                        />

                        <!-- Saving Throw toggle / badge -->
                        <div
                            v-if="!canModify"
                            class="mt-3 flex w-full items-center justify-between rounded-xs border px-2.5 py-1 text-xs"
                            :class="
                                isSaveProficient(ab.key)
                                    ? 'border-[#1b1b18] bg-[#1b1b18] text-white dark:border-[#EDEDEC] dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                    : 'border-[#e3e3e0] text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]'
                            "
                        >
                            <span>Save</span>
                            <span class="font-semibold">
                                {{ formatSigned(savingThrowBonus(ab.key)) }}
                            </span>
                        </div>
                        <button
                            v-else
                            type="button"
                            class="mt-3 flex w-full cursor-pointer items-center justify-between rounded-xs border px-2.5 py-1 text-xs transition-colors"
                            :class="
                                isSaveProficient(ab.key)
                                    ? 'border-[#1b1b18] bg-[#1b1b18] text-white dark:border-[#EDEDEC] dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                    : 'border-[#e3e3e0] text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]'
                            "
                            @click="toggleSaveProficiency(ab.key)"
                        >
                            <span>Save</span>
                            <span class="font-semibold">
                                {{ formatSigned(savingThrowBonus(ab.key)) }}
                            </span>
                        </button>
                    </div>
                </section>

                <!-- 18 Skills (2024): Protected in Play Mode, Editable in Edit Stats Mode -->
                <section
                    class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-2"
                    >
                        <h2 class="text-base font-semibold">Skills (2024)</h2>
                        <span
                            class="text-[11px] text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            <template v-if="canModify">
                                Tap badge: None &rarr; Prof &rarr; Exp
                            </template>
                            <template v-else>
                                Switch to "Edit Stats" to adjust proficiencies
                            </template>
                        </span>
                    </div>

                    <ul
                        class="mt-4 grid gap-x-8 divide-y divide-[#e3e3e0] sm:grid-cols-2 lg:grid-cols-3 dark:divide-[#3E3E3A]"
                    >
                        <li
                            v-for="skill in skillDefinitions"
                            :key="skill.key"
                            class="flex items-center justify-between gap-2 py-2.5 text-sm sm:py-2"
                        >
                            <div class="flex min-w-0 items-center gap-2.5">
                                <span
                                    v-if="!canModify"
                                    class="flex h-5 w-10 shrink-0 items-center justify-center rounded-xs border text-[10px] font-semibold uppercase"
                                    :class="
                                        skillProficiencyLevel(skill.key) ===
                                        'expertise'
                                            ? 'border-[#f53003] bg-[#f53003] text-white dark:border-[#FF4433] dark:bg-[#FF4433]'
                                            : skillProficiencyLevel(
                                                    skill.key,
                                                ) === 'proficient'
                                              ? 'border-[#1b1b18] bg-[#1b1b18] text-white dark:border-[#EDEDEC] dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                              : 'border-[#e3e3e0] text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]'
                                    "
                                >
                                    {{
                                        skillProficiencyLevel(skill.key) ===
                                        'expertise'
                                            ? 'EXP'
                                            : skillProficiencyLevel(
                                                    skill.key,
                                                ) === 'proficient'
                                              ? 'PROF'
                                              : '—'
                                    }}
                                </span>
                                <button
                                    v-else
                                    type="button"
                                    class="flex h-5 w-10 shrink-0 cursor-pointer items-center justify-center rounded-xs border text-[10px] font-semibold uppercase transition-colors"
                                    :class="
                                        skillProficiencyLevel(skill.key) ===
                                        'expertise'
                                            ? 'border-[#f53003] bg-[#f53003] text-white dark:border-[#FF4433] dark:bg-[#FF4433]'
                                            : skillProficiencyLevel(
                                                    skill.key,
                                                ) === 'proficient'
                                              ? 'border-[#1b1b18] bg-[#1b1b18] text-white dark:border-[#EDEDEC] dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                              : 'border-[#e3e3e0] text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]'
                                    "
                                    @click="cycleSkillProficiency(skill.key)"
                                >
                                    {{
                                        skillProficiencyLevel(skill.key) ===
                                        'expertise'
                                            ? 'EXP'
                                            : skillProficiencyLevel(
                                                    skill.key,
                                                ) === 'proficient'
                                              ? 'PROF'
                                              : '—'
                                    }}
                                </button>
                                <span class="truncate">{{ skill.label }}</span>
                                <span
                                    class="shrink-0 text-xs text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                >
                                    ({{ skill.ability }})
                                </span>
                            </div>

                            <span
                                class="shrink-0 font-mono text-sm font-semibold"
                            >
                                {{
                                    formatSigned(
                                        skillBonus(skill.key, skill.ability),
                                    )
                                }}
                            </span>
                        </li>
                    </ul>
                </section>
            </div>

            <!-- TAB 2: FEATURES (Origins, Traits, Class Features, Feats, Proficiencies) -->
            <div
                v-show="activeTab === 'features'"
                class="space-y-5 sm:space-y-6"
            >
                <!-- Origins & Class Progression -->
                <section
                    class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-2"
                    >
                        <div>
                            <h2 class="text-base font-semibold">
                                2024 Character Origins &amp; Class Progression
                            </h2>
                            <p
                                class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Species, Class, Subclass, Background, Origin
                                Feat, Level, Size, and XP.
                            </p>
                        </div>
                        <button
                            v-if="!canModify && props.canEdit"
                            type="button"
                            class="cursor-pointer text-xs font-semibold text-[#706f6c] underline hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                            @click="isEditing = true"
                        >
                            Edit build details &rarr;
                        </button>
                    </div>

                    <!-- Play Mode: Summary cards -->
                    <div
                        v-if="!canModify"
                        class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-4"
                    >
                        <div
                            class="rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] p-3 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                        >
                            <span
                                class="block text-[11px] font-medium text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                >Species</span
                            >
                            <span class="mt-1 block text-sm font-semibold">{{
                                sheet.species || '—'
                            }}</span>
                        </div>
                        <div
                            class="rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] p-3 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                        >
                            <span
                                class="block text-[11px] font-medium text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                >Class</span
                            >
                            <span class="mt-1 block text-sm font-semibold">{{
                                sheet.class || '—'
                            }}</span>
                        </div>
                        <div
                            class="rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] p-3 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                        >
                            <span
                                class="block text-[11px] font-medium text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                >Subclass</span
                            >
                            <span class="mt-1 block text-sm font-semibold">{{
                                sheet.subclass || '—'
                            }}</span>
                        </div>
                        <div
                            class="rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] p-3 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                        >
                            <span
                                class="block text-[11px] font-medium text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                >Background</span
                            >
                            <span class="mt-1 block text-sm font-semibold">{{
                                sheet.background || '—'
                            }}</span>
                        </div>
                        <div
                            class="rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] p-3 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                        >
                            <span
                                class="block text-[11px] font-medium text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                >Origin Feat</span
                            >
                            <span class="mt-1 block text-sm font-semibold">{{
                                sheet.origin_feat || '—'
                            }}</span>
                        </div>
                        <div
                            class="rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] p-3 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                        >
                            <span
                                class="block text-[11px] font-medium text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                >Level</span
                            >
                            <span class="mt-1 block text-sm font-semibold">{{
                                sheet.level
                            }}</span>
                        </div>
                        <div
                            class="rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] p-3 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                        >
                            <span
                                class="block text-[11px] font-medium text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                >Size</span
                            >
                            <span class="mt-1 block text-sm font-semibold">{{
                                sheet.size || '—'
                            }}</span>
                        </div>
                        <div
                            class="rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] p-3 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                        >
                            <span
                                class="block text-[11px] font-medium text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                >XP</span
                            >
                            <span class="mt-1 block text-sm font-semibold">{{
                                sheet.xp || 0
                            }}</span>
                        </div>
                    </div>

                    <!-- Edit Stats Mode: Inputs -->
                    <div
                        v-else
                        class="mt-4 grid grid-cols-2 gap-3.5 sm:grid-cols-4 lg:grid-cols-4"
                    >
                        <div>
                            <label
                                class="block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Species (2024)
                            </label>
                            <input
                                v-model="sheet.species"
                                type="text"
                                class="mt-1 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Class
                            </label>
                            <input
                                v-model="sheet.class"
                                type="text"
                                class="mt-1 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Subclass
                            </label>
                            <input
                                v-model="sheet.subclass"
                                type="text"
                                placeholder="e.g. Battle Master"
                                class="mt-1 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Background (2024)
                            </label>
                            <input
                                v-model="sheet.background"
                                type="text"
                                class="mt-1 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Origin Feat
                            </label>
                            <input
                                v-model="sheet.origin_feat"
                                type="text"
                                placeholder="e.g. Alert, Tough"
                                class="mt-1 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Level (1-20)
                            </label>
                            <input
                                v-model.number="sheet.level"
                                type="number"
                                min="1"
                                max="20"
                                class="mt-1 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm font-semibold focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Size
                            </label>
                            <input
                                v-model="sheet.size"
                                type="text"
                                placeholder="Small or Medium"
                                class="mt-1 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Experience Points (XP)
                            </label>
                            <input
                                v-model.number="sheet.xp"
                                type="number"
                                min="0"
                                class="mt-1 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                            />
                        </div>
                    </div>
                </section>

                <!-- Features & Proficiencies (Quick Edit Always Active) -->
                <section class="grid gap-5 sm:gap-6 lg:grid-cols-2">
                    <div
                        class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                    >
                        <h2 class="text-base font-semibold">
                            Class Features, Species Traits &amp; Feats
                        </h2>
                        <p
                            class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Record your class features, species traits, origin
                            feat benefits, and general feats.
                        </p>
                        <textarea
                            v-model="sheet.features_traits"
                            :disabled="!props.canEdit"
                            rows="8"
                            placeholder="Second Wind, Action Surge, Darkvision, Origin Feat details..."
                            class="mt-3 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] p-3 text-sm focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:focus:border-[#EDEDEC]"
                        />
                    </div>

                    <div
                        class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                    >
                        <h2 class="text-base font-semibold">
                            Proficiencies, Languages &amp; Other Traits
                        </h2>
                        <p
                            class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Armor training, weapon proficiencies, tool
                            proficiencies, and languages known.
                        </p>
                        <textarea
                            v-model="sheet.proficiencies"
                            :disabled="!props.canEdit"
                            rows="8"
                            placeholder="Light/Medium/Heavy Armor, Shields, Simple & Martial Weapons, Common, Elvish, Thieves' Tools..."
                            class="mt-3 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] p-3 text-sm focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:focus:border-[#EDEDEC]"
                        />
                    </div>
                </section>
            </div>

            <!-- TAB 3: EQUIPMENTS (Weapons, 2024 Weapon Masteries, Armor & Shield) -->
            <div
                v-show="activeTab === 'equipments'"
                class="space-y-5 sm:space-y-6"
            >
                <!-- Armor & Shield (Armor Text is Quick Editable, AC Base in Edit Stats Mode) -->
                <section
                    class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <div
                        class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
                    >
                        <div>
                            <div class="flex flex-wrap items-center gap-2.5">
                                <h2 class="text-base font-semibold">
                                    Armor &amp; Shield
                                </h2>
                                <button
                                    v-if="props.canEdit"
                                    type="button"
                                    class="inline-flex cursor-pointer items-center gap-1 rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1 text-xs font-medium hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:hover:border-[#EDEDEC]"
                                    @click="openCompendium('armor')"
                                >
                                    <span>+ Add Armor from Compendium</span>
                                </button>
                            </div>
                            <p
                                class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Note your equipped armor, shield, magical
                                defensive items, and stealth properties.
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <span
                                class="text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                            >
                                Armor Class (AC)
                            </span>
                            <span
                                v-if="!canModify"
                                class="text-xl font-bold sm:text-2xl"
                            >
                                {{ sheet.armor_class }}
                            </span>
                            <input
                                v-else
                                v-model.number="sheet.armor_class"
                                type="number"
                                class="w-20 rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] py-1.5 text-center text-lg font-bold dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                            />
                        </div>
                    </div>

                    <textarea
                        v-model="sheet.armor_equipped"
                        :disabled="!props.canEdit"
                        rows="4"
                        placeholder="e.g. Chain Mail (AC 16, Str 13, Stealth Disadvantage), Shield (+2 AC)..."
                        class="mt-4 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] p-3 text-sm focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:focus:border-[#EDEDEC]"
                    />
                </section>

                <!-- Weapons & 2024 Weapon Masteries (Quick Edit Always Active) -->
                <section
                    class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-2"
                    >
                        <div>
                            <h2 class="text-base font-semibold">
                                Weapons &amp; 2024 Weapon Masteries
                            </h2>
                            <p
                                class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Toggle active 2024 Weapon Mastery properties and
                                record your equipped weapons and attack rolls.
                            </p>
                        </div>

                        <button
                            v-if="props.canEdit"
                            type="button"
                            class="inline-flex cursor-pointer items-center gap-1 rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1 text-xs font-medium hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:hover:border-[#EDEDEC]"
                            @click="openCompendium('weapon')"
                        >
                            <span>+ Add Weapon from Compendium</span>
                        </button>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-1.5">
                        <button
                            v-for="mastery in masteryProperties"
                            :key="mastery"
                            type="button"
                            :disabled="!props.canEdit"
                            class="cursor-pointer rounded-full border px-3 py-1.5 text-xs font-medium transition-colors disabled:cursor-default sm:py-1"
                            :class="
                                sheet.weapon_masteries.includes(mastery)
                                    ? 'border-[#1b1b18] bg-[#1b1b18] text-white dark:border-[#EDEDEC] dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                    : 'border-[#e3e3e0] text-[#706f6c] hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:text-[#A1A09A] dark:hover:border-[#EDEDEC]'
                            "
                            @click="toggleWeaponMastery(mastery)"
                        >
                            {{ mastery }}
                        </button>
                    </div>

                    <textarea
                        v-model="sheet.weapons_attacks"
                        :disabled="!props.canEdit"
                        rows="6"
                        placeholder="e.g. Longsword +5 to hit, 1d8+3 slashing (Sap mastery)&#10;Heavy Crossbow +4 to hit, 1d10+2 piercing (Push mastery)..."
                        class="mt-4 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] p-3 text-sm focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:focus:border-[#EDEDEC]"
                    />
                </section>
            </div>

            <!-- TAB 4: SPELL (Spellcasting Ability, Save DC, Spell Attack, Spells) -->
            <div v-show="activeTab === 'spells'" class="space-y-5 sm:space-y-6">
                <section
                    class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <div
                        class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center sm:gap-4"
                    >
                        <div>
                            <h2 class="text-base font-semibold">
                                Spellcasting (2024)
                            </h2>
                            <p
                                class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Auto-calculated Spell Save DC, Spell Attack
                                Bonus, and spell tracking.
                            </p>
                        </div>

                        <!-- Ability selector: Readout in Play Mode, Select in Edit Stats Mode -->
                        <div v-if="!canModify">
                            <span
                                class="inline-block rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-1.5 text-xs font-semibold uppercase dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                            >
                                Ability:
                                {{
                                    sheet.spellcasting_ability === 'none'
                                        ? 'None'
                                        : sheet.spellcasting_ability
                                }}
                            </span>
                        </div>
                        <select
                            v-else
                            v-model="sheet.spellcasting_ability"
                            class="w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-xs font-medium sm:w-auto sm:py-1.5 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                        >
                            <option value="none">No Spellcasting</option>
                            <option value="int">Intelligence (INT)</option>
                            <option value="wis">Wisdom (WIS)</option>
                            <option value="cha">Charisma (CHA)</option>
                        </select>
                    </div>

                    <div
                        class="mt-4 grid grid-cols-1 gap-3 text-center sm:grid-cols-3"
                    >
                        <div
                            class="rounded-sm border border-[#e3e3e0] p-3.5 dark:border-[#3E3E3A]"
                        >
                            <span
                                class="block text-[11px] text-[#706f6c] uppercase dark:text-[#A1A09A]"
                            >
                                Spellcasting Ability
                            </span>
                            <span
                                class="mt-1 block text-lg font-bold uppercase"
                            >
                                {{
                                    sheet.spellcasting_ability === 'none'
                                        ? 'None'
                                        : sheet.spellcasting_ability
                                }}
                            </span>
                        </div>

                        <div
                            class="rounded-sm border border-[#e3e3e0] p-3.5 dark:border-[#3E3E3A]"
                        >
                            <span
                                class="block text-[11px] text-[#706f6c] uppercase dark:text-[#A1A09A]"
                            >
                                Spell Save DC
                            </span>
                            <span class="mt-1 block text-xl font-bold">
                                {{ spellSaveDc !== null ? spellSaveDc : '—' }}
                            </span>
                        </div>

                        <div
                            class="rounded-sm border border-[#e3e3e0] p-3.5 dark:border-[#3E3E3A]"
                        >
                            <span
                                class="block text-[11px] text-[#706f6c] uppercase dark:text-[#A1A09A]"
                            >
                                Spell Attack Bonus
                            </span>
                            <span class="mt-1 block text-xl font-bold">
                                {{
                                    spellAttackBonus !== null
                                        ? formatSigned(spellAttackBonus)
                                        : '—'
                                }}
                            </span>
                        </div>
                    </div>

                    <!-- Spells Textbox (Quick Edit Always Active) -->
                    <div class="mt-5">
                        <div
                            class="flex flex-wrap items-center justify-between gap-2"
                        >
                            <div>
                                <label class="block text-sm font-semibold">
                                    Cantrips, Prepared Spells &amp; Spell Slots
                                </label>
                                <p
                                    class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    Track your cantrips, prepared spells, ritual
                                    spells, and remaining spell slots.
                                </p>
                            </div>

                            <button
                                v-if="props.canEdit"
                                type="button"
                                class="inline-flex cursor-pointer items-center gap-1 rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1 text-xs font-medium hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:hover:border-[#EDEDEC]"
                                @click="openCompendium('spell')"
                            >
                                <span>+ Add Spell from Compendium</span>
                            </button>
                        </div>
                        <textarea
                            v-model="sheet.spells"
                            :disabled="!props.canEdit"
                            rows="8"
                            placeholder="Cantrips: Fire Bolt, Mage Hand&#10;1st Level (4/4 slots): Shield, Magic Missile, Detect Magic..."
                            class="mt-3 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] p-3 text-sm focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:focus:border-[#EDEDEC]"
                        />
                    </div>
                </section>
            </div>

            <!-- TAB 5: INVENTORY (Coins / Money & Adventuring Gear) -->
            <div
                v-show="activeTab === 'inventory'"
                class="space-y-5 sm:space-y-6"
            >
                <!-- Money & Coins (Quick Edit Always Active) -->
                <section
                    class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <h2 class="text-base font-semibold">Money &amp; Coins</h2>
                    <p
                        class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                    >
                        Track your currency across Copper, Silver, Electrum,
                        Gold, and Platinum pieces.
                    </p>

                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-5">
                        <div
                            v-for="coin in coinDefinitions"
                            :key="coin.key"
                            class="rounded-sm border border-[#e3e3e0] p-3 text-center dark:border-[#3E3E3A]"
                        >
                            <label
                                class="block text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                            >
                                {{ coin.label }} ({{ coin.short }})
                            </label>
                            <input
                                v-model.number="sheet.coins[coin.key]"
                                :disabled="!props.canEdit"
                                type="number"
                                min="0"
                                class="mt-2 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] py-1.5 text-center text-base font-bold dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                            />
                        </div>
                    </div>
                </section>

                <!-- Inventory & Gear (Quick Edit Always Active) -->
                <section
                    class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-2"
                    >
                        <div>
                            <h2 class="text-base font-semibold">
                                Inventory, Magic Items &amp; Adventuring Gear
                            </h2>
                            <p
                                class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Backpack contents, potions, magic items, attuned
                                items, and treasure.
                            </p>
                        </div>

                        <button
                            v-if="props.canEdit"
                            type="button"
                            class="inline-flex cursor-pointer items-center gap-1 rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1 text-xs font-medium hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:hover:border-[#EDEDEC]"
                            @click="openCompendium('gear')"
                        >
                            <span>+ Add Gear from Compendium</span>
                        </button>
                    </div>
                    <textarea
                        v-model="sheet.equipment"
                        :disabled="!props.canEdit"
                        rows="8"
                        placeholder="Explorer's Pack, 50 ft Hempen Rope, 2x Potion of Healing, Bag of Holding..."
                        class="mt-3 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] p-3 text-sm focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:focus:border-[#EDEDEC]"
                    />
                </section>
            </div>

            <!-- TAB 6: JOURNAL (Notes, Backstory, Avatar URL & Delete Character) -->
            <div
                v-show="activeTab === 'journal'"
                class="space-y-5 sm:space-y-6"
            >
                <!-- Journal & Notes (Quick Edit Always Active) -->
                <section
                    class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <h2 class="text-base font-semibold">
                        Journal, Backstory &amp; Session Notes
                    </h2>
                    <p
                        class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                    >
                        Backstory, allies, organizations, quests, and session
                        logs.
                    </p>
                    <textarea
                        v-model="sheet.notes"
                        :disabled="!props.canEdit"
                        rows="10"
                        placeholder="Backstory, personality traits, allies, quest objectives, session notes..."
                        class="mt-3 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] p-3 text-sm focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:focus:border-[#EDEDEC]"
                    />
                </section>

                <!-- Character Portrait URL -->
                <section
                    class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <h2 class="text-base font-semibold">Character Portrait</h2>
                    <p
                        class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                    >
                        Character portrait image URL.
                    </p>
                    <div class="mt-3">
                        <div v-if="!canModify">
                            <img
                                v-if="avatarUrl"
                                :src="avatarUrl"
                                :alt="name"
                                class="h-24 w-24 rounded-lg border border-[#e3e3e0] object-cover dark:border-[#3E3E3A]"
                            />
                            <span
                                v-else
                                class="text-xs text-[#706f6c] italic dark:text-[#A1A09A]"
                            >
                                No portrait URL set. Switch to "Edit Stats" to
                                set a portrait URL.
                            </span>
                        </div>
                        <input
                            v-else
                            v-model="avatarUrl"
                            type="url"
                            placeholder="https://example.com/avatar.png"
                            class="block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:focus:border-[#EDEDEC]"
                        />
                    </div>
                </section>

                <!-- Delete Character (Owner Only, available in Edit Stats mode to prevent accidents) -->
                <section
                    v-if="props.character.user_id === user?.id && isEditing"
                    class="flex flex-col items-start justify-between gap-3 rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(245,48,3,0.25)] sm:flex-row sm:items-center dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_rgba(255,68,51,0.3)]"
                >
                    <div>
                        <h3
                            class="text-sm font-semibold text-[#f53003] dark:text-[#FF4433]"
                        >
                            Delete Character
                        </h3>
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                            Permanently delete this character sheet.
                        </p>
                    </div>

                    <button
                        v-if="!confirmingDelete"
                        type="button"
                        class="w-full cursor-pointer rounded-sm border border-[#f53003] px-3.5 py-2 text-center text-xs font-medium text-[#f53003] transition-colors hover:bg-[#f53003] hover:text-white sm:w-auto sm:py-1.5 dark:border-[#FF4433] dark:text-[#FF4433] dark:hover:bg-[#FF4433] dark:hover:text-white"
                        @click="confirmingDelete = true"
                    >
                        Delete
                    </button>

                    <div
                        v-else
                        class="flex w-full items-center gap-2 sm:w-auto"
                    >
                        <Form
                            v-bind="
                                CharacterController.destroy.form(
                                    props.character.id,
                                )
                            "
                            v-slot="{ processing }"
                            class="flex-1 sm:flex-initial"
                        >
                            <button
                                type="submit"
                                :disabled="processing"
                                class="w-full cursor-pointer rounded-sm bg-[#f53003] px-3.5 py-2 text-xs font-medium text-white hover:opacity-90 disabled:opacity-50 sm:w-auto sm:py-1.5 dark:bg-[#FF4433]"
                            >
                                Confirm delete
                            </button>
                        </Form>
                        <button
                            type="button"
                            class="cursor-pointer rounded-sm border border-[#e3e3e0] px-3 py-2 text-xs sm:py-1.5 dark:border-[#3E3E3A]"
                            @click="confirmingDelete = false"
                        >
                            Cancel
                        </button>
                    </div>
                </section>
            </div>

            <!-- Compendium Search & Picker Modal -->
            <div
                v-if="compendiumModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4"
                role="dialog"
                aria-modal="true"
            >
                <!-- Backdrop -->
                <div
                    class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"
                    @click="closeCompendium"
                />

                <!-- Modal Window -->
                <div
                    class="relative flex max-h-[85vh] w-full max-w-2xl flex-col rounded-lg border border-[#e3e3e0] bg-white shadow-2xl dark:border-[#3E3E3A] dark:bg-[#161615]"
                >
                    <!-- Modal Header -->
                    <div
                        class="flex items-center justify-between border-b border-[#e3e3e0] px-4 py-3 sm:px-6 dark:border-[#3E3E3A]"
                    >
                        <div>
                            <h3 class="text-base font-bold sm:text-lg">
                                2024 SRD Compendium
                            </h3>
                            <p
                                class="text-xs text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Browse and add equipment, spells, or gear with
                                auto-calculated bonuses.
                            </p>
                        </div>
                        <button
                            type="button"
                            class="cursor-pointer rounded-xs p-1 text-[#706f6c] hover:bg-[#1b1b18]/5 hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:bg-[#EDEDEC]/10 dark:hover:text-[#EDEDEC]"
                            @click="closeCompendium"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Flash Notice Banner inside Modal -->
                    <div
                        v-if="compendiumNotice"
                        class="border-b border-emerald-500/20 bg-emerald-500/10 px-4 py-2 text-xs font-semibold text-emerald-700 dark:text-emerald-300"
                    >
                        ✓ {{ compendiumNotice }}
                    </div>

                    <!-- Search & Filter Controls -->
                    <div
                        class="space-y-3 border-b border-[#e3e3e0] p-4 dark:border-[#3E3E3A]"
                    >
                        <input
                            v-model="compendiumSearch"
                            type="text"
                            placeholder="Search by name, property, school, or keyword..."
                            class="block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3.5 py-2 text-sm focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                        />

                        <!-- Type Filters -->
                        <div class="flex flex-wrap gap-1.5 text-xs">
                            <button
                                v-for="t in [
                                    { key: 'all', label: 'All' },
                                    { key: 'weapon', label: 'Weapons' },
                                    { key: 'armor', label: 'Armor' },
                                    { key: 'spell', label: 'Spells' },
                                    { key: 'gear', label: 'Gear' },
                                ]"
                                :key="t.key"
                                type="button"
                                class="cursor-pointer rounded-full px-3 py-1 font-medium transition-colors"
                                :class="
                                    compendiumFilterType === t.key
                                        ? 'bg-[#1b1b18] text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                        : 'border border-[#e3e3e0] text-[#706f6c] hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:text-[#A1A09A]'
                                "
                                @click="compendiumFilterType = t.key as any"
                            >
                                {{ t.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Results List (Scrollable) -->
                    <div
                        class="flex-1 divide-y divide-[#e3e3e0] overflow-y-auto p-4 sm:p-6 dark:divide-[#3E3E3A]"
                    >
                        <div
                            v-if="filteredCompendiumEntries.length === 0"
                            class="py-10 text-center text-xs text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            No compendium entries found matching "{{
                                compendiumSearch
                            }}".
                        </div>

                        <article
                            v-for="entry in filteredCompendiumEntries"
                            :key="entry.id"
                            class="flex flex-col gap-3 py-3 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="text-sm font-bold">
                                        {{ entry.name }}
                                    </h4>
                                    <span
                                        class="rounded-xs border border-[#e3e3e0] px-1.5 py-0.5 text-[10px] font-semibold tracking-wider text-[#706f6c] uppercase dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                                    >
                                        {{ entry.type }}
                                    </span>
                                    <span
                                        v-if="entry.data?.mastery"
                                        class="rounded-xs bg-[#f53003]/10 px-1.5 py-0.5 text-[10px] font-semibold text-[#f53003] dark:bg-[#FF4433]/15 dark:text-[#FF4433]"
                                    >
                                        Mastery: {{ entry.data.mastery }}
                                    </span>
                                </div>

                                <p
                                    v-if="entry.summary"
                                    class="text-xs text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    {{ entry.summary }}
                                </p>

                                <p
                                    v-if="entry.description"
                                    class="line-clamp-2 text-xs text-[#1b1b18]/80 dark:text-[#EDEDEC]/80"
                                >
                                    {{ entry.description }}
                                </p>
                            </div>

                            <button
                                type="button"
                                class="shrink-0 cursor-pointer rounded-sm border border-black bg-[#1b1b18] px-3.5 py-1.5 text-xs font-medium text-white transition-colors hover:bg-black sm:w-auto dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A]"
                                @click="addCompendiumEntry(entry)"
                            >
                                + Add to Sheet
                            </button>
                        </article>
                    </div>

                    <!-- Modal Footer -->
                    <div
                        class="flex items-center justify-between border-t border-[#e3e3e0] px-4 py-3 sm:px-6 dark:border-[#3E3E3A]"
                    >
                        <span
                            class="text-xs text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            {{ filteredCompendiumEntries.length }} entries found
                        </span>
                        <button
                            type="button"
                            class="cursor-pointer rounded-sm border border-[#e3e3e0] px-3 py-1.5 text-xs font-medium transition-colors hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#EDEDEC]"
                            @click="closeCompendium"
                        >
                            Done
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
