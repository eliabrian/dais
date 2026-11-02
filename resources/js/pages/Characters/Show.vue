<script setup lang="ts">
import { Form, Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';
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
    SheetArmorItem,
    SheetFeatItem,
    SheetFeatureItem,
    SheetInventoryItem,
    SheetLanguageItem,
    SheetSpellItem,
    SheetTraitItem,
    SheetWeaponItem,
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
    armors_list: Array.isArray(sd.armors_list)
        ? ([...sd.armors_list] as SheetArmorItem[])
        : ([] as SheetArmorItem[]),
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
    weapons_list: Array.isArray(sd.weapons_list)
        ? ([...sd.weapons_list] as SheetWeaponItem[])
        : ([] as SheetWeaponItem[]),
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
    inventory_list: Array.isArray(sd.inventory_list)
        ? ([...sd.inventory_list] as SheetInventoryItem[])
        : ([] as SheetInventoryItem[]),
    features_list: Array.isArray(sd.features_list)
        ? ([...sd.features_list] as SheetFeatureItem[])
        : ([] as SheetFeatureItem[]),
    traits_list: Array.isArray(sd.traits_list)
        ? ([...sd.traits_list] as SheetTraitItem[])
        : ([] as SheetTraitItem[]),
    feats_list: Array.isArray(sd.feats_list)
        ? ([...sd.feats_list] as SheetFeatItem[])
        : ([] as SheetFeatItem[]),
    languages_list: Array.isArray(sd.languages_list)
        ? ([...sd.languages_list] as SheetLanguageItem[])
        : ([] as SheetLanguageItem[]),
    equipment: sd.equipment ?? '',
    spellcasting_ability: (sd.spellcasting_ability ?? 'none') as
        | 'none'
        | 'int'
        | 'wis'
        | 'cha',
    spells_list: Array.isArray(sd.spells_list)
        ? ([...sd.spells_list] as SheetSpellItem[])
        : ([] as SheetSpellItem[]),
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

function recalculateAc(): void {
    if (!sheet.armors_list || sheet.armors_list.length === 0) {
        return;
    }
    const dexScore = Number(sheet.abilities.dex || 10);
    const dexMod = Math.floor((dexScore - 10) / 2);

    const equippedBody = sheet.armors_list.find(
        (a) => a.equipped && a.category?.toLowerCase() !== 'shield',
    );
    const equippedShields = sheet.armors_list.filter(
        (a) => a.equipped && a.category?.toLowerCase() === 'shield',
    );
    const shieldBonus = equippedShields.reduce(
        (sum, s) => sum + (s.ac_bonus ?? 2),
        0,
    );

    if (equippedBody) {
        const cat = equippedBody.category?.toLowerCase() || '';
        const base = Number(equippedBody.ac_base ?? 10);
        let calculated = base;
        if (equippedBody.dex_bonus === 'full' || cat.includes('light')) {
            calculated += dexMod;
        } else if (
            equippedBody.dex_bonus === 'max_2' ||
            cat.includes('medium')
        ) {
            calculated += Math.min(2, Math.max(0, dexMod));
        }
        sheet.armor_class = calculated + shieldBonus;
    } else {
        sheet.armor_class = 10 + dexMod + shieldBonus;
    }
}

watch(
    () => sheet.abilities.dex,
    () => {
        if (sheet.armors_list.length > 0) {
            recalculateAc();
        }
    },
);

function getWeaponAttackBonus(item: SheetWeaponItem): string {
    const strMod = abilityMod('str');
    const dexMod = abilityMod('dex');
    let chosenMod = strMod;
    if (item.ability === 'dex') {
        chosenMod = dexMod;
    } else if (
        item.ability === 'dex_or_str' ||
        item.properties?.some((p) => p.toLowerCase().includes('finesse'))
    ) {
        chosenMod = Math.max(strMod, dexMod);
    }
    const totalBonus =
        chosenMod + proficiencyBonus.value - exhaustionPenalty.value;
    return formatSigned(totalBonus);
}

function getWeaponDamageDisplay(item: SheetWeaponItem): string {
    const strMod = abilityMod('str');
    const dexMod = abilityMod('dex');
    let chosenMod = strMod;
    if (item.ability === 'dex') {
        chosenMod = dexMod;
    } else if (
        item.ability === 'dex_or_str' ||
        item.properties?.some((p) => p.toLowerCase().includes('finesse'))
    ) {
        chosenMod = Math.max(strMod, dexMod);
    }
    const dmgSign =
        chosenMod >= 0 ? `+ ${chosenMod}` : `- ${Math.abs(chosenMod)}`;
    const baseDamage =
        `${item.damage || '1d6'} ${dmgSign} ${item.damage_type || ''}`.trim();

    const versatileProp = item.properties?.find((p) =>
        p.toLowerCase().includes('versatile'),
    );
    if (versatileProp) {
        const match = versatileProp.match(/\((.*?)\)/);
        const vDie = match ? match[1] : '';
        if (vDie) {
            return `${baseDamage} (Two-Handed: ${vDie} ${dmgSign})`;
        }
    }
    return baseDamage;
}

const groupedSpells = computed(() => {
    const groups: { level: number; label: string; spells: SheetSpellItem[] }[] =
        [];
    const levels = Array.from(
        new Set((sheet.spells_list || []).map((s) => s.level)),
    ).sort((a, b) => a - b);
    for (const lvl of levels) {
        groups.push({
            level: lvl,
            label: lvl === 0 ? 'Cantrips' : `Level ${lvl} Spells`,
            spells: (sheet.spells_list || []).filter((s) => s.level === lvl),
        });
    }
    return groups;
});

const expandedSpellIds = ref<string[]>([]);
function toggleSpellExpand(id: string): void {
    const idx = expandedSpellIds.value.indexOf(id);
    if (idx >= 0) {
        expandedSpellIds.value.splice(idx, 1);
    } else {
        expandedSpellIds.value.push(id);
    }
}

const expandedItemIds = ref<string[]>([]);
function toggleItemExpand(id: string): void {
    const idx = expandedItemIds.value.indexOf(id);
    if (idx >= 0) {
        expandedItemIds.value.splice(idx, 1);
    } else {
        expandedItemIds.value.push(id);
    }
}

function removeWeapon(id: string): void {
    if (!props.canEdit) {
        return;
    }
    sheet.weapons_list = sheet.weapons_list.filter((w) => w.id !== id);
}

function removeArmor(id: string): void {
    if (!props.canEdit) {
        return;
    }
    sheet.armors_list = sheet.armors_list.filter((a) => a.id !== id);
    recalculateAc();
}

function toggleArmorEquipped(item: SheetArmorItem): void {
    if (!props.canEdit) {
        return;
    }
    item.equipped = !item.equipped;
    recalculateAc();
}

function removeSpell(id: string): void {
    if (!props.canEdit) {
        return;
    }
    sheet.spells_list = sheet.spells_list.filter((s) => s.id !== id);
}

function toggleSpellPrepared(item: SheetSpellItem): void {
    if (!props.canEdit) {
        return;
    }
    item.prepared = !item.prepared;
}

function removeInventoryItem(id: string): void {
    if (!props.canEdit) {
        return;
    }
    sheet.inventory_list = sheet.inventory_list.filter((i) => i.id !== id);
}

function changeInventoryQuantity(
    item: SheetInventoryItem,
    delta: number,
): void {
    if (!props.canEdit) {
        return;
    }
    item.quantity = Math.max(1, (item.quantity || 1) + delta);
}

// Universal cross-browser ID generator (safe in non-secure HTTP contexts like dais.test or mobile IPs)
function generateId(): string {
    if (
        typeof window !== 'undefined' &&
        window.crypto &&
        typeof window.crypto.randomUUID === 'function'
    ) {
        return window.crypto.randomUUID();
    }
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
        const r = (Math.random() * 16) | 0;
        const v = c === 'x' ? r : (r & 0x3) | 0x8;
        return v.toString(16);
    });
}

// Custom quick-add forms
const showCustomWeaponForm = ref(false);
const customWeapon = reactive({
    name: '',
    category: 'Martial Melee',
    damage: '1d8',
    damage_type: 'slashing',
    ability: 'str',
    mastery: '',
    properties: '',
    range: 'Melee',
});
function addCustomWeapon(): void {
    if (!props.canEdit || !customWeapon.name.trim()) {
        return;
    }
    sheet.weapons_list.push({
        id: generateId(),
        name: customWeapon.name.trim(),
        category: customWeapon.category.trim() || 'Custom Weapon',
        damage: customWeapon.damage.trim() || '1d6',
        damage_type: customWeapon.damage_type.trim() || 'slashing',
        ability: customWeapon.ability,
        mastery: customWeapon.mastery.trim(),
        properties: customWeapon.properties
            ? customWeapon.properties
                  .split(',')
                  .map((p) => p.trim())
                  .filter(Boolean)
            : [],
        range: customWeapon.range.trim() || 'Melee',
    });
    if (
        customWeapon.mastery.trim() &&
        !sheet.weapon_masteries.includes(customWeapon.mastery.trim())
    ) {
        sheet.weapon_masteries.push(customWeapon.mastery.trim());
    }
    customWeapon.name = '';
    customWeapon.properties = '';
    showCustomWeaponForm.value = false;
    flashNotice('Added custom weapon!');
}

const showCustomArmorForm = ref(false);
const customArmor = reactive({
    name: '',
    category: 'Medium',
    ac_base: 14,
    ac_bonus: 0,
    dex_bonus: 'max_2' as 'full' | 'max_2' | 'none',
    stealth_disadv: false,
});
function addCustomArmor(): void {
    if (!props.canEdit || !customArmor.name.trim()) {
        return;
    }
    const isShield = customArmor.category.toLowerCase() === 'shield';
    sheet.armors_list.push({
        id: generateId(),
        name: customArmor.name.trim(),
        category: customArmor.category,
        ac_base: isShield ? undefined : Number(customArmor.ac_base || 10),
        ac_bonus: isShield ? Number(customArmor.ac_bonus || 2) : undefined,
        dex_bonus: isShield ? 'none' : customArmor.dex_bonus,
        stealth_disadv: customArmor.stealth_disadv,
        equipped: true,
    });
    recalculateAc();
    customArmor.name = '';
    showCustomArmorForm.value = false;
    flashNotice('Added custom armor!');
}

const showCustomSpellForm = ref(false);
const customSpell = reactive({
    name: '',
    level: 1,
    school: 'Evocation',
    casting_time: '1 Action',
    range: '60 ft',
    duration: 'Instantaneous',
    components: 'V, S',
    summary: '',
});
function addCustomSpell(): void {
    if (!props.canEdit || !customSpell.name.trim()) {
        return;
    }
    sheet.spells_list.push({
        id: generateId(),
        name: customSpell.name.trim(),
        level: Number(customSpell.level || 0),
        school: customSpell.school.trim(),
        casting_time: customSpell.casting_time.trim(),
        range: customSpell.range.trim(),
        duration: customSpell.duration.trim(),
        components: customSpell.components.trim(),
        summary: customSpell.summary.trim(),
        prepared: true,
    });
    customSpell.name = '';
    customSpell.summary = '';
    showCustomSpellForm.value = false;
    flashNotice('Added custom spell!');
}

const showCustomItemForm = ref(false);
const customItem = reactive({
    name: '',
    quantity: 1,
    weight: '1 lb',
    summary: '',
});
function addCustomItem(): void {
    if (!props.canEdit || !customItem.name.trim()) {
        return;
    }
    sheet.inventory_list.push({
        id: generateId(),
        name: customItem.name.trim(),
        quantity: Math.max(1, Number(customItem.quantity || 1)),
        weight: customItem.weight.trim(),
        summary: customItem.summary.trim(),
    });
    customItem.name = '';
    customItem.summary = '';
    showCustomItemForm.value = false;
    flashNotice('Added custom item!');
}

// Custom Feature
const showCustomFeatureForm = ref(false);
const customFeature = reactive({
    name: '',
    source: 'class' as 'class' | 'background' | 'subclass' | 'other',
    level: 1,
    summary: '',
    description: '',
});
function addCustomFeature(): void {
    if (!props.canEdit || !customFeature.name.trim()) {
        return;
    }
    sheet.features_list.push({
        id: generateId(),
        name: customFeature.name.trim(),
        source: customFeature.source,
        level: Number(customFeature.level || 1),
        summary: customFeature.summary.trim(),
        description:
            customFeature.description.trim() || customFeature.summary.trim(),
    });
    customFeature.name = '';
    customFeature.summary = '';
    customFeature.description = '';
    showCustomFeatureForm.value = false;
    flashNotice('Added custom feature!');
}
function removeFeature(id: string): void {
    if (!props.canEdit) {
        return;
    }
    const idx = sheet.features_list.findIndex((f) => f.id === id);
    if (idx !== -1) {
        sheet.features_list.splice(idx, 1);
    }
}

// Custom Trait
const showCustomTraitForm = ref(false);
const customTrait = reactive({
    name: '',
    species_name: '',
    summary: '',
    description: '',
});
function addCustomTrait(): void {
    if (!props.canEdit || !customTrait.name.trim()) {
        return;
    }
    sheet.traits_list.push({
        id: generateId(),
        name: customTrait.name.trim(),
        species_name: customTrait.species_name.trim(),
        summary: customTrait.summary.trim(),
        description:
            customTrait.description.trim() || customTrait.summary.trim(),
    });
    customTrait.name = '';
    customTrait.species_name = '';
    customTrait.summary = '';
    customTrait.description = '';
    showCustomTraitForm.value = false;
    flashNotice('Added custom trait!');
}
function removeTrait(id: string): void {
    if (!props.canEdit) {
        return;
    }
    const idx = sheet.traits_list.findIndex((t) => t.id === id);
    if (idx !== -1) {
        sheet.traits_list.splice(idx, 1);
    }
}

// Custom Feat
const showCustomFeatForm = ref(false);
const customFeat = reactive({
    name: '',
    category: 'Origin' as 'Origin' | 'General' | 'Fighting Style' | 'Epic Boon',
    summary: '',
    description: '',
});
function addCustomFeat(): void {
    if (!props.canEdit || !customFeat.name.trim()) {
        return;
    }
    sheet.feats_list.push({
        id: generateId(),
        name: customFeat.name.trim(),
        category: customFeat.category,
        summary: customFeat.summary.trim(),
        description: customFeat.description.trim() || customFeat.summary.trim(),
    });
    customFeat.name = '';
    customFeat.summary = '';
    customFeat.description = '';
    showCustomFeatForm.value = false;
    flashNotice('Added custom feat!');
}
function removeFeat(id: string): void {
    if (!props.canEdit) {
        return;
    }
    const idx = sheet.feats_list.findIndex((f) => f.id === id);
    if (idx !== -1) {
        sheet.feats_list.splice(idx, 1);
    }
}

// Custom Language
const showCustomLanguageForm = ref(false);
const customLanguage = reactive({
    name: '',
    script: '',
    type: 'Standard' as 'Standard' | 'Exotic' | 'Secret',
});
function addCustomLanguage(): void {
    if (!props.canEdit || !customLanguage.name.trim()) {
        return;
    }
    sheet.languages_list.push({
        id: generateId(),
        name: customLanguage.name.trim(),
        script: customLanguage.script.trim(),
        type: customLanguage.type,
    });
    customLanguage.name = '';
    customLanguage.script = '';
    showCustomLanguageForm.value = false;
    flashNotice('Added custom language!');
}
function removeLanguage(id: string): void {
    if (!props.canEdit) {
        return;
    }
    const idx = sheet.languages_list.findIndex((l) => l.id === id);
    if (idx !== -1) {
        sheet.languages_list.splice(idx, 1);
    }
}

function addCompendiumEntry(entry: CompendiumEntry): void {
    if (!props.canEdit) {
        return;
    }

    if (entry.type === 'weapon') {
        const newWeapon: SheetWeaponItem = {
            id: generateId(),
            compendium_id: entry.id,
            name: entry.name,
            category: (entry.data?.category as string) || 'Weapon',
            damage: (entry.data?.damage as string) || '1d6',
            damage_type: (entry.data?.damage_type as string) || 'slashing',
            ability: (entry.data?.ability as string) || 'str',
            mastery: (entry.data?.mastery as string) || '',
            properties: Array.isArray(entry.data?.properties)
                ? [...entry.data.properties]
                : [],
            range: (entry.data?.range as string) || 'Melee',
            weight: (entry.data?.weight as string) || '',
        };
        sheet.weapons_list.push(newWeapon);

        if (
            entry.data?.mastery &&
            !sheet.weapon_masteries.includes(entry.data.mastery)
        ) {
            sheet.weapon_masteries.push(entry.data.mastery);
        }

        flashNotice(`Added ${entry.name} to weapons list!`);
    } else if (entry.type === 'armor') {
        const isShield =
            entry.data?.category === 'Shield' ||
            entry.name.toLowerCase().includes('shield');
        const newArmor: SheetArmorItem = {
            id: generateId(),
            compendium_id: entry.id,
            name: entry.name,
            category:
                (entry.data?.category as string) ||
                (isShield ? 'Shield' : 'Armor'),
            ac_base:
                entry.data?.ac_base !== undefined
                    ? Number(entry.data.ac_base)
                    : undefined,
            ac_bonus:
                entry.data?.ac_bonus !== undefined
                    ? Number(entry.data.ac_bonus)
                    : isShield
                      ? 2
                      : undefined,
            dex_bonus:
                (entry.data?.dex_bonus as 'full' | 'max_2' | 'none') || 'none',
            stealth_disadv: Boolean(entry.data?.stealth_disadv),
            str_req:
                entry.data?.str_req !== undefined
                    ? Number(entry.data.str_req)
                    : null,
            weight: (entry.data?.weight as string) || '',
            equipped: true,
        };
        sheet.armors_list.push(newArmor);
        recalculateAc();

        flashNotice(
            `Added & equipped ${entry.name} (AC: ${sheet.armor_class})!`,
        );
    } else if (entry.type === 'spell') {
        const newSpell: SheetSpellItem = {
            id: generateId(),
            compendium_id: entry.id,
            name: entry.name,
            level: Number(entry.data?.level ?? 0),
            school: (entry.data?.school as string) || '',
            casting_time: (entry.data?.casting_time as string) || '1 Action',
            range: (entry.data?.range as string) || 'Self',
            components: (entry.data?.components as string) || 'V, S',
            duration: (entry.data?.duration as string) || 'Instantaneous',
            summary: entry.summary || '',
            description: entry.description || '',
            attack_type: (entry.data?.attack_type as string) || '',
            damage: (entry.data?.damage as string) || '',
            damage_type: (entry.data?.damage_type as string) || '',
            save_ability: (entry.data?.save_ability as string) || '',
            prepared: true,
        };
        sheet.spells_list.push(newSpell);
        flashNotice(`Added ${entry.name} to spells list!`);
    } else if (entry.type === 'gear') {
        const existing = sheet.inventory_list.find(
            (i) => i.name.toLowerCase() === entry.name.toLowerCase(),
        );
        if (existing) {
            existing.quantity = (existing.quantity || 1) + 1;
        } else {
            const newItem: SheetInventoryItem = {
                id: generateId(),
                compendium_id: entry.id,
                name: entry.name,
                quantity: 1,
                weight: (entry.data?.weight as string) || '',
                summary: entry.summary || '',
                description: entry.description || '',
            };
            sheet.inventory_list.push(newItem);
        }
        flashNotice(`Added ${entry.name} to inventory!`);
    } else if (entry.type === 'feature') {
        const newFeature: SheetFeatureItem = {
            id: generateId(),
            compendium_id: entry.id,
            name: entry.name,
            source: (entry.data?.source as string) || 'class',
            level:
                entry.data?.level !== undefined
                    ? Number(entry.data.level)
                    : undefined,
            summary: entry.summary || '',
            description: entry.description || '',
        };
        sheet.features_list.push(newFeature);
        flashNotice(`Added ${entry.name} to features!`);
    } else if (entry.type === 'trait') {
        const newTrait: SheetTraitItem = {
            id: generateId(),
            compendium_id: entry.id,
            name: entry.name,
            species_name:
                (entry.data?.species as string) ||
                (entry.data?.species_name as string) ||
                '',
            summary: entry.summary || '',
            description: entry.description || '',
        };
        sheet.traits_list.push(newTrait);
        flashNotice(`Added ${entry.name} to species traits!`);
    } else if (entry.type === 'feat') {
        const newFeat: SheetFeatItem = {
            id: generateId(),
            compendium_id: entry.id,
            name: entry.name,
            category: (entry.data?.category as string) || 'Origin',
            summary: entry.summary || '',
            description: entry.description || '',
            benefits: Array.isArray(entry.data?.benefits)
                ? [...entry.data.benefits]
                : [],
        };
        sheet.feats_list.push(newFeat);
        flashNotice(`Added ${entry.name} to feats!`);
    } else if (entry.type === 'language') {
        const newLang: SheetLanguageItem = {
            id: generateId(),
            compendium_id: entry.id,
            name: entry.name,
            script: (entry.data?.script as string) || '',
            type: (entry.data?.type as string) || 'Standard',
        };
        sheet.languages_list.push(newLang);
        flashNotice(`Added ${entry.name} to languages!`);
    } else if (entry.type === 'class') {
        sheet.class = entry.name;
        flashNotice(`Set class to ${entry.name}!`);
    } else if (entry.type === 'species') {
        sheet.species = entry.name;
        flashNotice(`Set species to ${entry.name}!`);
    } else if (entry.type === 'background') {
        sheet.background = entry.name;
        flashNotice(`Set background to ${entry.name}!`);
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
                weapons_list: [...sheet.weapons_list],
                armors_list: [...sheet.armors_list],
                spells_list: [...sheet.spells_list],
                inventory_list: [...sheet.inventory_list],
                features_list: [...sheet.features_list],
                traits_list: [...sheet.traits_list],
                feats_list: [...sheet.feats_list],
                languages_list: [...sheet.languages_list],
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

                <!-- 1. Class & Subclass Features -->
                <section
                    class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <div
                        class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
                    >
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-base font-semibold">
                                    Class &amp; Subclass Features
                                </h2>
                                <span
                                    class="rounded-full bg-[#1b1b18]/10 px-2 py-0.5 text-xs font-semibold dark:bg-[#EDEDEC]/10"
                                >
                                    {{ sheet.features_list.length }}
                                </span>
                                <button
                                    v-if="props.canEdit"
                                    type="button"
                                    class="inline-flex cursor-pointer items-center gap-1 rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1 text-xs font-medium hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:hover:border-[#EDEDEC]"
                                    @click="openCompendium('feature')"
                                >
                                    <span>+ Add Feature from Compendium</span>
                                </button>
                                <button
                                    v-if="props.canEdit"
                                    type="button"
                                    class="inline-flex cursor-pointer items-center gap-1 rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1 text-xs font-medium text-[#706f6c] hover:border-[#1b1b18] hover:text-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#A1A09A] dark:hover:border-[#EDEDEC] dark:hover:text-[#EDEDEC]"
                                    @click="
                                        showCustomFeatureForm =
                                            !showCustomFeatureForm
                                    "
                                >
                                    <span>{{
                                        showCustomFeatureForm
                                            ? 'Cancel'
                                            : '+ Custom Feature'
                                    }}</span>
                                </button>
                            </div>
                            <p
                                class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Core class features, subclass milestones, and
                                special abilities.
                            </p>
                        </div>
                    </div>

                    <!-- Custom Feature Form -->
                    <div
                        v-if="showCustomFeatureForm"
                        class="mt-4 rounded-md border border-[#e3e3e0] bg-[#FDFDFC] p-4 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                    >
                        <h4
                            class="text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Add Custom Feature
                        </h4>
                        <div class="mt-3 grid gap-3 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium">
                                    Feature Name
                                </label>
                                <input
                                    v-model="customFeature.name"
                                    type="text"
                                    placeholder="e.g. Action Surge"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#161615]"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium"
                                    >Source</label
                                >
                                <select
                                    v-model="customFeature.source"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#161615]"
                                >
                                    <option value="class">Class</option>
                                    <option value="subclass">Subclass</option>
                                    <option value="background">
                                        Background
                                    </option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium">
                                    Level Requirement
                                </label>
                                <input
                                    v-model.number="customFeature.level"
                                    type="number"
                                    min="1"
                                    max="20"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#161615]"
                                />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium">
                                    Short Summary
                                </label>
                                <input
                                    v-model="customFeature.summary"
                                    type="text"
                                    placeholder="e.g. Take one additional action on your turn (1/Short Rest)"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#161615]"
                                />
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-xs font-medium">
                                    Description
                                </label>
                                <textarea
                                    v-model="customFeature.description"
                                    rows="2"
                                    placeholder="Full details of how this feature functions..."
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#161615]"
                                />
                            </div>
                        </div>
                        <div class="mt-3 flex justify-end gap-2">
                            <button
                                type="button"
                                class="rounded-xs px-2.5 py-1 text-xs text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A]"
                                @click="showCustomFeatureForm = false"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="rounded-xs bg-[#1b1b18] px-3 py-1 text-xs font-semibold text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]"
                                @click="addCustomFeature"
                            >
                                Save to List
                            </button>
                        </div>
                    </div>

                    <!-- Features List -->
                    <div
                        v-if="sheet.features_list.length > 0"
                        class="mt-4 grid gap-3 sm:grid-cols-2"
                    >
                        <div
                            v-for="feature in sheet.features_list"
                            :key="feature.id"
                            class="flex flex-col justify-between rounded-md border border-[#e3e3e0] bg-[#FDFDFC] p-3.5 transition-colors dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                        >
                            <div>
                                <div
                                    class="flex items-start justify-between gap-2"
                                >
                                    <div
                                        class="flex flex-wrap items-center gap-1.5"
                                    >
                                        <h4 class="text-sm font-semibold">
                                            {{ feature.name }}
                                        </h4>
                                        <span
                                            class="rounded-xs border border-[#e3e3e0] px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-[#706f6c] uppercase dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                                        >
                                            {{ feature.source || 'Class' }}
                                        </span>
                                        <span
                                            v-if="feature.level"
                                            class="rounded-xs bg-[#1b1b18]/5 px-1.5 py-0.5 text-[10px] font-medium text-[#706f6c] dark:bg-[#EDEDEC]/10 dark:text-[#A1A09A]"
                                        >
                                            Lvl {{ feature.level }}
                                        </span>
                                    </div>
                                    <button
                                        v-if="props.canEdit"
                                        type="button"
                                        title="Remove feature"
                                        class="cursor-pointer rounded-xs p-1 text-xs text-[#706f6c] transition-colors hover:text-red-600 dark:text-[#A1A09A] dark:hover:text-red-400"
                                        @click="removeFeature(feature.id)"
                                    >
                                        <span class="sr-only">Delete</span>
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
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            />
                                        </svg>
                                    </button>
                                </div>
                                <p
                                    v-if="feature.summary"
                                    class="mt-1.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    {{ feature.summary }}
                                </p>
                                <p
                                    v-if="
                                        feature.description &&
                                        feature.description !== feature.summary
                                    "
                                    class="mt-1 line-clamp-3 text-xs text-[#1b1b18]/80 dark:text-[#EDEDEC]/80"
                                >
                                    {{ feature.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="mt-4 rounded-md border border-dashed border-[#e3e3e0] p-6 text-center text-xs text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                    >
                        No class features in list. Use '+ Add Feature from
                        Compendium' to choose from official 2024 class
                        abilities.
                    </div>
                </section>

                <!-- 2. Species Ancestral Traits -->
                <section
                    class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <div
                        class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
                    >
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-base font-semibold">
                                    Species Traits &amp; Lineage
                                </h2>
                                <span
                                    class="rounded-full bg-[#1b1b18]/10 px-2 py-0.5 text-xs font-semibold dark:bg-[#EDEDEC]/10"
                                >
                                    {{ sheet.traits_list.length }}
                                </span>
                                <button
                                    v-if="props.canEdit"
                                    type="button"
                                    class="inline-flex cursor-pointer items-center gap-1 rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1 text-xs font-medium hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:hover:border-[#EDEDEC]"
                                    @click="openCompendium('trait')"
                                >
                                    <span>+ Add Trait from Compendium</span>
                                </button>
                                <button
                                    v-if="props.canEdit"
                                    type="button"
                                    class="inline-flex cursor-pointer items-center gap-1 rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1 text-xs font-medium text-[#706f6c] hover:border-[#1b1b18] hover:text-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#A1A09A] dark:hover:border-[#EDEDEC] dark:hover:text-[#EDEDEC]"
                                    @click="
                                        showCustomTraitForm =
                                            !showCustomTraitForm
                                    "
                                >
                                    <span>{{
                                        showCustomTraitForm
                                            ? 'Cancel'
                                            : '+ Custom Trait'
                                    }}</span>
                                </button>
                            </div>
                            <p
                                class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Innate ancestral traits, darkvision,
                                resistances, and sensory capabilities.
                            </p>
                        </div>
                    </div>

                    <!-- Custom Trait Form -->
                    <div
                        v-if="showCustomTraitForm"
                        class="mt-4 rounded-md border border-[#e3e3e0] bg-[#FDFDFC] p-4 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                    >
                        <h4
                            class="text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Add Custom Species Trait
                        </h4>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium"
                                    >Trait Name</label
                                >
                                <input
                                    v-model="customTrait.name"
                                    type="text"
                                    placeholder="e.g. Darkvision"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#161615]"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium">
                                    Species / Lineage
                                </label>
                                <input
                                    v-model="customTrait.species_name"
                                    type="text"
                                    :placeholder="sheet.species || 'e.g. Elf'"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#161615]"
                                />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium"
                                    >Description</label
                                >
                                <textarea
                                    v-model="customTrait.description"
                                    rows="2"
                                    placeholder="Details of the trait..."
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#161615]"
                                />
                            </div>
                        </div>
                        <div class="mt-3 flex justify-end gap-2">
                            <button
                                type="button"
                                class="rounded-xs px-2.5 py-1 text-xs text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A]"
                                @click="showCustomTraitForm = false"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="rounded-xs bg-[#1b1b18] px-3 py-1 text-xs font-semibold text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]"
                                @click="addCustomTrait"
                            >
                                Save to List
                            </button>
                        </div>
                    </div>

                    <!-- Traits List -->
                    <div
                        v-if="sheet.traits_list.length > 0"
                        class="mt-4 grid gap-3 sm:grid-cols-2"
                    >
                        <div
                            v-for="trait in sheet.traits_list"
                            :key="trait.id"
                            class="flex flex-col justify-between rounded-md border border-[#e3e3e0] bg-[#FDFDFC] p-3.5 transition-colors dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                        >
                            <div>
                                <div
                                    class="flex items-start justify-between gap-2"
                                >
                                    <div
                                        class="flex flex-wrap items-center gap-1.5"
                                    >
                                        <h4 class="text-sm font-semibold">
                                            {{ trait.name }}
                                        </h4>
                                        <span
                                            v-if="trait.species_name"
                                            class="rounded-xs border border-[#e3e3e0] px-1.5 py-0.5 text-[10px] font-medium text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                                        >
                                            {{ trait.species_name }}
                                        </span>
                                    </div>
                                    <button
                                        v-if="props.canEdit"
                                        type="button"
                                        title="Remove trait"
                                        class="cursor-pointer rounded-xs p-1 text-xs text-[#706f6c] transition-colors hover:text-red-600 dark:text-[#A1A09A] dark:hover:text-red-400"
                                        @click="removeTrait(trait.id)"
                                    >
                                        <span class="sr-only">Delete</span>
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
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            />
                                        </svg>
                                    </button>
                                </div>
                                <p
                                    v-if="trait.summary"
                                    class="mt-1.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    {{ trait.summary }}
                                </p>
                                <p
                                    v-if="
                                        trait.description &&
                                        trait.description !== trait.summary
                                    "
                                    class="mt-1 line-clamp-3 text-xs text-[#1b1b18]/80 dark:text-[#EDEDEC]/80"
                                >
                                    {{ trait.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="mt-4 rounded-md border border-dashed border-[#e3e3e0] p-6 text-center text-xs text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                    >
                        No species traits in list. Use '+ Add Trait from
                        Compendium' to add ancestral features.
                    </div>
                </section>

                <!-- 3. Feats (Origin, General & Epic Boons) -->
                <section
                    class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <div
                        class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
                    >
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-base font-semibold">
                                    Feats (Origin, General &amp; Epic Boons)
                                </h2>
                                <span
                                    class="rounded-full bg-[#1b1b18]/10 px-2 py-0.5 text-xs font-semibold dark:bg-[#EDEDEC]/10"
                                >
                                    {{ sheet.feats_list.length }}
                                </span>
                                <button
                                    v-if="props.canEdit"
                                    type="button"
                                    class="inline-flex cursor-pointer items-center gap-1 rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1 text-xs font-medium hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:hover:border-[#EDEDEC]"
                                    @click="openCompendium('feat')"
                                >
                                    <span>+ Add Feat from Compendium</span>
                                </button>
                                <button
                                    v-if="props.canEdit"
                                    type="button"
                                    class="inline-flex cursor-pointer items-center gap-1 rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1 text-xs font-medium text-[#706f6c] hover:border-[#1b1b18] hover:text-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#A1A09A] dark:hover:border-[#EDEDEC] dark:hover:text-[#EDEDEC]"
                                    @click="
                                        showCustomFeatForm = !showCustomFeatForm
                                    "
                                >
                                    <span>{{
                                        showCustomFeatForm
                                            ? 'Cancel'
                                            : '+ Custom Feat'
                                    }}</span>
                                </button>
                            </div>
                            <p
                                class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                2024 Origin feats from your background and
                                General feats chosen at level milestones.
                            </p>
                        </div>
                    </div>

                    <!-- Custom Feat Form -->
                    <div
                        v-if="showCustomFeatForm"
                        class="mt-4 rounded-md border border-[#e3e3e0] bg-[#FDFDFC] p-4 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                    >
                        <h4
                            class="text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Add Custom Feat
                        </h4>
                        <div class="mt-3 grid gap-3 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium"
                                    >Feat Name</label
                                >
                                <input
                                    v-model="customFeat.name"
                                    type="text"
                                    placeholder="e.g. War Caster"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#161615]"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium"
                                    >Category</label
                                >
                                <select
                                    v-model="customFeat.category"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#161615]"
                                >
                                    <option value="Origin">Origin</option>
                                    <option value="General">General</option>
                                    <option value="Fighting Style">
                                        Fighting Style
                                    </option>
                                    <option value="Epic Boon">Epic Boon</option>
                                </select>
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-xs font-medium">
                                    Summary / Description
                                </label>
                                <textarea
                                    v-model="customFeat.description"
                                    rows="2"
                                    placeholder="Benefits and rules of this feat..."
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#161615]"
                                />
                            </div>
                        </div>
                        <div class="mt-3 flex justify-end gap-2">
                            <button
                                type="button"
                                class="rounded-xs px-2.5 py-1 text-xs text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A]"
                                @click="showCustomFeatForm = false"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="rounded-xs bg-[#1b1b18] px-3 py-1 text-xs font-semibold text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]"
                                @click="addCustomFeat"
                            >
                                Save to List
                            </button>
                        </div>
                    </div>

                    <!-- Feats List -->
                    <div
                        v-if="sheet.feats_list.length > 0"
                        class="mt-4 grid gap-3 sm:grid-cols-2"
                    >
                        <div
                            v-for="feat in sheet.feats_list"
                            :key="feat.id"
                            class="flex flex-col justify-between rounded-md border border-[#e3e3e0] bg-[#FDFDFC] p-3.5 transition-colors dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                        >
                            <div>
                                <div
                                    class="flex items-start justify-between gap-2"
                                >
                                    <div
                                        class="flex flex-wrap items-center gap-1.5"
                                    >
                                        <h4 class="text-sm font-semibold">
                                            {{ feat.name }}
                                        </h4>
                                        <span
                                            class="rounded-xs border border-[#e3e3e0] px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-[#706f6c] uppercase dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                                        >
                                            {{ feat.category || 'Origin' }}
                                        </span>
                                    </div>
                                    <button
                                        v-if="props.canEdit"
                                        type="button"
                                        title="Remove feat"
                                        class="cursor-pointer rounded-xs p-1 text-xs text-[#706f6c] transition-colors hover:text-red-600 dark:text-[#A1A09A] dark:hover:text-red-400"
                                        @click="removeFeat(feat.id)"
                                    >
                                        <span class="sr-only">Delete</span>
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
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            />
                                        </svg>
                                    </button>
                                </div>
                                <p
                                    v-if="feat.summary"
                                    class="mt-1.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    {{ feat.summary }}
                                </p>
                                <ul
                                    v-if="
                                        feat.benefits &&
                                        feat.benefits.length > 0
                                    "
                                    class="mt-2 space-y-1 text-xs text-[#1b1b18]/80 dark:text-[#EDEDEC]/80"
                                >
                                    <li
                                        v-for="(b, bi) in feat.benefits"
                                        :key="bi"
                                        class="flex items-start gap-1.5"
                                    >
                                        <span
                                            class="text-[#706f6c] dark:text-[#A1A09A]"
                                            >•</span
                                        >
                                        <span>{{ b }}</span>
                                    </li>
                                </ul>
                                <p
                                    v-else-if="
                                        feat.description &&
                                        feat.description !== feat.summary
                                    "
                                    class="mt-1 line-clamp-3 text-xs text-[#1b1b18]/80 dark:text-[#EDEDEC]/80"
                                >
                                    {{ feat.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="mt-4 rounded-md border border-dashed border-[#e3e3e0] p-6 text-center text-xs text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                    >
                        No feats in list. Use '+ Add Feat from Compendium' to
                        add Origin or General feats.
                    </div>
                </section>

                <!-- 4. Language Proficiencies -->
                <section
                    class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <div
                        class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
                    >
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-base font-semibold">
                                    Language Proficiencies
                                </h2>
                                <span
                                    class="rounded-full bg-[#1b1b18]/10 px-2 py-0.5 text-xs font-semibold dark:bg-[#EDEDEC]/10"
                                >
                                    {{ sheet.languages_list.length }}
                                </span>
                                <button
                                    v-if="props.canEdit"
                                    type="button"
                                    class="inline-flex cursor-pointer items-center gap-1 rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1 text-xs font-medium hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:hover:border-[#EDEDEC]"
                                    @click="openCompendium('language')"
                                >
                                    <span>+ Add Language from Compendium</span>
                                </button>
                                <button
                                    v-if="props.canEdit"
                                    type="button"
                                    class="inline-flex cursor-pointer items-center gap-1 rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1 text-xs font-medium text-[#706f6c] hover:border-[#1b1b18] hover:text-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#A1A09A] dark:hover:border-[#EDEDEC] dark:hover:text-[#EDEDEC]"
                                    @click="
                                        showCustomLanguageForm =
                                            !showCustomLanguageForm
                                    "
                                >
                                    <span>{{
                                        showCustomLanguageForm
                                            ? 'Cancel'
                                            : '+ Custom Language'
                                    }}</span>
                                </button>
                            </div>
                            <p
                                class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Standard, Rare, and Secret languages known by
                                your character.
                            </p>
                        </div>
                    </div>

                    <!-- Custom Language Form -->
                    <div
                        v-if="showCustomLanguageForm"
                        class="mt-4 rounded-md border border-[#e3e3e0] bg-[#FDFDFC] p-4 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                    >
                        <h4
                            class="text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Add Custom Language
                        </h4>
                        <div class="mt-3 grid gap-3 sm:grid-cols-3">
                            <div>
                                <label class="block text-xs font-medium">
                                    Language Name
                                </label>
                                <input
                                    v-model="customLanguage.name"
                                    type="text"
                                    placeholder="e.g. Sylvan"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#161615]"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium"
                                    >Script</label
                                >
                                <input
                                    v-model="customLanguage.script"
                                    type="text"
                                    placeholder="e.g. Elvish"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#161615]"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium"
                                    >Type</label
                                >
                                <select
                                    v-model="customLanguage.type"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#161615]"
                                >
                                    <option value="Standard">Standard</option>
                                    <option value="Exotic">Exotic</option>
                                    <option value="Secret">Secret</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3 flex justify-end gap-2">
                            <button
                                type="button"
                                class="rounded-xs px-2.5 py-1 text-xs text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A]"
                                @click="showCustomLanguageForm = false"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="rounded-xs bg-[#1b1b18] px-3 py-1 text-xs font-semibold text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]"
                                @click="addCustomLanguage"
                            >
                                Save to List
                            </button>
                        </div>
                    </div>

                    <!-- Languages List -->
                    <div
                        v-if="sheet.languages_list.length > 0"
                        class="mt-4 flex flex-wrap gap-2.5"
                    >
                        <div
                            v-for="lang in sheet.languages_list"
                            :key="lang.id"
                            class="inline-flex items-center gap-2 rounded-md border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-xs transition-colors dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                        >
                            <span class="font-semibold">{{ lang.name }}</span>
                            <span
                                v-if="lang.type"
                                class="rounded-xs border border-[#e3e3e0] px-1.5 py-0.5 text-[10px] text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                            >
                                {{ lang.type }}
                            </span>
                            <span
                                v-if="lang.script"
                                class="text-[10px] text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                (Script: {{ lang.script }})
                            </span>
                            <button
                                v-if="props.canEdit"
                                type="button"
                                title="Remove language"
                                class="cursor-pointer text-[#706f6c] hover:text-red-600 dark:text-[#A1A09A] dark:hover:text-red-400"
                                @click="removeLanguage(lang.id)"
                            >
                                ×
                            </button>
                        </div>
                    </div>
                    <div
                        v-else
                        class="mt-4 rounded-md border border-dashed border-[#e3e3e0] p-6 text-center text-xs text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                    >
                        No languages recorded. Use '+ Add Language from
                        Compendium' to add languages your character knows.
                    </div>
                </section>

                <!-- Freeform Notes & Legacy Text -->
                <section class="grid gap-5 sm:gap-6 lg:grid-cols-2">
                    <div
                        class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                    >
                        <h2 class="text-base font-semibold">
                            Custom Feature Notes &amp; Raw Text
                        </h2>
                        <p
                            class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Quick notes or custom abilities without adding
                            individual list entries.
                        </p>
                        <textarea
                            v-model="sheet.features_traits"
                            :disabled="!props.canEdit"
                            rows="6"
                            placeholder="Additional feature notes or raw text..."
                            class="mt-3 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] p-3 text-sm focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:focus:border-[#EDEDEC]"
                        />
                    </div>

                    <div
                        class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                    >
                        <h2 class="text-base font-semibold">
                            Proficiencies &amp; Tool Training Notes
                        </h2>
                        <p
                            class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Armor training, weapon proficiencies, and tool
                            proficiencies text summary.
                        </p>
                        <textarea
                            v-model="sheet.proficiencies"
                            :disabled="!props.canEdit"
                            rows="6"
                            placeholder="Armor Training, Weapon masteries, Tool proficiencies summary..."
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
                <!-- Armor & Shield Section -->
                <section
                    class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <div
                        class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
                    >
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
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
                                <button
                                    v-if="props.canEdit"
                                    type="button"
                                    class="inline-flex cursor-pointer items-center gap-1 rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1 text-xs font-medium text-[#706f6c] hover:border-[#1b1b18] hover:text-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#A1A09A] dark:hover:border-[#EDEDEC] dark:hover:text-[#EDEDEC]"
                                    @click="
                                        showCustomArmorForm =
                                            !showCustomArmorForm
                                    "
                                >
                                    <span>{{
                                        showCustomArmorForm
                                            ? 'Cancel'
                                            : '+ Custom Armor'
                                    }}</span>
                                </button>
                            </div>
                            <p
                                class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Equipped armor and shields auto-calculate your
                                Armor Class (AC).
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
                                class="rounded-sm bg-[#1b1b18]/5 px-3 py-1 text-xl font-bold sm:text-2xl dark:bg-[#EDEDEC]/5"
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

                    <!-- Custom Armor Form -->
                    <div
                        v-if="showCustomArmorForm && props.canEdit"
                        class="mt-4 rounded-md border border-[#e3e3e0] bg-[#1b1b18]/[0.02] p-4 dark:border-[#3E3E3A] dark:bg-[#EDEDEC]/[0.02]"
                    >
                        <h3
                            class="text-xs font-bold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Add Custom Armor or Shield
                        </h3>
                        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium"
                                    >Armor Name</label
                                >
                                <input
                                    v-model="customArmor.name"
                                    type="text"
                                    placeholder="e.g. Mithral Breastplate"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium"
                                    >Type</label
                                >
                                <select
                                    v-model="customArmor.category"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                >
                                    <option value="Light">Light Armor</option>
                                    <option value="Medium">Medium Armor</option>
                                    <option value="Heavy">Heavy Armor</option>
                                    <option value="Shield">Shield</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium">
                                    {{
                                        customArmor.category === 'Shield'
                                            ? 'AC Bonus'
                                            : 'Base AC'
                                    }}
                                </label>
                                <input
                                    v-if="customArmor.category === 'Shield'"
                                    v-model.number="customArmor.ac_bonus"
                                    type="number"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-center text-xs dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                />
                                <input
                                    v-else
                                    v-model.number="customArmor.ac_base"
                                    type="number"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-center text-xs dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                />
                            </div>
                        </div>

                        <div
                            class="mt-3 flex flex-wrap items-center justify-between gap-3"
                        >
                            <label
                                class="flex items-center gap-2 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                <input
                                    v-model="customArmor.stealth_disadv"
                                    type="checkbox"
                                    class="rounded text-[#1b1b18]"
                                />
                                <span>Disadvantage on Stealth checks</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    class="rounded-xs px-2.5 py-1 text-xs text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A]"
                                    @click="showCustomArmorForm = false"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="button"
                                    class="rounded-xs bg-[#1b1b18] px-3 py-1 text-xs font-semibold text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]"
                                    @click="addCustomArmor"
                                >
                                    Save to List
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Armor & Shield Items List -->
                    <div
                        v-if="sheet.armors_list.length > 0"
                        class="mt-4 space-y-2"
                    >
                        <div
                            v-for="armor in sheet.armors_list"
                            :key="armor.id"
                            class="flex flex-col justify-between gap-3 rounded-md border border-[#e3e3e0] bg-[#FDFDFC] p-3 transition-colors sm:flex-row sm:items-center dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                        >
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-semibold">
                                    {{ armor.name }}
                                </span>
                                <span
                                    class="rounded-full border border-[#e3e3e0] px-2 py-0.5 text-[11px] font-medium text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                                >
                                    {{ armor.category }}
                                </span>
                                <span
                                    class="rounded-full bg-[#1b1b18]/5 px-2 py-0.5 text-[11px] font-bold text-[#1b1b18] dark:bg-[#EDEDEC]/10 dark:text-[#EDEDEC]"
                                >
                                    {{
                                        armor.category?.toLowerCase() ===
                                        'shield'
                                            ? `+${armor.ac_bonus || 2} AC`
                                            : `AC ${armor.ac_base || 10}`
                                    }}
                                </span>
                                <span
                                    v-if="
                                        armor.category?.toLowerCase() !==
                                        'shield'
                                    "
                                    class="rounded-full border border-[#e3e3e0] px-2 py-0.5 text-[10px] text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                                >
                                    {{
                                        armor.dex_bonus === 'full'
                                            ? '+ Full Dex'
                                            : armor.dex_bonus === 'max_2'
                                              ? '+ Dex (max 2)'
                                              : 'No Dex'
                                    }}
                                </span>
                                <span
                                    v-if="armor.stealth_disadv"
                                    class="rounded-full border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-[10px] font-medium text-amber-700 dark:text-amber-400"
                                >
                                    Stealth Disadv.
                                </span>
                                <span
                                    v-if="armor.str_req"
                                    class="rounded-full border border-[#e3e3e0] px-2 py-0.5 text-[10px] text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                                >
                                    Str {{ armor.str_req }}+
                                </span>
                                <span
                                    v-if="armor.weight"
                                    class="text-[10px] text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    {{ armor.weight }}
                                </span>
                            </div>

                            <div
                                class="flex items-center gap-2 self-end sm:self-center"
                            >
                                <button
                                    type="button"
                                    :disabled="!props.canEdit"
                                    class="cursor-pointer rounded-xs border px-2.5 py-1 text-xs font-semibold transition-colors disabled:cursor-default"
                                    :class="
                                        armor.equipped
                                            ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-700 dark:text-emerald-400'
                                            : 'border-[#e3e3e0] text-[#706f6c] hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:text-[#A1A09A] dark:hover:border-[#EDEDEC]'
                                    "
                                    @click="toggleArmorEquipped(armor)"
                                >
                                    {{
                                        armor.equipped
                                            ? '✓ Equipped'
                                            : 'Unequipped'
                                    }}
                                </button>
                                <button
                                    v-if="props.canEdit"
                                    type="button"
                                    title="Remove armor"
                                    class="cursor-pointer rounded-xs p-1 text-xs text-[#706f6c] transition-colors hover:text-red-600 dark:text-[#A1A09A] dark:hover:text-red-400"
                                    @click="removeArmor(armor.id)"
                                >
                                    ✕
                                </button>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="mt-4 rounded-md border border-dashed border-[#e3e3e0] p-4 text-center text-xs text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                    >
                        No armor or shields in list. Use '+ Add Armor from
                        Compendium' to choose leather, plate, shield, etc.
                    </div>

                    <!-- Additional Armor Notes -->
                    <div
                        class="mt-4 border-t border-[#e3e3e0] pt-3 dark:border-[#3E3E3A]"
                    >
                        <label
                            class="block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Additional Armor &amp; Defensive Notes
                        </label>
                        <textarea
                            v-model="sheet.armor_equipped"
                            :disabled="!props.canEdit"
                            rows="2"
                            placeholder="Magic item attunements, Ring of Protection (+1 AC), unarmored defense notes..."
                            class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] p-2.5 text-xs focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:focus:border-[#EDEDEC]"
                        />
                    </div>
                </section>

                <!-- Weapons & 2024 Weapon Masteries Section -->
                <section
                    class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-2"
                    >
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-base font-semibold">
                                    Weapons &amp; 2024 Weapon Masteries
                                </h2>
                                <button
                                    v-if="props.canEdit"
                                    type="button"
                                    class="inline-flex cursor-pointer items-center gap-1 rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1 text-xs font-medium hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:hover:border-[#EDEDEC]"
                                    @click="openCompendium('weapon')"
                                >
                                    <span>+ Add Weapon from Compendium</span>
                                </button>
                                <button
                                    v-if="props.canEdit"
                                    type="button"
                                    class="inline-flex cursor-pointer items-center gap-1 rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1 text-xs font-medium text-[#706f6c] hover:border-[#1b1b18] hover:text-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#A1A09A] dark:hover:border-[#EDEDEC] dark:hover:text-[#EDEDEC]"
                                    @click="
                                        showCustomWeaponForm =
                                            !showCustomWeaponForm
                                    "
                                >
                                    <span>{{
                                        showCustomWeaponForm
                                            ? 'Cancel'
                                            : '+ Custom Weapon'
                                    }}</span>
                                </button>
                            </div>
                            <p
                                class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Auto-calculated Attack Rolls (+to hit) and
                                Damage formulas based on your ability modifiers
                                and proficiency.
                            </p>
                        </div>
                    </div>

                    <!-- 2024 Weapon Mastery Pills -->
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

                    <!-- Custom Weapon Form -->
                    <div
                        v-if="showCustomWeaponForm && props.canEdit"
                        class="mt-4 rounded-md border border-[#e3e3e0] bg-[#1b1b18]/[0.02] p-4 dark:border-[#3E3E3A] dark:bg-[#EDEDEC]/[0.02]"
                    >
                        <h3
                            class="text-xs font-bold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Add Custom Weapon
                        </h3>
                        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-4">
                            <div>
                                <label class="block text-xs font-medium"
                                    >Weapon Name</label
                                >
                                <input
                                    v-model="customWeapon.name"
                                    type="text"
                                    placeholder="e.g. Moon-Touched Rapier"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium"
                                    >Damage Dice &amp; Type</label
                                >
                                <div class="mt-1 flex gap-1">
                                    <input
                                        v-model="customWeapon.damage"
                                        type="text"
                                        placeholder="1d8"
                                        class="w-16 rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2 py-1.5 text-center text-xs dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                    />
                                    <input
                                        v-model="customWeapon.damage_type"
                                        type="text"
                                        placeholder="slashing"
                                        class="w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium"
                                    >Ability</label
                                >
                                <select
                                    v-model="customWeapon.ability"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                >
                                    <option value="str">Strength (STR)</option>
                                    <option value="dex">Dexterity (DEX)</option>
                                    <option value="dex_or_str">
                                        Finesse (Best of STR/DEX)
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium"
                                    >2024 Mastery</label
                                >
                                <select
                                    v-model="customWeapon.mastery"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                >
                                    <option value="">None</option>
                                    <option
                                        v-for="m in masteryProperties"
                                        :key="m"
                                        :value="m"
                                    >
                                        {{ m }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium"
                                    >Properties (comma separated)</label
                                >
                                <input
                                    v-model="customWeapon.properties"
                                    type="text"
                                    placeholder="e.g. Versatile (1d10), Light, Thrown (20/60)"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium"
                                    >Range</label
                                >
                                <input
                                    v-model="customWeapon.range"
                                    type="text"
                                    placeholder="Melee, 150/600 ft"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                />
                            </div>
                        </div>

                        <div class="mt-3 flex justify-end gap-2">
                            <button
                                type="button"
                                class="rounded-xs px-2.5 py-1 text-xs text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A]"
                                @click="showCustomWeaponForm = false"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="rounded-xs bg-[#1b1b18] px-3 py-1 text-xs font-semibold text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]"
                                @click="addCustomWeapon"
                            >
                                Save Weapon
                            </button>
                        </div>
                    </div>

                    <!-- Weapons List Cards -->
                    <div
                        v-if="sheet.weapons_list.length > 0"
                        class="mt-4 space-y-2.5"
                    >
                        <div
                            v-for="weapon in sheet.weapons_list"
                            :key="weapon.id"
                            class="rounded-md border border-[#e3e3e0] bg-[#FDFDFC] p-3.5 transition-colors sm:p-4 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                        >
                            <div
                                class="flex flex-wrap items-center justify-between gap-2"
                            >
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="text-sm font-bold sm:text-base"
                                    >
                                        {{ weapon.name }}
                                    </span>
                                    <span
                                        v-if="weapon.category"
                                        class="rounded-full border border-[#e3e3e0] px-2 py-0.5 text-[11px] font-medium text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                                    >
                                        {{ weapon.category }}
                                    </span>
                                    <span
                                        v-if="weapon.range"
                                        class="text-xs text-[#706f6c] dark:text-[#A1A09A]"
                                    >
                                        {{ weapon.range }}
                                    </span>
                                </div>

                                <button
                                    v-if="props.canEdit"
                                    type="button"
                                    title="Remove weapon"
                                    class="cursor-pointer rounded-xs p-1 text-xs text-[#706f6c] transition-colors hover:text-red-600 dark:text-[#A1A09A] dark:hover:text-red-400"
                                    @click="removeWeapon(weapon.id)"
                                >
                                    ✕
                                </button>
                            </div>

                            <!-- Attack & Damage Badges -->
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <div
                                    class="flex items-center gap-2 rounded-sm bg-[#1b1b18]/5 px-3 py-1.5 dark:bg-[#EDEDEC]/5"
                                >
                                    <span
                                        class="text-[10px] font-bold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                    >
                                        To Hit:
                                    </span>
                                    <span
                                        class="text-sm font-bold sm:text-base"
                                    >
                                        {{ getWeaponAttackBonus(weapon) }}
                                    </span>
                                </div>

                                <div
                                    class="flex items-center gap-2 rounded-sm bg-[#1b1b18]/5 px-3 py-1.5 dark:bg-[#EDEDEC]/5"
                                >
                                    <span
                                        class="text-[10px] font-bold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                    >
                                        Damage:
                                    </span>
                                    <span
                                        class="text-sm font-bold sm:text-base"
                                    >
                                        {{ getWeaponDamageDisplay(weapon) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Mastery & Properties Tags -->
                            <div
                                class="mt-3 flex flex-wrap items-center gap-1.5"
                            >
                                <button
                                    v-if="weapon.mastery"
                                    type="button"
                                    class="cursor-pointer rounded-full border px-2.5 py-0.5 text-[11px] font-semibold transition-colors"
                                    :class="
                                        sheet.weapon_masteries.includes(
                                            weapon.mastery,
                                        )
                                            ? 'border-[#1b1b18] bg-[#1b1b18] text-white dark:border-[#EDEDEC] dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                            : 'border-[#e3e3e0] text-[#706f6c] hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:text-[#A1A09A]'
                                    "
                                    :title="
                                        sheet.weapon_masteries.includes(
                                            weapon.mastery,
                                        )
                                            ? 'Mastery active on character'
                                            : 'Click to activate mastery'
                                    "
                                    @click="toggleWeaponMastery(weapon.mastery)"
                                >
                                    ★ Mastery: {{ weapon.mastery }}
                                </button>
                                <span
                                    v-for="prop in weapon.properties"
                                    :key="prop"
                                    class="rounded-full border border-[#e3e3e0] px-2 py-0.5 text-[10px] text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                                >
                                    {{ prop }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="mt-4 rounded-md border border-dashed border-[#e3e3e0] p-4 text-center text-xs text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                    >
                        No weapons in list. Use '+ Add Weapon from Compendium'
                        to add weapons with auto-calculated attack rolls.
                    </div>

                    <!-- Additional Attack Notes -->
                    <div
                        class="mt-4 border-t border-[#e3e3e0] pt-3 dark:border-[#3E3E3A]"
                    >
                        <label
                            class="block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Additional Attack &amp; Action Notes
                        </label>
                        <textarea
                            v-model="sheet.weapons_attacks"
                            :disabled="!props.canEdit"
                            rows="2"
                            placeholder="Unarmed Strike (1 + STR mod damage), Grappling notes, bonus action off-hand rules..."
                            class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] p-2.5 text-xs focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:focus:border-[#EDEDEC]"
                        />
                    </div>
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

                    <!-- Spells List Section -->
                    <div class="mt-5">
                        <div
                            class="flex flex-wrap items-center justify-between gap-2"
                        >
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <label class="block text-sm font-semibold">
                                        Known &amp; Prepared Spells
                                    </label>
                                    <button
                                        v-if="props.canEdit"
                                        type="button"
                                        class="inline-flex cursor-pointer items-center gap-1 rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1 text-xs font-medium hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:hover:border-[#EDEDEC]"
                                        @click="openCompendium('spell')"
                                    >
                                        <span>+ Add Spell from Compendium</span>
                                    </button>
                                    <button
                                        v-if="props.canEdit"
                                        type="button"
                                        class="inline-flex cursor-pointer items-center gap-1 rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1 text-xs font-medium text-[#706f6c] hover:border-[#1b1b18] hover:text-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#A1A09A] dark:hover:border-[#EDEDEC] dark:hover:text-[#EDEDEC]"
                                        @click="
                                            showCustomSpellForm =
                                                !showCustomSpellForm
                                        "
                                    >
                                        <span>{{
                                            showCustomSpellForm
                                                ? 'Cancel'
                                                : '+ Custom Spell'
                                        }}</span>
                                    </button>
                                </div>
                                <p
                                    class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    Organized by spell level with
                                    auto-calculated spell save DCs and attack
                                    bonuses.
                                </p>
                            </div>
                        </div>

                        <!-- Custom Spell Form -->
                        <div
                            v-if="showCustomSpellForm && props.canEdit"
                            class="mt-4 rounded-md border border-[#e3e3e0] bg-[#1b1b18]/[0.02] p-4 dark:border-[#3E3E3A] dark:bg-[#EDEDEC]/[0.02]"
                        >
                            <h3
                                class="text-xs font-bold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                            >
                                Add Custom Spell
                            </h3>
                            <div
                                class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-4"
                            >
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium"
                                        >Spell Name</label
                                    >
                                    <input
                                        v-model="customSpell.name"
                                        type="text"
                                        placeholder="e.g. Chaos Bolt"
                                        class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium"
                                        >Level</label
                                    >
                                    <select
                                        v-model.number="customSpell.level"
                                        class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                    >
                                        <option :value="0">Cantrip (0)</option>
                                        <option
                                            v-for="lvl in 9"
                                            :key="lvl"
                                            :value="lvl"
                                        >
                                            Level {{ lvl }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium"
                                        >School</label
                                    >
                                    <input
                                        v-model="customSpell.school"
                                        type="text"
                                        placeholder="Evocation"
                                        class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                    />
                                </div>
                            </div>

                            <div
                                class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3"
                            >
                                <div>
                                    <label class="block text-xs font-medium"
                                        >Casting Time</label
                                    >
                                    <input
                                        v-model="customSpell.casting_time"
                                        type="text"
                                        placeholder="1 Action"
                                        class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium"
                                        >Range</label
                                    >
                                    <input
                                        v-model="customSpell.range"
                                        type="text"
                                        placeholder="60 ft / Touch / Self"
                                        class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium"
                                        >Duration</label
                                    >
                                    <input
                                        v-model="customSpell.duration"
                                        type="text"
                                        placeholder="Instantaneous"
                                        class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                    />
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="block text-xs font-medium"
                                    >Summary / Effect</label
                                >
                                <input
                                    v-model="customSpell.summary"
                                    type="text"
                                    placeholder="2d8+1d6 damage of random type..."
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                />
                            </div>

                            <div class="mt-3 flex justify-end gap-2">
                                <button
                                    type="button"
                                    class="rounded-xs px-2.5 py-1 text-xs text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A]"
                                    @click="showCustomSpellForm = false"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="button"
                                    class="rounded-xs bg-[#1b1b18] px-3 py-1 text-xs font-semibold text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]"
                                    @click="addCustomSpell"
                                >
                                    Save Spell
                                </button>
                            </div>
                        </div>

                        <!-- Spells List Grouped by Level -->
                        <div
                            v-if="sheet.spells_list.length > 0"
                            class="mt-4 space-y-5"
                        >
                            <div
                                v-for="group in groupedSpells"
                                :key="group.level"
                                class="space-y-2.5"
                            >
                                <div
                                    class="flex items-center gap-2 border-b border-[#e3e3e0] pb-1.5 dark:border-[#3E3E3A]"
                                >
                                    <span
                                        class="text-xs font-bold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                    >
                                        {{ group.label }}
                                    </span>
                                    <span
                                        class="py-0.2 rounded-full bg-[#1b1b18]/5 px-2 text-[10px] font-semibold dark:bg-[#EDEDEC]/10"
                                    >
                                        {{ group.spells.length }}
                                    </span>
                                </div>

                                <div class="space-y-2">
                                    <div
                                        v-for="spell in group.spells"
                                        :key="spell.id"
                                        class="rounded-md border border-[#e3e3e0] bg-[#FDFDFC] p-3.5 transition-colors sm:p-4 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                    >
                                        <div
                                            class="flex flex-wrap items-center justify-between gap-2"
                                        >
                                            <div
                                                class="flex flex-wrap items-center gap-2"
                                            >
                                                <span
                                                    class="text-sm font-bold sm:text-base"
                                                >
                                                    {{ spell.name }}
                                                </span>
                                                <span
                                                    v-if="spell.school"
                                                    class="rounded-full border border-[#e3e3e0] px-2 py-0.5 text-[11px] font-medium text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                                                >
                                                    {{ spell.school }}
                                                </span>
                                                <span
                                                    v-if="spell.casting_time"
                                                    class="rounded-full bg-[#1b1b18]/5 px-2 py-0.5 text-[11px] font-medium dark:bg-[#EDEDEC]/5"
                                                >
                                                    {{ spell.casting_time }}
                                                </span>
                                                <span
                                                    v-if="spell.range"
                                                    class="text-xs text-[#706f6c] dark:text-[#A1A09A]"
                                                >
                                                    {{ spell.range }}
                                                </span>
                                                <span
                                                    v-if="spell.duration"
                                                    class="text-xs text-[#706f6c] dark:text-[#A1A09A]"
                                                >
                                                    · {{ spell.duration }}
                                                </span>
                                            </div>

                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <button
                                                    v-if="
                                                        spell.level > 0 &&
                                                        props.canEdit
                                                    "
                                                    type="button"
                                                    class="cursor-pointer rounded-xs border px-2 py-0.5 text-xs font-semibold transition-colors"
                                                    :class="
                                                        spell.prepared
                                                            ? 'border-indigo-500/40 bg-indigo-500/10 text-indigo-700 dark:text-indigo-400'
                                                            : 'border-[#e3e3e0] text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]'
                                                    "
                                                    @click="
                                                        toggleSpellPrepared(
                                                            spell,
                                                        )
                                                    "
                                                >
                                                    {{
                                                        spell.prepared
                                                            ? 'Prepared'
                                                            : 'Unprepared'
                                                    }}
                                                </button>
                                                <button
                                                    v-if="props.canEdit"
                                                    type="button"
                                                    title="Remove spell"
                                                    class="cursor-pointer rounded-xs p-1 text-xs text-[#706f6c] transition-colors hover:text-red-600 dark:text-[#A1A09A] dark:hover:text-red-400"
                                                    @click="
                                                        removeSpell(spell.id)
                                                    "
                                                >
                                                    ✕
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Dynamic Attack / Save Roll Tags -->
                                        <div
                                            v-if="
                                                spell.attack_type ||
                                                spell.save_ability ||
                                                spell.damage
                                            "
                                            class="mt-2.5 flex flex-wrap items-center gap-2"
                                        >
                                            <span
                                                v-if="spell.attack_type"
                                                class="rounded-sm bg-[#1b1b18]/5 px-2.5 py-1 text-xs font-semibold text-[#1b1b18] dark:bg-[#EDEDEC]/5 dark:text-[#EDEDEC]"
                                            >
                                                Spell Attack:
                                                {{
                                                    spellAttackBonus !== null
                                                        ? formatSigned(
                                                              spellAttackBonus,
                                                          )
                                                        : '+0'
                                                }}
                                                to hit
                                            </span>
                                            <span
                                                v-if="spell.save_ability"
                                                class="rounded-sm bg-[#1b1b18]/5 px-2.5 py-1 text-xs font-semibold text-[#1b1b18] dark:bg-[#EDEDEC]/5 dark:text-[#EDEDEC]"
                                            >
                                                Save: DC
                                                {{
                                                    spellSaveDc !== null
                                                        ? spellSaveDc
                                                        : '10'
                                                }}
                                                {{
                                                    spell.save_ability.toUpperCase()
                                                }}
                                            </span>
                                            <span
                                                v-if="spell.damage"
                                                class="rounded-sm border border-[#e3e3e0] px-2.5 py-1 text-xs font-medium dark:border-[#3E3E3A]"
                                            >
                                                {{ spell.damage }}
                                                {{ spell.damage_type }}
                                            </span>
                                        </div>

                                        <!-- Summary & Description Expander -->
                                        <div
                                            class="mt-2 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                                        >
                                            <p v-if="spell.summary">
                                                {{ spell.summary }}
                                            </p>
                                            <div
                                                v-if="spell.description"
                                                class="mt-1"
                                            >
                                                <button
                                                    type="button"
                                                    class="cursor-pointer text-[11px] font-medium text-[#1b1b18] underline hover:opacity-80 dark:text-[#EDEDEC]"
                                                    @click="
                                                        toggleSpellExpand(
                                                            spell.id,
                                                        )
                                                    "
                                                >
                                                    {{
                                                        expandedSpellIds.includes(
                                                            spell.id,
                                                        )
                                                            ? '▲ Hide Details'
                                                            : '▼ Read Full Description'
                                                    }}
                                                </button>
                                                <p
                                                    v-if="
                                                        expandedSpellIds.includes(
                                                            spell.id,
                                                        )
                                                    "
                                                    class="mt-2 rounded-xs border border-[#e3e3e0] bg-[#1b1b18]/[0.02] p-2.5 text-xs leading-relaxed text-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#EDEDEC]/[0.02] dark:text-[#EDEDEC]"
                                                >
                                                    {{ spell.description }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="mt-4 rounded-md border border-dashed border-[#e3e3e0] p-4 text-center text-xs text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                        >
                            No spells in list. Use '+ Add Spell from Compendium'
                            to choose Cantrips and 1st-level spells.
                        </div>

                        <!-- Additional Spell Notes -->
                        <div
                            class="mt-4 border-t border-[#e3e3e0] pt-3 dark:border-[#3E3E3A]"
                        >
                            <label
                                class="block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Additional Spell Notes, Spell Slots &amp;
                                Rituals
                            </label>
                            <textarea
                                v-model="sheet.spells"
                                :disabled="!props.canEdit"
                                rows="3"
                                placeholder="Spell slots used/total (e.g. 1st: 3/4), ritual caster notes, spell scroll inventory..."
                                class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] p-2.5 text-xs focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:focus:border-[#EDEDEC]"
                            />
                        </div>
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
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-base font-semibold">
                                    Inventory, Magic Items &amp; Adventuring
                                    Gear
                                </h2>
                                <button
                                    v-if="props.canEdit"
                                    type="button"
                                    class="inline-flex cursor-pointer items-center gap-1 rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1 text-xs font-medium hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:hover:border-[#EDEDEC]"
                                    @click="openCompendium('gear')"
                                >
                                    <span>+ Add Gear from Compendium</span>
                                </button>
                                <button
                                    v-if="props.canEdit"
                                    type="button"
                                    class="inline-flex cursor-pointer items-center gap-1 rounded-xs border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1 text-xs font-medium text-[#706f6c] hover:border-[#1b1b18] hover:text-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#A1A09A] dark:hover:border-[#EDEDEC] dark:hover:text-[#EDEDEC]"
                                    @click="
                                        showCustomItemForm = !showCustomItemForm
                                    "
                                >
                                    <span>{{
                                        showCustomItemForm
                                            ? 'Cancel'
                                            : '+ Custom Item'
                                    }}</span>
                                </button>
                            </div>
                            <p
                                class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Track quantities of adventuring gear, potions,
                                and treasure items with quick stepper controls.
                            </p>
                        </div>
                    </div>

                    <!-- Custom Item Form -->
                    <div
                        v-if="showCustomItemForm && props.canEdit"
                        class="mt-4 rounded-md border border-[#e3e3e0] bg-[#1b1b18]/[0.02] p-4 dark:border-[#3E3E3A] dark:bg-[#EDEDEC]/[0.02]"
                    >
                        <h3
                            class="text-xs font-bold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Add Custom Item
                        </h3>
                        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium"
                                    >Item Name</label
                                >
                                <input
                                    v-model="customItem.name"
                                    type="text"
                                    placeholder="e.g. Mysterious Sapphire Ring"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium"
                                    >Quantity</label
                                >
                                <input
                                    v-model.number="customItem.quantity"
                                    type="number"
                                    min="1"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-center text-xs dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium"
                                    >Weight</label
                                >
                                <input
                                    v-model="customItem.weight"
                                    type="text"
                                    placeholder="e.g. 1 lb"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                                />
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="block text-xs font-medium"
                                >Summary / Description</label
                            >
                            <input
                                v-model="customItem.summary"
                                type="text"
                                placeholder="Glows softly in darkness, found in goblin cave..."
                                class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-2.5 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                            />
                        </div>

                        <div class="mt-3 flex justify-end gap-2">
                            <button
                                type="button"
                                class="rounded-xs px-2.5 py-1 text-xs text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A]"
                                @click="showCustomItemForm = false"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="rounded-xs bg-[#1b1b18] px-3 py-1 text-xs font-semibold text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]"
                                @click="addCustomItem"
                            >
                                Save Item
                            </button>
                        </div>
                    </div>

                    <!-- Inventory Items List Cards -->
                    <div
                        v-if="sheet.inventory_list.length > 0"
                        class="mt-4 space-y-2"
                    >
                        <div
                            v-for="item in sheet.inventory_list"
                            :key="item.id"
                            class="flex flex-col justify-between gap-3 rounded-md border border-[#e3e3e0] bg-[#FDFDFC] p-3 transition-colors sm:flex-row sm:items-center dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                        >
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-semibold">
                                        {{ item.name }}
                                    </span>
                                    <span
                                        v-if="item.weight"
                                        class="rounded-full border border-[#e3e3e0] px-2 py-0.5 text-[10px] text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                                    >
                                        {{ item.weight }}
                                    </span>
                                </div>
                                <p
                                    v-if="item.summary"
                                    class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    {{ item.summary }}
                                </p>
                                <div v-if="item.description" class="mt-1">
                                    <button
                                        type="button"
                                        class="cursor-pointer text-[11px] font-medium text-[#1b1b18] underline hover:opacity-80 dark:text-[#EDEDEC]"
                                        @click="toggleItemExpand(item.id)"
                                    >
                                        {{
                                            expandedItemIds.includes(item.id)
                                                ? '▲ Hide Details'
                                                : '▼ Read Details'
                                        }}
                                    </button>
                                    <p
                                        v-if="expandedItemIds.includes(item.id)"
                                        class="mt-1.5 rounded-xs border border-[#e3e3e0] bg-[#1b1b18]/[0.02] p-2 text-xs leading-relaxed dark:border-[#3E3E3A] dark:bg-[#EDEDEC]/[0.02]"
                                    >
                                        {{ item.description }}
                                    </p>
                                </div>
                            </div>

                            <!-- Quantity Stepper & Delete -->
                            <div
                                class="flex items-center gap-3 self-end sm:self-center"
                            >
                                <div
                                    class="flex items-center rounded-sm border border-[#e3e3e0] bg-white dark:border-[#3E3E3A] dark:bg-[#161615]"
                                >
                                    <button
                                        type="button"
                                        :disabled="
                                            !props.canEdit || item.quantity <= 1
                                        "
                                        class="flex h-7 w-7 items-center justify-center text-xs font-bold text-[#706f6c] transition-colors hover:bg-[#1b1b18]/5 disabled:opacity-30 dark:text-[#A1A09A] dark:hover:bg-[#EDEDEC]/5"
                                        @click="
                                            changeInventoryQuantity(item, -1)
                                        "
                                    >
                                        −
                                    </button>
                                    <span
                                        class="min-w-[28px] text-center text-xs font-bold"
                                    >
                                        {{ item.quantity }}
                                    </span>
                                    <button
                                        type="button"
                                        :disabled="!props.canEdit"
                                        class="flex h-7 w-7 items-center justify-center text-xs font-bold text-[#706f6c] transition-colors hover:bg-[#1b1b18]/5 disabled:opacity-30 dark:text-[#A1A09A] dark:hover:bg-[#EDEDEC]/5"
                                        @click="
                                            changeInventoryQuantity(item, 1)
                                        "
                                    >
                                        +
                                    </button>
                                </div>

                                <button
                                    v-if="props.canEdit"
                                    type="button"
                                    title="Remove item"
                                    class="cursor-pointer rounded-xs p-1 text-xs text-[#706f6c] transition-colors hover:text-red-600 dark:text-[#A1A09A] dark:hover:text-red-400"
                                    @click="removeInventoryItem(item.id)"
                                >
                                    ✕
                                </button>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="mt-4 rounded-md border border-dashed border-[#e3e3e0] p-4 text-center text-xs text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                    >
                        No gear in list. Use '+ Add Gear from Compendium' or '+
                        Custom Item' to track items.
                    </div>

                    <!-- Additional Inventory Notes -->
                    <div
                        class="mt-4 border-t border-[#e3e3e0] pt-3 dark:border-[#3E3E3A]"
                    >
                        <label
                            class="block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Additional Inventory &amp; Backpack Notes
                        </label>
                        <textarea
                            v-model="sheet.equipment"
                            :disabled="!props.canEdit"
                            rows="3"
                            placeholder="Explorer's Pack details, Bag of Holding contents, treasure gems, mount equipment..."
                            class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] p-2.5 text-xs focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:focus:border-[#EDEDEC]"
                        />
                    </div>
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
                                    { key: 'feature', label: 'Features' },
                                    { key: 'trait', label: 'Traits' },
                                    { key: 'feat', label: 'Feats' },
                                    { key: 'language', label: 'Languages' },
                                    { key: 'weapon', label: 'Weapons' },
                                    { key: 'armor', label: 'Armor' },
                                    { key: 'spell', label: 'Spells' },
                                    { key: 'gear', label: 'Gear' },
                                    { key: 'class', label: 'Classes' },
                                    { key: 'species', label: 'Species' },
                                    { key: 'background', label: 'Backgrounds' },
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
                                    <span
                                        v-if="entry.data?.category"
                                        class="rounded-xs border border-[#e3e3e0] px-1.5 py-0.5 text-[10px] text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                                    >
                                        {{ entry.data.category }}
                                    </span>
                                    <span
                                        v-if="entry.data?.source"
                                        class="rounded-xs border border-[#e3e3e0] px-1.5 py-0.5 text-[10px] text-[#706f6c] capitalize dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                                    >
                                        {{ entry.data.source }}
                                    </span>
                                    <span
                                        v-if="entry.data?.level"
                                        class="rounded-xs bg-[#1b1b18]/5 px-1.5 py-0.5 text-[10px] text-[#706f6c] dark:bg-[#EDEDEC]/10 dark:text-[#A1A09A]"
                                    >
                                        Lvl {{ entry.data.level }}
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
