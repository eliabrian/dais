<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';
import CharacterController from '@/actions/App/Http/Controllers/CharacterController';
import {
    ABILITY_INFO,
    DND_BACKGROUNDS,
    DND_CLASSES,
    DND_SPECIES,
    ORIGIN_FEATS,
    POINT_BUY_COSTS,
    SKILL_LIST,
    STANDARD_ARRAY,
    WEAPON_MASTERY_OPTIONS,
} from '@/lib/dnd2024BuilderData';
import type {
    AbilityKey,
    CharacterCampaignRef,
    Dnd2024SheetData,
    SheetArmorItem,
    SheetFeatItem,
    SheetFeatureItem,
    SheetInventoryItem,
    SheetLanguageItem,
    SheetSpellItem,
    SheetTraitItem,
    SheetWeaponItem,
    SkillProficiency,
    System,
} from '@/types';

const props = withDefaults(
    defineProps<{
        systems?: System[];
        availableCampaigns?: CharacterCampaignRef[];
        fixedCampaignId?: string;
        fixedCampaignName?: string;
        fixedSystemId?: string;
    }>(),
    {
        systems: () => [],
        availableCampaigns: () => [],
        fixedCampaignId: undefined,
        fixedCampaignName: undefined,
        fixedSystemId: undefined,
    },
);

const emit = defineEmits<{
    (e: 'close'): void;
}>();

type WizardStep = 1 | 2 | 3 | 4 | 5 | 6;

const currentStep = ref<WizardStep>(1);

const steps: { step: WizardStep; title: string; subtitle: string }[] = [
    { step: 1, title: '1. Identity', subtitle: 'Name & campaign' },
    { step: 2, title: '2. Class', subtitle: 'Features & skills' },
    { step: 3, title: '3. Background', subtitle: 'Origin feat & bonuses' },
    { step: 4, title: '4. Species', subtitle: 'Lineage & traits' },
    { step: 5, title: '5. Abilities', subtitle: 'Scores & hit points' },
    { step: 6, title: '6. Equipment', subtitle: 'Gear & review' },
];

// Step 1: Identity & Basics
const charName = ref('');
const avatarUrl = ref('');
const level = ref(1);

const defaultSystemId = computed(() => {
    if (props.fixedSystemId) {
        return props.fixedSystemId;
    }
    const dnd = props.systems.find((sys) => sys.code === 'dnd5e_2024');
    return dnd?.id ?? props.systems[0]?.id ?? '';
});

const systemId = ref(defaultSystemId.value);
const campaignId = ref(props.fixedCampaignId ?? '');

watch(defaultSystemId, (val) => {
    if (!systemId.value && val) {
        systemId.value = val;
    }
});

// Step 2: Class & Class Features
const selectedClassName = ref('Fighter');
const selectedSubclass = ref('');
const classFeatureChoices = reactive<Record<string, string>>({
    fighting_style: 'Defense',
    divine_order: 'Protector',
    primal_order: 'Magician',
    eldritch_invocations: 'Pact of the Blade',
});
const enabledClassFeatures = ref<string[]>([
    'fighting_style',
    'second_wind',
    'weapon_mastery_fighter',
]);
const classSkills = ref<string[]>(['athletics', 'perception']);
const allowAnyClassSkill = ref(false);
const rogueExpertiseSkills = ref<string[]>(['stealth', 'sleight_of_hand']);
const selectedWeaponMasteries = ref<string[]>(['Sap', 'Graze', 'Topple']);

const currentClassDef = computed(
    () =>
        DND_CLASSES.find((c) => c.name === selectedClassName.value) ??
        DND_CLASSES[4],
);

watch(selectedClassName, () => {
    const def = currentClassDef.value;
    enabledClassFeatures.value = def.features.map((f) => f.id);
    for (const f of def.features) {
        if (
            f.choices &&
            f.choices.options.length > 0 &&
            !classFeatureChoices[f.id]
        ) {
            classFeatureChoices[f.id] = f.choices.options[0].name;
        }
    }
    classSkills.value = [...def.recommendedSkills];
    selectedWeaponMasteries.value = [...def.recommendedMasteries];
    selectedSubclass.value = level.value >= 3 ? (def.subclasses[0] ?? '') : '';

    if (abilityMethod.value === 'standard') {
        applyClassRecommendedAbilities();
    }
    syncStartingEquipmentFromClass();
});

watch(level, (newLvl) => {
    if (newLvl >= 3 && !selectedSubclass.value) {
        selectedSubclass.value = currentClassDef.value.subclasses[0] ?? '';
    }
});

// Step 3: Background, Origin Feat, Background Feature & Ability Bonuses
const selectedBackgroundName = ref('Soldier');
const selectedOriginFeatName = ref('Savage Attacker');
const selectedBackgroundFeatureName = ref('Military Rank');
const backgroundSkills = ref<[string, string]>(['athletics', 'intimidation']);
const toolProficiency = ref('Gaming Set');
const skilledExtraSkills = ref<string[]>(['perception', 'insight', 'survival']);

const bonusDistributionMode = ref<'2_1' | '1_1_1'>('2_1');
const allowAnyBackgroundAbility = ref(false);
const bonusPlusTwoAbility = ref<AbilityKey>('str');
const bonusPlusOneAbility = ref<AbilityKey>('con');
const bonusThreeAbilities = ref<AbilityKey[]>(['str', 'dex', 'con']);

const currentBackgroundDef = computed(
    () =>
        DND_BACKGROUNDS.find((b) => b.name === selectedBackgroundName.value) ??
        DND_BACKGROUNDS[14],
);

const currentOriginFeatDef = computed(
    () =>
        ORIGIN_FEATS.find((f) => f.name === selectedOriginFeatName.value) ??
        ORIGIN_FEATS[0],
);

watch(selectedBackgroundName, () => {
    const bg = currentBackgroundDef.value;
    selectedOriginFeatName.value = bg.originFeat;
    selectedBackgroundFeatureName.value = bg.features[0]?.name ?? '';
    backgroundSkills.value = [bg.skills[0], bg.skills[1]];
    toolProficiency.value = bg.toolProficiency;
    bonusPlusTwoAbility.value = bg.defaultPlusTwo;
    bonusPlusOneAbility.value = bg.defaultPlusOne;
    bonusThreeAbilities.value = [...bg.abilities];
});

// Step 4: Species, Lineage & Species Traits
const selectedSpeciesName = ref('Human');
const selectedSize = ref<'Medium' | 'Small'>('Medium');
const selectedLineageName = ref('');
const humanExtraSkill = ref('persuasion');
const humanVersatileFeat = ref('Alert');
const elfKeenSenseSkill = ref('perception');

const currentSpeciesDef = computed(
    () =>
        DND_SPECIES.find((s) => s.name === selectedSpeciesName.value) ??
        DND_SPECIES[0],
);

watch(selectedSpeciesName, () => {
    const sp = currentSpeciesDef.value;
    selectedSize.value = sp.defaultSize;
    selectedLineageName.value = sp.lineages?.[0]?.name ?? '';
});

const effectiveBaseSpeed = computed(() => {
    const sp = currentSpeciesDef.value;
    if (sp.lineages && selectedLineageName.value) {
        const lin = sp.lineages.find(
            (l) => l.name === selectedLineageName.value,
        );
        if (lin?.speedOverride) {
            return lin.speedOverride;
        }
    }
    return sp.speed;
});

// Step 5: Ability Scores
type AbilityGenMethod = 'standard' | 'pointbuy' | 'manual';
const abilityMethod = ref<AbilityGenMethod>('standard');

const baseAbilities = reactive<Record<AbilityKey, number>>({
    str: 15,
    dex: 13,
    con: 14,
    int: 10,
    wis: 12,
    cha: 8,
});

function applyClassRecommendedAbilities(): void {
    const rec = currentClassDef.value.recommendedAbilities;
    baseAbilities.str = rec.str;
    baseAbilities.dex = rec.dex;
    baseAbilities.con = rec.con;
    baseAbilities.int = rec.int;
    baseAbilities.wis = rec.wis;
    baseAbilities.cha = rec.cha;
}

function resetPointBuy(): void {
    baseAbilities.str = 10;
    baseAbilities.dex = 10;
    baseAbilities.con = 10;
    baseAbilities.int = 10;
    baseAbilities.wis = 10;
    baseAbilities.cha = 10;
}

watch(abilityMethod, (method) => {
    if (method === 'standard') {
        applyClassRecommendedAbilities();
    } else if (method === 'pointbuy') {
        for (const k of [
            'str',
            'dex',
            'con',
            'int',
            'wis',
            'cha',
        ] as AbilityKey[]) {
            baseAbilities[k] = Math.max(8, Math.min(15, baseAbilities[k]));
        }
    }
});

const pointBuySpent = computed(() => {
    let total = 0;
    for (const k of [
        'str',
        'dex',
        'con',
        'int',
        'wis',
        'cha',
    ] as AbilityKey[]) {
        const score = Math.max(8, Math.min(15, baseAbilities[k]));
        total += POINT_BUY_COSTS[score] ?? 0;
    }
    return total;
});

const pointBuyRemaining = computed(() => 27 - pointBuySpent.value);

function adjustPointBuy(key: AbilityKey, delta: number): void {
    const next = baseAbilities[key] + delta;
    if (next < 8 || next > 15) {
        return;
    }
    const currentCost = POINT_BUY_COSTS[baseAbilities[key]] ?? 0;
    const nextCost = POINT_BUY_COSTS[next] ?? 0;
    if (delta > 0 && pointBuyRemaining.value < nextCost - currentCost) {
        return;
    }
    baseAbilities[key] = next;
}

function assignStandardArrayValue(key: AbilityKey, newVal: number): void {
    const oldVal = baseAbilities[key];
    const otherKey = (
        ['str', 'dex', 'con', 'int', 'wis', 'cha'] as AbilityKey[]
    ).find((k) => k !== key && baseAbilities[k] === newVal);
    if (otherKey) {
        baseAbilities[otherKey] = oldVal;
    }
    baseAbilities[key] = newVal;
}

function rollSingleAbility(key: AbilityKey): void {
    const rolls = Array.from(
        { length: 4 },
        () => Math.floor(Math.random() * 6) + 1,
    );
    rolls.sort((a, b) => a - b);
    baseAbilities[key] = rolls[1] + rolls[2] + rolls[3];
}

function rollAllAbilities(): void {
    abilityMethod.value = 'manual';
    for (const k of [
        'str',
        'dex',
        'con',
        'int',
        'wis',
        'cha',
    ] as AbilityKey[]) {
        rollSingleAbility(k);
    }
}

function backgroundBonusFor(key: AbilityKey): number {
    if (bonusDistributionMode.value === '2_1') {
        if (bonusPlusTwoAbility.value === key) {
            return 2;
        }
        if (
            bonusPlusOneAbility.value === key &&
            bonusPlusOneAbility.value !== bonusPlusTwoAbility.value
        ) {
            return 1;
        }
        return 0;
    }
    return bonusThreeAbilities.value.includes(key) ? 1 : 0;
}

function toggleThreeAbilityBonus(key: AbilityKey): void {
    const idx = bonusThreeAbilities.value.indexOf(key);
    if (idx >= 0) {
        if (bonusThreeAbilities.value.length > 1) {
            bonusThreeAbilities.value.splice(idx, 1);
        }
    } else if (bonusThreeAbilities.value.length < 3) {
        bonusThreeAbilities.value.push(key);
    } else {
        bonusThreeAbilities.value.shift();
        bonusThreeAbilities.value.push(key);
    }
}

const finalAbilities = computed<Record<AbilityKey, number>>(() => {
    const res = {} as Record<AbilityKey, number>;
    for (const k of [
        'str',
        'dex',
        'con',
        'int',
        'wis',
        'cha',
    ] as AbilityKey[]) {
        res[k] = Math.min(
            20,
            Math.max(3, Number(baseAbilities[k] || 10) + backgroundBonusFor(k)),
        );
    }
    return res;
});

function abilityMod(key: AbilityKey): number {
    return Math.floor((finalAbilities.value[key] - 10) / 2);
}

function formatSigned(val: number): string {
    return val >= 0 ? `+${val}` : `${val}`;
}

const proficiencyBonus = computed(() => {
    const lvl = Math.max(1, Math.min(20, Number(level.value) || 1));
    return Math.ceil(lvl / 4) + 1;
});

// Combined Skills & Expertise Map
const combinedSkillsMap = computed<Record<string, SkillProficiency>>(() => {
    const map: Record<string, SkillProficiency> = {};
    for (const sk of classSkills.value) {
        if (sk) {
            map[sk] = 'proficient';
        }
    }
    for (const sk of backgroundSkills.value) {
        if (sk) {
            map[sk] = 'proficient';
        }
    }
    if (
        selectedOriginFeatName.value === 'Skilled' ||
        (selectedSpeciesName.value === 'Human' &&
            humanVersatileFeat.value === 'Skilled')
    ) {
        for (const sk of skilledExtraSkills.value) {
            if (sk) {
                map[sk] = 'proficient';
            }
        }
    }
    if (selectedSpeciesName.value === 'Human' && humanExtraSkill.value) {
        map[humanExtraSkill.value] = 'proficient';
    }
    if (selectedSpeciesName.value === 'Elf' && elfKeenSenseSkill.value) {
        map[elfKeenSenseSkill.value] = 'proficient';
    }
    if (selectedClassName.value === 'Rogue') {
        for (const expSk of rogueExpertiseSkills.value) {
            if (expSk && map[expSk]) {
                map[expSk] = 'expertise';
            }
        }
    }
    return map;
});

const proficientSkillNames = computed(() =>
    SKILL_LIST.filter(
        (s) =>
            combinedSkillsMap.value[s.key] &&
            combinedSkillsMap.value[s.key] !== 'none',
    ).map((s) =>
        combinedSkillsMap.value[s.key] === 'expertise'
            ? `${s.label} (Expertise)`
            : s.label,
    ),
);

function toggleClassSkill(skillKey: string): void {
    const idx = classSkills.value.indexOf(skillKey);
    const max = currentClassDef.value.skillChoicesCount;
    if (idx >= 0) {
        classSkills.value.splice(idx, 1);
    } else if (classSkills.value.length < max) {
        classSkills.value.push(skillKey);
    } else {
        classSkills.value.shift();
        classSkills.value.push(skillKey);
    }
}

function toggleRogueExpertise(skillKey: string): void {
    const idx = rogueExpertiseSkills.value.indexOf(skillKey);
    if (idx >= 0) {
        rogueExpertiseSkills.value.splice(idx, 1);
    } else if (rogueExpertiseSkills.value.length < 2) {
        rogueExpertiseSkills.value.push(skillKey);
    } else {
        rogueExpertiseSkills.value.shift();
        rogueExpertiseSkills.value.push(skillKey);
    }
}

function toggleWeaponMastery(masteryName: string): void {
    const idx = selectedWeaponMasteries.value.indexOf(masteryName);
    const max = Math.max(1, currentClassDef.value.weaponMasteryCount || 2);
    if (idx >= 0) {
        selectedWeaponMasteries.value.splice(idx, 1);
    } else if (selectedWeaponMasteries.value.length < max) {
        selectedWeaponMasteries.value.push(masteryName);
    } else {
        selectedWeaponMasteries.value.shift();
        selectedWeaponMasteries.value.push(masteryName);
    }
}

function toggleSkilledExtraSkill(skillKey: string): void {
    const idx = skilledExtraSkills.value.indexOf(skillKey);
    if (idx >= 0) {
        skilledExtraSkills.value.splice(idx, 1);
    } else if (skilledExtraSkills.value.length < 3) {
        skilledExtraSkills.value.push(skillKey);
    } else {
        skilledExtraSkills.value.shift();
        skilledExtraSkills.value.push(skillKey);
    }
}

// Calculated Max HP
const calculatedMaxHp = computed(() => {
    const lvl = Math.max(1, Math.min(20, Number(level.value) || 1));
    const hd = currentClassDef.value.hitDie;
    const con = abilityMod('con');
    const avgPerLevel = Math.floor(hd / 2) + 1;

    let total = hd + con + (lvl - 1) * (avgPerLevel + con);

    const hasTough =
        selectedOriginFeatName.value === 'Tough' ||
        (selectedSpeciesName.value === 'Human' &&
            humanVersatileFeat.value === 'Tough');
    if (hasTough) {
        total += 2 * lvl;
    }

    if (currentSpeciesDef.value.hpPerLevelBonus) {
        total += currentSpeciesDef.value.hpPerLevelBonus * lvl;
    }

    return Math.max(lvl, total);
});

const customHpOverride = ref<number | null>(null);
const effectiveMaxHp = computed(() =>
    customHpOverride.value !== null && customHpOverride.value > 0
        ? customHpOverride.value
        : calculatedMaxHp.value,
);

// Step 6: Equipment & Spells
const equipmentChoice = ref<'package_a' | 'gold_only'>('package_a');

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

const startingWeapons = ref<SheetWeaponItem[]>([]);
const startingArmors = ref<SheetArmorItem[]>([]);
const startingInventory = ref<SheetInventoryItem[]>([]);
const startingSpells = ref<SheetSpellItem[]>([]);

function syncStartingEquipmentFromClass(): void {
    const cls = currentClassDef.value;
    startingWeapons.value = cls.startingEquipment.weapons.map((w) => ({
        ...w,
        id: generateId(),
    }));
    startingArmors.value = cls.startingEquipment.armors.map((a) => ({
        ...a,
        id: generateId(),
    }));
    startingInventory.value = cls.startingEquipment.inventory.map((i) => ({
        ...i,
        id: generateId(),
    }));
    startingSpells.value = (cls.recommendedSpells ?? []).map((s) => ({
        ...s,
        id: generateId(),
    }));
}

// Initialize equipment on setup
syncStartingEquipmentFromClass();

const startingGold = computed(() => {
    const cls = currentClassDef.value;
    const bgGold = 15;
    return equipmentChoice.value === 'package_a'
        ? cls.startingEquipment.goldA + bgGold
        : cls.startingEquipment.goldB + currentBackgroundDef.value.startingGold;
});

const calculatedAc = computed(() => {
    const dexMod = abilityMod('dex');
    const conMod = abilityMod('con');
    const wisMod = abilityMod('wis');

    const armors =
        equipmentChoice.value === 'package_a' ? startingArmors.value : [];
    const equippedBody = armors.find(
        (a) => a.equipped && a.category?.toLowerCase() !== 'shield',
    );
    const equippedShields = armors.filter(
        (a) => a.equipped && a.category?.toLowerCase() === 'shield',
    );
    const shieldBonus = equippedShields.reduce(
        (sum, s) => sum + (s.ac_bonus ?? 2),
        0,
    );

    const hasDefenseStyle =
        selectedClassName.value === 'Fighter' &&
        enabledClassFeatures.value.includes('fighting_style') &&
        classFeatureChoices.fighting_style === 'Defense';

    if (equippedBody) {
        const cat = equippedBody.category?.toLowerCase() || '';
        let ac = Number(equippedBody.ac_base ?? 10);
        if (equippedBody.dex_bonus === 'full' || cat.includes('light')) {
            ac += dexMod;
        } else if (
            equippedBody.dex_bonus === 'max_2' ||
            cat.includes('medium')
        ) {
            ac += Math.min(2, Math.max(0, dexMod));
        }
        return ac + shieldBonus + (hasDefenseStyle ? 1 : 0);
    }

    if (selectedClassName.value === 'Barbarian') {
        return 10 + dexMod + conMod + shieldBonus;
    }
    if (selectedClassName.value === 'Monk' && shieldBonus === 0) {
        return 10 + dexMod + wisMod;
    }

    return 10 + dexMod + shieldBonus;
});

const calculatedInitiative = computed(() => {
    const dex = abilityMod('dex');
    const hasAlert =
        selectedOriginFeatName.value === 'Alert' ||
        (selectedSpeciesName.value === 'Human' &&
            humanVersatileFeat.value === 'Alert');
    return dex + (hasAlert ? proficiencyBonus.value : 0);
});

// Generated Features & Proficiencies text
const generatedFeaturesText = computed(() => {
    const lines: string[] = [];

    // 1. Class Features
    const cls = currentClassDef.value;
    lines.push(
        `=== ${cls.name.toUpperCase()} FEATURES (Lvl ${level.value}) ===`,
    );
    if (selectedSubclass.value.trim()) {
        lines.push(`• Subclass: ${selectedSubclass.value.trim()}`);
    }
    for (const feat of cls.features) {
        if (!enabledClassFeatures.value.includes(feat.id)) {
            continue;
        }
        if (feat.choices && classFeatureChoices[feat.id]) {
            const chosenOpt = feat.choices.options.find(
                (o) => o.name === classFeatureChoices[feat.id],
            );
            lines.push(
                `• ${feat.name} — ${classFeatureChoices[feat.id]}: ${chosenOpt?.summary ?? feat.description}`,
            );
        } else {
            lines.push(`• ${feat.name}: ${feat.description}`);
        }
    }

    // 2. Background & Origin Feat
    const bg = currentBackgroundDef.value;
    const bgFeat = bg.features.find(
        (f) => f.name === selectedBackgroundFeatureName.value,
    );
    lines.push('');
    lines.push(`=== BACKGROUND: ${bg.name.toUpperCase()} ===`);
    if (bgFeat) {
        lines.push(`• ${bgFeat.name}: ${bgFeat.description}`);
    }

    const oFeat = currentOriginFeatDef.value;
    lines.push(`• Origin Feat — ${oFeat.name}: ${oFeat.summary}`);
    for (const b of oFeat.benefits) {
        lines.push(`  - ${b}`);
    }

    // 3. Species Traits
    const sp = currentSpeciesDef.value;
    lines.push('');
    lines.push(
        `=== SPECIES: ${sp.name.toUpperCase()}${selectedLineageName.value ? ` (${selectedLineageName.value})` : ''} ===`,
    );
    if (sp.lineages && selectedLineageName.value) {
        const lin = sp.lineages.find(
            (l) => l.name === selectedLineageName.value,
        );
        if (lin) {
            lines.push(
                `• ${sp.lineageLabel ?? 'Lineage'} — ${lin.name}: ${lin.summary}`,
            );
        }
    }
    if (sp.name === 'Human') {
        const vFeat = ORIGIN_FEATS.find(
            (f) => f.name === humanVersatileFeat.value,
        );
        lines.push(
            `• Versatile Origin Feat — ${humanVersatileFeat.value}: ${vFeat?.summary ?? ''}`,
        );
    }
    for (const trait of sp.traits) {
        lines.push(`• ${trait.name}: ${trait.description}`);
    }

    return lines.join('\n');
});

const generatedProficienciesText = computed(() => {
    const cls = currentClassDef.value;
    const sp = currentSpeciesDef.value;
    const parts: string[] = [];

    let armorProf = cls.armorProficiencies;
    let weaponProf = cls.weaponProficiencies;

    if (
        cls.name === 'Cleric' &&
        classFeatureChoices.divine_order === 'Protector'
    ) {
        armorProf = 'Light Armor, Medium Armor, Heavy Armor, Shields';
        weaponProf = 'Simple Weapons, Martial Weapons';
    }
    if (cls.name === 'Druid' && classFeatureChoices.primal_order === 'Warden') {
        armorProf = 'Light Armor, Medium Armor, Shields';
        weaponProf = 'Simple Weapons, Martial Weapons';
    }

    parts.push(`Armor Training: ${armorProf}`);
    parts.push(`Weapons: ${weaponProf}`);

    const tools: string[] = [];
    if (cls.toolProficiencies) {
        tools.push(cls.toolProficiencies);
    }
    if (toolProficiency.value.trim()) {
        tools.push(toolProficiency.value.trim());
    }
    if (tools.length > 0) {
        parts.push(`Tools: ${tools.join(', ')}`);
    }

    parts.push(`Languages: ${sp.languages.join(', ')}`);

    return parts.join('\n');
});

// Submission state
const isSubmitting = ref(false);
const validationErrors = ref<Record<string, string>>({});

function goToStep(step: WizardStep): void {
    if (step > 1 && !charName.value.trim()) {
        validationErrors.value = {
            name: 'Please enter a character name before continuing.',
        };
        currentStep.value = 1;
        return;
    }
    validationErrors.value = {};
    currentStep.value = step;
}

function nextStep(): void {
    if (currentStep.value === 1 && !charName.value.trim()) {
        validationErrors.value = {
            name: 'Please enter a character name first.',
        };
        return;
    }
    validationErrors.value = {};
    if (currentStep.value < 6) {
        currentStep.value = (currentStep.value + 1) as WizardStep;
    }
}

function prevStep(): void {
    if (currentStep.value > 1) {
        currentStep.value = (currentStep.value - 1) as WizardStep;
    }
}

function submitCharacter(): void {
    if (!charName.value.trim()) {
        validationErrors.value = { name: 'Character name is required.' };
        currentStep.value = 1;
        return;
    }
    if (isSubmitting.value) {
        return;
    }

    isSubmitting.value = true;
    validationErrors.value = {};

    const lvl = Math.max(1, Math.min(20, Number(level.value) || 1));
    const cls = currentClassDef.value;
    const sp = currentSpeciesDef.value;

    const originFeatDisplay =
        sp.name === 'Human' && humanVersatileFeat.value
            ? `${selectedOriginFeatName.value}, ${humanVersatileFeat.value}`
            : selectedOriginFeatName.value;

    const speciesDisplay = selectedLineageName.value
        ? `${sp.name} (${selectedLineageName.value})`
        : sp.name;

    const featuresList: SheetFeatureItem[] = [];
    for (const feat of cls.features) {
        if (!enabledClassFeatures.value.includes(feat.id)) {
            continue;
        }
        const chosenOpt =
            feat.choices && classFeatureChoices[feat.id]
                ? feat.choices.options.find(
                      (o) => o.name === classFeatureChoices[feat.id],
                  )
                : null;
        featuresList.push({
            id: crypto.randomUUID(),
            name: chosenOpt
                ? `${feat.name} (${classFeatureChoices[feat.id]})`
                : feat.name,
            source: 'class',
            level: 1,
            summary:
                chosenOpt?.summary ??
                (feat.description.length > 120
                    ? `${feat.description.slice(0, 117)}...`
                    : feat.description),
            description: chosenOpt
                ? `${feat.description}\n\nSelected Option: ${chosenOpt.name} - ${chosenOpt.summary}`
                : feat.description,
        });
    }

    const bgFeat = currentBackgroundDef.value.features.find(
        (f) => f.name === selectedBackgroundFeatureName.value,
    );
    if (bgFeat) {
        featuresList.push({
            id: crypto.randomUUID(),
            name: bgFeat.name,
            source: 'background',
            summary:
                bgFeat.description.length > 120
                    ? `${bgFeat.description.slice(0, 117)}...`
                    : bgFeat.description,
            description: bgFeat.description,
        });
    }

    const traitsList: SheetTraitItem[] = [];
    if (sp.lineages && selectedLineageName.value) {
        const lin = sp.lineages.find(
            (l) => l.name === selectedLineageName.value,
        );
        if (lin) {
            traitsList.push({
                id: crypto.randomUUID(),
                name: `${sp.lineageLabel ?? 'Lineage'}: ${lin.name}`,
                species_name: sp.name,
                summary: lin.summary,
                description: lin.summary,
            });
        }
    }
    for (const trait of sp.traits) {
        traitsList.push({
            id: crypto.randomUUID(),
            name: trait.name,
            species_name: sp.name,
            summary:
                trait.description.length > 120
                    ? `${trait.description.slice(0, 117)}...`
                    : trait.description,
            description: trait.description,
        });
    }

    const featsList: SheetFeatItem[] = [];
    const oFeat = currentOriginFeatDef.value;
    featsList.push({
        id: crypto.randomUUID(),
        name: oFeat.name,
        category: 'Origin',
        summary: oFeat.summary,
        description: oFeat.summary,
        benefits: [...oFeat.benefits],
    });
    if (sp.name === 'Human' && humanVersatileFeat.value) {
        const vFeat = ORIGIN_FEATS.find(
            (f) => f.name === humanVersatileFeat.value,
        );
        if (vFeat) {
            featsList.push({
                id: crypto.randomUUID(),
                name: vFeat.name,
                category: 'Origin',
                summary: vFeat.summary,
                description: vFeat.summary,
                benefits: [...vFeat.benefits],
            });
        }
    }

    const languagesList: SheetLanguageItem[] = sp.languages.map((langName) => ({
        id: crypto.randomUUID(),
        name: langName,
        type: 'Standard',
    }));

    const featuresList: SheetFeatureItem[] = [];
    for (const feat of cls.features) {
        if (!enabledClassFeatures.value.includes(feat.id)) {
            continue;
        }
        const chosenOpt =
            feat.choices && classFeatureChoices[feat.id]
                ? feat.choices.options.find(
                      (o) => o.name === classFeatureChoices[feat.id],
                  )
                : null;
        featuresList.push({
            id: crypto.randomUUID(),
            name: chosenOpt
                ? `${feat.name} (${classFeatureChoices[feat.id]})`
                : feat.name,
            source: 'class',
            level: 1,
            summary:
                chosenOpt?.summary ??
                (feat.description.length > 120
                    ? `${feat.description.slice(0, 117)}...`
                    : feat.description),
            description: chosenOpt
                ? `${feat.description}\n\nSelected Option: ${chosenOpt.name} - ${chosenOpt.summary}`
                : feat.description,
        });
    }

    const bgFeat = currentBackgroundDef.value.features.find(
        (f) => f.name === selectedBackgroundFeatureName.value,
    );
    if (bgFeat) {
        featuresList.push({
            id: crypto.randomUUID(),
            name: bgFeat.name,
            source: 'background',
            summary:
                bgFeat.description.length > 120
                    ? `${bgFeat.description.slice(0, 117)}...`
                    : bgFeat.description,
            description: bgFeat.description,
        });
    }

    const traitsList: SheetTraitItem[] = [];
    if (sp.lineages && selectedLineageName.value) {
        const lin = sp.lineages.find((l) => l.name === selectedLineageName.value);
        if (lin) {
            traitsList.push({
                id: crypto.randomUUID(),
                name: `${sp.lineageLabel ?? 'Lineage'}: ${lin.name}`,
                species_name: sp.name,
                summary: lin.summary,
                description: lin.summary,
            });
        }
    }
    for (const trait of sp.traits) {
        traitsList.push({
            id: crypto.randomUUID(),
            name: trait.name,
            species_name: sp.name,
            summary:
                trait.description.length > 120
                    ? `${trait.description.slice(0, 117)}...`
                    : trait.description,
            description: trait.description,
        });
    }

    const featsList: SheetFeatItem[] = [];
    const oFeat = currentOriginFeatDef.value;
    featsList.push({
        id: crypto.randomUUID(),
        name: oFeat.name,
        category: 'Origin',
        summary: oFeat.summary,
        description: oFeat.summary,
        benefits: [...oFeat.benefits],
    });
    if (sp.name === 'Human' && humanVersatileFeat.value) {
        const vFeat = ORIGIN_FEATS.find((f) => f.name === humanVersatileFeat.value);
        if (vFeat) {
            featsList.push({
                id: crypto.randomUUID(),
                name: vFeat.name,
                category: 'Origin',
                summary: vFeat.summary,
                description: vFeat.summary,
                benefits: [...vFeat.benefits],
            });
        }
    }

    const languagesList: SheetLanguageItem[] = sp.languages.map((langName) => ({
        id: crypto.randomUUID(),
        name: langName,
        type: 'Standard',
    }));

    const sheetData: Dnd2024SheetData = {
        species: speciesDisplay,
        class: cls.name,
        subclass: selectedSubclass.value.trim(),
        background: selectedBackgroundName.value,
        origin_feat: originFeatDisplay,
        level: lvl,
        xp: 0,
        armor_class: calculatedAc.value,
        speed: effectiveBaseSpeed.value,
        size: selectedSize.value,
        heroic_inspiration: Boolean(sp.grantsHeroicInspiration),
        exhaustion: 0,
        hp: {
            current: effectiveMaxHp.value,
            max: effectiveMaxHp.value,
            temp: 0,
        },
        hit_dice: {
            total: `${lvl}d${cls.hitDie}`,
            remaining: lvl,
        },
        death_saves: {
            successes: 0,
            failures: 0,
        },
        abilities: { ...finalAbilities.value },
        saving_throws: [...cls.savingThrows],
        skills: { ...combinedSkillsMap.value },
        weapon_masteries: [...selectedWeaponMasteries.value],
        weapons_list:
            equipmentChoice.value === 'package_a'
                ? [...startingWeapons.value]
                : [],
        armors_list:
            equipmentChoice.value === 'package_a'
                ? [...startingArmors.value]
                : [],
        spells_list: [...startingSpells.value],
        inventory_list:
            equipmentChoice.value === 'package_a'
                ? [...startingInventory.value]
                : [],
        features_list: featuresList,
        traits_list: traitsList,
        feats_list: featsList,
        languages_list: languagesList,
        weapons_attacks: '',
        armor_equipped:
            equipmentChoice.value === 'package_a'
                ? startingArmors.value.map((a) => a.name).join(', ')
                : '',
        features_traits: generatedFeaturesText.value,
        proficiencies: generatedProficienciesText.value,
        equipment:
            equipmentChoice.value === 'package_a'
                ? `Class Gear: ${cls.startingEquipment.packageALabel}\nBackground Gear: ${currentBackgroundDef.value.startingGearSummary}`
                : 'Purchased with Starting Gold',
        coins: {
            cp: 0,
            sp: 0,
            ep: 0,
            gp: startingGold.value,
            pp: 0,
        },
        spellcasting_ability: cls.spellcastingAbility,
        spells: '',
        notes: '',
    };

    router.post(
        CharacterController.store.url(),
        {
            name: charName.value.trim(),
            avatar_url: avatarUrl.value.trim() || null,
            system_id: systemId.value || null,
            campaign_id: campaignId.value || null,
            species: speciesDisplay,
            class: cls.name,
            background: selectedBackgroundName.value,
            level: lvl,
            sheet_data: sheetData as Record<string, any>,
        },
        {
            onError: (errs) => {
                validationErrors.value = errs;
                if (
                    errs.name ||
                    errs.avatar_url ||
                    errs.campaign_id ||
                    errs.system_id
                ) {
                    currentStep.value = 1;
                }
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
}
</script>

<template>
    <section
        class="rounded-lg bg-white p-4 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-6 dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
    >
        <!-- Top Header & Live Character Summary Banner -->
        <div
            class="flex flex-col justify-between gap-4 border-b border-[#e3e3e0] pb-4 sm:flex-row sm:items-center dark:border-[#3E3E3A]"
        >
            <div class="flex items-center gap-3.5">
                <img
                    v-if="avatarUrl.trim()"
                    :src="avatarUrl.trim()"
                    alt="Preview"
                    class="h-12 w-12 shrink-0 rounded-full border border-[#e3e3e0] object-cover dark:border-[#3E3E3A]"
                />
                <div
                    v-else
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] text-base font-bold uppercase dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                >
                    {{ charName.trim() ? charName.trim().charAt(0) : '?' }}
                </div>

                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-base font-semibold sm:text-lg">
                            {{ charName.trim() || 'New Character Builder' }}
                        </h2>
                        <span
                            class="rounded-xs bg-[#1b1b18] px-2 py-0.5 text-[11px] font-semibold text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]"
                        >
                            Lvl {{ level }} {{ selectedSpeciesName }}
                            {{ selectedClassName }}
                        </span>
                        <span
                            class="rounded-xs border border-[#e3e3e0] px-2 py-0.5 text-[11px] font-medium text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                        >
                            {{ selectedBackgroundName }} &middot;
                            {{ selectedOriginFeatName }}
                        </span>
                    </div>
                    <div
                        class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                    >
                        <span>
                            HP:
                            <strong
                                class="text-[#1b1b18] dark:text-[#EDEDEC]"
                                >{{ effectiveMaxHp }}</strong
                            >
                        </span>
                        <span>&middot;</span>
                        <span>
                            AC:
                            <strong
                                class="text-[#1b1b18] dark:text-[#EDEDEC]"
                                >{{ calculatedAc }}</strong
                            >
                        </span>
                        <span>&middot;</span>
                        <span>
                            Init:
                            <strong
                                class="text-[#1b1b18] dark:text-[#EDEDEC]"
                                >{{
                                    formatSigned(calculatedInitiative)
                                }}</strong
                            >
                        </span>
                        <span>&middot;</span>
                        <span>
                            Speed:
                            <strong class="text-[#1b1b18] dark:text-[#EDEDEC]"
                                >{{ effectiveBaseSpeed }} ft</strong
                            >
                        </span>
                        <span>&middot;</span>
                        <span>
                            Prof:
                            <strong class="text-[#1b1b18] dark:text-[#EDEDEC]"
                                >+{{ proficiencyBonus }}</strong
                            >
                        </span>
                    </div>
                </div>
            </div>

            <button
                type="button"
                class="cursor-pointer self-end rounded-xs border border-[#e3e3e0] px-3 py-1 text-xs font-medium text-[#706f6c] transition-colors hover:border-[#1b1b18] hover:text-[#1b1b18] sm:self-center dark:border-[#3E3E3A] dark:text-[#A1A09A] dark:hover:border-[#EDEDEC] dark:hover:text-[#EDEDEC]"
                @click="emit('close')"
            >
                Close Builder
            </button>
        </div>

        <!-- 6-Step Progress Bar Navigation -->
        <nav
            aria-label="Character Builder Steps"
            class="mt-4 grid grid-cols-2 gap-1.5 sm:grid-cols-3 lg:grid-cols-6"
        >
            <button
                v-for="s in steps"
                :key="s.step"
                type="button"
                class="flex cursor-pointer flex-col rounded-sm border px-3 py-2 text-left transition-all"
                :class="
                    currentStep === s.step
                        ? 'border-[#1b1b18] bg-[#1b1b18] text-white dark:border-[#EDEDEC] dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                        : currentStep > s.step
                          ? 'border-[#1b1b18]/40 bg-[#FDFDFC] text-[#1b1b18] hover:border-[#1b1b18] dark:border-[#EDEDEC]/40 dark:bg-[#0a0a0a] dark:text-[#EDEDEC]'
                          : 'border-[#e3e3e0] bg-[#FDFDFC] text-[#706f6c] hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#A1A09A]'
                "
                @click="goToStep(s.step)"
            >
                <span class="text-xs font-semibold">{{ s.title }}</span>
                <span
                    class="truncate text-[10px]"
                    :class="
                        currentStep === s.step
                            ? 'text-white/80 dark:text-[#1b1b18]/80'
                            : 'text-[#706f6c] dark:text-[#A1A09A]'
                    "
                >
                    {{ s.subtitle }}
                </span>
            </button>
        </nav>

        <!-- STEP 1: IDENTITY & BASICS -->
        <div v-if="currentStep === 1" class="mt-6 space-y-5">
            <div>
                <h3
                    class="text-sm font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                >
                    Step 1: Character Identity &amp; Campaign Setup
                </h3>
                <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                    Start by naming your adventurer, choosing their starting
                    level, and linking them to a campaign if desired.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label
                        for="wiz_char_name"
                        class="block text-sm font-medium"
                    >
                        Character Name <span class="text-[#f53003]">*</span>
                    </label>
                    <input
                        id="wiz_char_name"
                        v-model="charName"
                        v-focus
                        type="text"
                        required
                        placeholder="e.g. Seraphina Vale, Thorne Ironfist"
                        class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3.5 py-2 text-sm text-[#1b1b18] placeholder-[#706f6c]/60 focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC]"
                        @keydown.enter.prevent="nextStep"
                    />
                    <p
                        v-if="validationErrors.name"
                        class="mt-1.5 text-xs text-[#f53003] dark:text-[#FF4433]"
                    >
                        {{ validationErrors.name }}
                    </p>
                </div>

                <div>
                    <label
                        for="wiz_avatar_url"
                        class="block text-sm font-medium"
                    >
                        Portrait / Avatar URL
                        <span
                            class="font-normal text-[#706f6c] dark:text-[#A1A09A]"
                            >(optional)</span
                        >
                    </label>
                    <input
                        id="wiz_avatar_url"
                        v-model="avatarUrl"
                        type="url"
                        placeholder="https://example.com/portrait.png"
                        class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3.5 py-2 text-sm text-[#1b1b18] placeholder-[#706f6c]/60 focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC]"
                    />
                    <p
                        v-if="validationErrors.avatar_url"
                        class="mt-1.5 text-xs text-[#f53003] dark:text-[#FF4433]"
                    >
                        {{ validationErrors.avatar_url }}
                    </p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="wiz_level" class="block text-sm font-medium">
                        Starting Level (1–20)
                    </label>
                    <input
                        id="wiz_level"
                        v-model.number="level"
                        type="number"
                        min="1"
                        max="20"
                        class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3.5 py-2 text-sm font-semibold text-[#1b1b18] focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC]"
                    />
                    <p
                        class="mt-1 text-[11px] text-[#706f6c] dark:text-[#A1A09A]"
                    >
                        Proficiency Bonus: +{{ proficiencyBonus }}
                    </p>
                </div>

                <div v-if="!props.fixedSystemId && props.systems.length > 0">
                    <label for="wiz_system" class="block text-sm font-medium">
                        Game System
                    </label>
                    <select
                        id="wiz_system"
                        v-model="systemId"
                        class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3.5 py-2 text-sm text-[#1b1b18] focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC]"
                    >
                        <option
                            v-for="sys in props.systems"
                            :key="sys.id"
                            :value="sys.id"
                        >
                            {{ sys.name }}
                        </option>
                    </select>
                </div>

                <div v-if="!props.fixedCampaignId">
                    <label for="wiz_campaign" class="block text-sm font-medium">
                        Campaign
                        <span
                            class="font-normal text-[#706f6c] dark:text-[#A1A09A]"
                            >(optional)</span
                        >
                    </label>
                    <select
                        id="wiz_campaign"
                        v-model="campaignId"
                        class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3.5 py-2 text-sm text-[#1b1b18] focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC]"
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
                <div v-else>
                    <label class="block text-sm font-medium"
                        >Assigned Campaign</label
                    >
                    <div
                        class="mt-1.5 rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3.5 py-2 text-sm font-semibold dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                    >
                        {{ props.fixedCampaignName || 'Current Campaign' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 2: CLASS & FEATURES -->
        <div v-if="currentStep === 2" class="mt-6 space-y-6">
            <div>
                <h3
                    class="text-sm font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                >
                    Step 2: Choose Your Class &amp; Class Features
                </h3>
                <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                    Your class defines your Hit Die, Saving Throw proficiencies,
                    combat training, and signature features.
                </p>
            </div>

            <!-- 12 Class Cards Grid -->
            <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 lg:grid-cols-4">
                <button
                    v-for="cls in DND_CLASSES"
                    :key="cls.name"
                    type="button"
                    class="flex cursor-pointer flex-col justify-between rounded-sm border p-3 text-left transition-all"
                    :class="
                        selectedClassName === cls.name
                            ? 'border-[#1b1b18] bg-[#1b1b18]/5 shadow-[inset_0px_0px_0px_1px_#1b1b18] dark:border-[#EDEDEC] dark:bg-[#EDEDEC]/10 dark:shadow-[inset_0px_0px_0px_1px_#EDEDEC]'
                            : 'border-[#e3e3e0] bg-[#FDFDFC] hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:hover:border-[#EDEDEC]'
                    "
                    @click="selectedClassName = cls.name"
                >
                    <div>
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-sm font-bold">{{
                                cls.name
                            }}</span>
                            <span
                                class="rounded-xs border border-[#e3e3e0] px-1.5 py-0.5 font-mono text-[10px] font-semibold dark:border-[#3E3E3A]"
                            >
                                d{{ cls.hitDie }}
                            </span>
                        </div>
                        <p
                            class="mt-1 line-clamp-2 text-[11px] text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            {{ cls.tagline }}
                        </p>
                    </div>
                    <div
                        class="mt-2.5 border-t border-[#e3e3e0]/60 pt-1.5 text-[10px] font-medium text-[#706f6c] dark:border-[#3E3E3A]/60 dark:text-[#A1A09A]"
                    >
                        Primary: {{ cls.primaryAbility }}
                    </div>
                </button>
            </div>

            <!-- Selected Class Details & Feature Choices -->
            <div
                class="rounded-md border border-[#e3e3e0] bg-[#FDFDFC] p-4 sm:p-5 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
            >
                <div
                    class="flex flex-wrap items-start justify-between gap-3 border-b border-[#e3e3e0] pb-3.5 dark:border-[#3E3E3A]"
                >
                    <div>
                        <h4 class="text-base font-bold">
                            {{ currentClassDef.name }}
                        </h4>
                        <p
                            class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            {{ currentClassDef.description }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-1.5 text-xs">
                        <span
                            class="rounded-xs bg-[#1b1b18] px-2.5 py-1 font-semibold text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]"
                        >
                            Hit Die: d{{ currentClassDef.hitDie }}
                        </span>
                        <span
                            class="rounded-xs border border-[#e3e3e0] px-2.5 py-1 font-medium dark:border-[#3E3E3A]"
                        >
                            Saves:
                            {{
                                currentClassDef.savingThrows
                                    .map((s) => s.toUpperCase())
                                    .join(', ')
                            }}
                        </span>
                    </div>
                </div>

                <!-- Subclass Picker (highlighted for Level 3+, optional for Level 1-2) -->
                <div class="mt-4">
                    <label
                        class="block text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                    >
                        {{ currentClassDef.name }} Subclass
                        <span class="font-normal normal-case">
                            ({{
                                level >= 3
                                    ? 'Granted at Level 3+'
                                    : 'Optional preview for future levels'
                            }})
                        </span>
                    </label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button
                            v-for="sub in currentClassDef.subclasses"
                            :key="sub"
                            type="button"
                            class="cursor-pointer rounded-xs border px-3 py-1.5 text-xs font-medium transition-colors"
                            :class="
                                selectedSubclass === sub
                                    ? 'border-[#1b1b18] bg-[#1b1b18] text-white dark:border-[#EDEDEC] dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                    : 'border-[#e3e3e0] bg-white hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#161615] dark:hover:border-[#EDEDEC]'
                            "
                            @click="
                                selectedSubclass =
                                    selectedSubclass === sub ? '' : sub
                            "
                        >
                            {{ sub }}
                        </button>
                        <input
                            v-model="selectedSubclass"
                            type="text"
                            placeholder="Or type custom subclass..."
                            class="rounded-xs border border-[#e3e3e0] bg-white px-2.5 py-1 text-xs focus:border-[#1b1b18] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#161615]"
                        />
                    </div>
                </div>

                <!-- Class Features & Specific Feature Option Pickers -->
                <div class="mt-5 space-y-3">
                    <h5
                        class="text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                    >
                        Class Features &amp; Specializations
                    </h5>

                    <div
                        v-for="feat in currentClassDef.features"
                        :key="feat.id"
                        class="rounded-sm border border-[#e3e3e0] bg-white p-3.5 dark:border-[#3E3E3A] dark:bg-[#161615]"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <label
                                class="flex cursor-pointer items-start gap-2.5"
                            >
                                <input
                                    v-model="enabledClassFeatures"
                                    type="checkbox"
                                    :value="feat.id"
                                    class="mt-0.5 rounded text-[#1b1b18]"
                                />
                                <div>
                                    <span class="text-sm font-semibold">{{
                                        feat.name
                                    }}</span>
                                    <p
                                        class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                                    >
                                        {{ feat.description }}
                                    </p>
                                </div>
                            </label>
                        </div>

                        <!-- Sub-options (e.g. Fighting Style, Divine Order, Primal Order, Eldritch Invocations) -->
                        <div
                            v-if="
                                feat.choices &&
                                enabledClassFeatures.includes(feat.id)
                            "
                            class="mt-3 border-t border-[#e3e3e0] pt-3 dark:border-[#3E3E3A]"
                        >
                            <span
                                class="block text-xs font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"
                            >
                                {{ feat.choices.label }}:
                            </span>
                            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                <button
                                    v-for="opt in feat.choices.options"
                                    :key="opt.name"
                                    type="button"
                                    class="cursor-pointer rounded-xs border p-2.5 text-left transition-all"
                                    :class="
                                        classFeatureChoices[feat.id] ===
                                        opt.name
                                            ? 'border-[#1b1b18] bg-[#1b1b18] text-white dark:border-[#EDEDEC] dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                            : 'border-[#e3e3e0] bg-[#FDFDFC] hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a]'
                                    "
                                    @click="
                                        classFeatureChoices[feat.id] = opt.name
                                    "
                                >
                                    <div class="text-xs font-bold">
                                        {{ opt.name }}
                                    </div>
                                    <div
                                        class="mt-0.5 text-[11px]"
                                        :class="
                                            classFeatureChoices[feat.id] ===
                                            opt.name
                                                ? 'text-white/85 dark:text-[#1b1b18]/85'
                                                : 'text-[#706f6c] dark:text-[#A1A09A]'
                                        "
                                    >
                                        {{ opt.summary }}
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Class Skill Proficiencies Picker -->
                <div
                    class="mt-5 border-t border-[#e3e3e0] pt-4 dark:border-[#3E3E3A]"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-2"
                    >
                        <div>
                            <h5
                                class="text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                            >
                                Class Skill Proficiencies (Choose
                                {{ currentClassDef.skillChoicesCount }})
                            </h5>
                            <p
                                class="text-[11px] text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Selected: {{ classSkills.length }} /
                                {{ currentClassDef.skillChoicesCount }}
                            </p>
                        </div>
                        <label
                            class="flex cursor-pointer items-center gap-1.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            <input
                                v-model="allowAnyClassSkill"
                                type="checkbox"
                                class="rounded text-[#1b1b18]"
                            />
                            <span
                                >Unlock all 18 skills (freeform override)</span
                            >
                        </label>
                    </div>

                    <div class="mt-2.5 flex flex-wrap gap-1.5">
                        <button
                            v-for="sk in SKILL_LIST.filter(
                                (s) =>
                                    allowAnyClassSkill ||
                                    currentClassDef.availableSkills.includes(
                                        s.key,
                                    ),
                            )"
                            :key="sk.key"
                            type="button"
                            class="cursor-pointer rounded-xs border px-2.5 py-1 text-xs font-medium transition-colors"
                            :class="
                                classSkills.includes(sk.key)
                                    ? 'border-[#1b1b18] bg-[#1b1b18] text-white dark:border-[#EDEDEC] dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                    : 'border-[#e3e3e0] bg-white hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#161615]'
                            "
                            @click="toggleClassSkill(sk.key)"
                        >
                            {{ sk.label }}
                            <span class="opacity-70"
                                >({{ sk.ability.toUpperCase() }})</span
                            >
                        </button>
                    </div>
                </div>

                <!-- Rogue Expertise Picker -->
                <div
                    v-if="selectedClassName === 'Rogue'"
                    class="mt-4 border-t border-[#e3e3e0] pt-4 dark:border-[#3E3E3A]"
                >
                    <h5
                        class="text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                    >
                        Rogue Expertise (Choose 2 Proficient Skills to Double
                        Proficiency Bonus)
                    </h5>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <button
                            v-for="sk in SKILL_LIST.filter(
                                (s) =>
                                    classSkills.includes(s.key) ||
                                    backgroundSkills.includes(s.key),
                            )"
                            :key="sk.key"
                            type="button"
                            class="cursor-pointer rounded-xs border px-2.5 py-1 text-xs font-semibold transition-colors"
                            :class="
                                rogueExpertiseSkills.includes(sk.key)
                                    ? 'border-[#f53003] bg-[#f53003] text-white'
                                    : 'border-[#e3e3e0] bg-white hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#161615]'
                            "
                            @click="toggleRogueExpertise(sk.key)"
                        >
                            {{ sk.label }} (EXP)
                        </button>
                    </div>
                </div>

                <!-- Weapon Masteries Picker -->
                <div
                    v-if="currentClassDef.weaponMasteryCount > 0"
                    class="mt-4 border-t border-[#e3e3e0] pt-4 dark:border-[#3E3E3A]"
                >
                    <h5
                        class="text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                    >
                        2024 Weapon Masteries (Choose
                        {{ currentClassDef.weaponMasteryCount }})
                    </h5>
                    <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                        <button
                            v-for="wm in WEAPON_MASTERY_OPTIONS"
                            :key="wm.name"
                            type="button"
                            class="cursor-pointer rounded-xs border p-2 text-left transition-colors"
                            :class="
                                selectedWeaponMasteries.includes(wm.name)
                                    ? 'border-[#1b1b18] bg-[#1b1b18] text-white dark:border-[#EDEDEC] dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                    : 'border-[#e3e3e0] bg-white hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#161615]'
                            "
                            @click="toggleWeaponMastery(wm.name)"
                        >
                            <div class="text-xs font-bold">{{ wm.name }}</div>
                            <div
                                class="mt-0.5 line-clamp-2 text-[10px]"
                                :class="
                                    selectedWeaponMasteries.includes(wm.name)
                                        ? 'text-white/80 dark:text-[#1b1b18]/80'
                                        : 'text-[#706f6c] dark:text-[#A1A09A]'
                                "
                            >
                                {{ wm.summary }}
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 3: BACKGROUND, ORIGIN FEAT & ABILITY BONUSES -->
        <div v-if="currentStep === 3" class="mt-6 space-y-6">
            <div>
                <h3
                    class="text-sm font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                >
                    Step 3: Choose Your Background, Origin Feat &amp; Ability
                    Bonuses
                </h3>
                <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                    In 2024 rules, your Background grants an Origin Feat, two
                    Skill Proficiencies, a Background Feature, a Tool
                    Proficiency, and your Ability Score Increases (+2/+1 or
                    +1/+1/+1). Every default is pre-selected for you and can be
                    freely customized below.
                </p>
            </div>

            <!-- 16 Backgrounds Grid -->
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                <button
                    v-for="bg in DND_BACKGROUNDS"
                    :key="bg.name"
                    type="button"
                    class="flex cursor-pointer flex-col justify-between rounded-sm border p-2.5 text-left transition-all"
                    :class="
                        selectedBackgroundName === bg.name
                            ? 'border-[#1b1b18] bg-[#1b1b18]/5 shadow-[inset_0px_0px_0px_1px_#1b1b18] dark:border-[#EDEDEC] dark:bg-[#EDEDEC]/10 dark:shadow-[inset_0px_0px_0px_1px_#EDEDEC]'
                            : 'border-[#e3e3e0] bg-[#FDFDFC] hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:hover:border-[#EDEDEC]'
                    "
                    @click="selectedBackgroundName = bg.name"
                >
                    <div>
                        <div class="text-xs font-bold">{{ bg.name }}</div>
                        <div
                            class="mt-0.5 text-[10px] font-medium text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Feat: {{ bg.originFeat }}
                        </div>
                    </div>
                    <div
                        class="mt-1.5 text-[10px] text-[#706f6c] uppercase dark:text-[#A1A09A]"
                    >
                        {{ bg.abilities.join(' / ') }}
                    </div>
                </button>
            </div>

            <!-- Background Details & Origin Feat Picker -->
            <div
                class="rounded-md border border-[#e3e3e0] bg-[#FDFDFC] p-4 sm:p-5 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
            >
                <div
                    class="border-b border-[#e3e3e0] pb-3.5 dark:border-[#3E3E3A]"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-2"
                    >
                        <h4 class="text-base font-bold">
                            {{ currentBackgroundDef.name }} Background
                        </h4>
                        <span
                            class="rounded-xs border border-[#e3e3e0] px-2 py-0.5 text-xs font-medium dark:border-[#3E3E3A]"
                        >
                            Recommended Abilities:
                            {{
                                currentBackgroundDef.abilities
                                    .map((a) => a.toUpperCase())
                                    .join(', ')
                            }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                        {{ currentBackgroundDef.description }}
                    </p>
                </div>

                <!-- 1. Pick Background Feature / Specialty -->
                <div class="mt-4">
                    <h5
                        class="text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                    >
                        Pick Background Feature / Specialty
                    </h5>
                    <div class="mt-2 grid gap-2.5 sm:grid-cols-2">
                        <button
                            v-for="bf in currentBackgroundDef.features"
                            :key="bf.name"
                            type="button"
                            class="cursor-pointer rounded-sm border p-3 text-left transition-all"
                            :class="
                                selectedBackgroundFeatureName === bf.name
                                    ? 'border-[#1b1b18] bg-[#1b1b18] text-white dark:border-[#EDEDEC] dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                    : 'border-[#e3e3e0] bg-white hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#161615]'
                            "
                            @click="selectedBackgroundFeatureName = bf.name"
                        >
                            <div class="text-xs font-bold">{{ bf.name }}</div>
                            <p
                                class="mt-1 text-[11px]"
                                :class="
                                    selectedBackgroundFeatureName === bf.name
                                        ? 'text-white/85 dark:text-[#1b1b18]/85'
                                        : 'text-[#706f6c] dark:text-[#A1A09A]'
                                "
                            >
                                {{ bf.description }}
                            </p>
                        </button>
                    </div>
                </div>

                <!-- 2. Origin Feat Selection & Benefits -->
                <div
                    class="mt-5 border-t border-[#e3e3e0] pt-4 dark:border-[#3E3E3A]"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-2"
                    >
                        <div>
                            <h5
                                class="text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                            >
                                Origin Feat (Default for
                                {{ currentBackgroundDef.name }}:
                                {{ currentBackgroundDef.originFeat }})
                            </h5>
                            <p
                                class="text-[11px] text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Keep the background’s default Origin Feat or
                                click any Origin Feat below to swap.
                            </p>
                        </div>
                        <button
                            v-if="
                                selectedOriginFeatName !==
                                currentBackgroundDef.originFeat
                            "
                            type="button"
                            class="cursor-pointer text-xs font-medium underline"
                            @click="
                                selectedOriginFeatName =
                                    currentBackgroundDef.originFeat
                            "
                        >
                            Reset to {{ currentBackgroundDef.originFeat }}
                        </button>
                    </div>

                    <div class="mt-2.5 flex flex-wrap gap-1.5">
                        <button
                            v-for="feat in ORIGIN_FEATS"
                            :key="feat.name"
                            type="button"
                            class="cursor-pointer rounded-xs border px-2.5 py-1 text-xs font-medium transition-colors"
                            :class="
                                selectedOriginFeatName === feat.name
                                    ? 'border-[#1b1b18] bg-[#1b1b18] text-white dark:border-[#EDEDEC] dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                    : 'border-[#e3e3e0] bg-white hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#161615]'
                            "
                            @click="selectedOriginFeatName = feat.name"
                        >
                            {{ feat.name }}
                            <span
                                v-if="
                                    feat.name ===
                                    currentBackgroundDef.originFeat
                                "
                                class="ml-1 text-[10px] opacity-75"
                            >
                                (Default)
                            </span>
                        </button>
                    </div>

                    <!-- Origin Feat Benefit Card -->
                    <div
                        class="mt-3 rounded-sm border border-[#e3e3e0] bg-white p-3.5 dark:border-[#3E3E3A] dark:bg-[#161615]"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold">{{
                                currentOriginFeatDef.name
                            }}</span>
                            <span
                                class="rounded-xs bg-[#1b1b18]/10 px-2 py-0.5 text-[10px] font-semibold uppercase dark:bg-[#EDEDEC]/10"
                            >
                                Origin Feat
                            </span>
                        </div>
                        <p
                            class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            {{ currentOriginFeatDef.description }}
                        </p>
                        <ul class="mt-2 space-y-1 text-xs">
                            <li
                                v-for="ben in currentOriginFeatDef.benefits"
                                :key="ben"
                                class="flex items-start gap-1.5"
                            >
                                <span class="font-bold">&bull;</span>
                                <span>{{ ben }}</span>
                            </li>
                        </ul>

                        <!-- If Skilled feat is selected, pick 3 extra skills -->
                        <div
                            v-if="selectedOriginFeatName === 'Skilled'"
                            class="mt-3 border-t border-[#e3e3e0] pt-3 dark:border-[#3E3E3A]"
                        >
                            <span class="block text-xs font-semibold">
                                Skilled Feat — Pick 3 Extra Skills ({{
                                    skilledExtraSkills.length
                                }}
                                / 3):
                            </span>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                <button
                                    v-for="sk in SKILL_LIST"
                                    :key="sk.key"
                                    type="button"
                                    class="cursor-pointer rounded-xs border px-2 py-1 text-[11px] font-medium"
                                    :class="
                                        skilledExtraSkills.includes(sk.key)
                                            ? 'border-[#1b1b18] bg-[#1b1b18] text-white dark:border-[#EDEDEC] dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                            : 'border-[#e3e3e0] bg-[#FDFDFC] dark:border-[#3E3E3A] dark:bg-[#0a0a0a]'
                                    "
                                    @click="toggleSkilledExtraSkill(sk.key)"
                                >
                                    {{ sk.label }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Background Skills & Tool Proficiency -->
                <div
                    class="mt-5 grid gap-4 border-t border-[#e3e3e0] pt-4 sm:grid-cols-3 dark:border-[#3E3E3A]"
                >
                    <div>
                        <label
                            class="block text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Background Skill 1
                        </label>
                        <select
                            v-model="backgroundSkills[0]"
                            class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-white px-3 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#161615]"
                        >
                            <option
                                v-for="sk in SKILL_LIST"
                                :key="sk.key"
                                :value="sk.key"
                            >
                                {{ sk.label }} ({{ sk.ability.toUpperCase() }})
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Background Skill 2
                        </label>
                        <select
                            v-model="backgroundSkills[1]"
                            class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-white px-3 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#161615]"
                        >
                            <option
                                v-for="sk in SKILL_LIST"
                                :key="sk.key"
                                :value="sk.key"
                            >
                                {{ sk.label }} ({{ sk.ability.toUpperCase() }})
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Tool Proficiency
                        </label>
                        <input
                            v-model="toolProficiency"
                            type="text"
                            class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-white px-3 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#161615]"
                        />
                    </div>
                </div>

                <!-- 4. Background Ability Score Bonuses (+2/+1 or +1/+1/+1) -->
                <div
                    class="mt-5 border-t border-[#e3e3e0] pt-4 dark:border-[#3E3E3A]"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-2"
                    >
                        <div>
                            <h5
                                class="text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                            >
                                Background Ability Score Increases
                            </h5>
                            <p
                                class="text-[11px] text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Increase one ability by +2 and another by +1, or
                                increase three abilities by +1 each.
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <div
                                class="inline-flex rounded-xs border border-[#e3e3e0] bg-white p-0.5 dark:border-[#3E3E3A] dark:bg-[#161615]"
                            >
                                <button
                                    type="button"
                                    class="cursor-pointer rounded-xs px-2.5 py-1 text-xs font-semibold"
                                    :class="
                                        bonusDistributionMode === '2_1'
                                            ? 'bg-[#1b1b18] text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                            : 'text-[#706f6c] dark:text-[#A1A09A]'
                                    "
                                    @click="bonusDistributionMode = '2_1'"
                                >
                                    +2 / +1
                                </button>
                                <button
                                    type="button"
                                    class="cursor-pointer rounded-xs px-2.5 py-1 text-xs font-semibold"
                                    :class="
                                        bonusDistributionMode === '1_1_1'
                                            ? 'bg-[#1b1b18] text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                            : 'text-[#706f6c] dark:text-[#A1A09A]'
                                    "
                                    @click="bonusDistributionMode = '1_1_1'"
                                >
                                    +1 / +1 / +1
                                </button>
                            </div>

                            <label
                                class="flex cursor-pointer items-center gap-1.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                <input
                                    v-model="allowAnyBackgroundAbility"
                                    type="checkbox"
                                    class="rounded text-[#1b1b18]"
                                />
                                <span>Allow any ability</span>
                            </label>
                        </div>
                    </div>

                    <div
                        v-if="bonusDistributionMode === '2_1'"
                        class="mt-3 grid gap-4 sm:grid-cols-2"
                    >
                        <div>
                            <span class="block text-xs font-medium"
                                >+2 Bonus Ability:</span
                            >
                            <div class="mt-1.5 flex flex-wrap gap-1.5">
                                <button
                                    v-for="ab in ABILITY_INFO.filter(
                                        (a) =>
                                            allowAnyBackgroundAbility ||
                                            currentBackgroundDef.abilities.includes(
                                                a.key,
                                            ),
                                    )"
                                    :key="ab.key"
                                    type="button"
                                    class="cursor-pointer rounded-xs border px-3 py-1.5 text-xs font-semibold"
                                    :class="
                                        bonusPlusTwoAbility === ab.key
                                            ? 'border-[#1b1b18] bg-[#1b1b18] text-white dark:border-[#EDEDEC] dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                            : 'border-[#e3e3e0] bg-white dark:border-[#3E3E3A] dark:bg-[#161615]'
                                    "
                                    @click="
                                        bonusPlusTwoAbility = ab.key;
                                        if (bonusPlusOneAbility === ab.key) {
                                            bonusPlusOneAbility =
                                                currentBackgroundDef.abilities.find(
                                                    (k) => k !== ab.key,
                                                ) ?? 'con';
                                        }
                                    "
                                >
                                    {{ ab.short }} (+2)
                                </button>
                            </div>
                        </div>

                        <div>
                            <span class="block text-xs font-medium"
                                >+1 Bonus Ability:</span
                            >
                            <div class="mt-1.5 flex flex-wrap gap-1.5">
                                <button
                                    v-for="ab in ABILITY_INFO.filter(
                                        (a) =>
                                            (allowAnyBackgroundAbility ||
                                                currentBackgroundDef.abilities.includes(
                                                    a.key,
                                                )) &&
                                            a.key !== bonusPlusTwoAbility,
                                    )"
                                    :key="ab.key"
                                    type="button"
                                    class="cursor-pointer rounded-xs border px-3 py-1.5 text-xs font-semibold"
                                    :class="
                                        bonusPlusOneAbility === ab.key
                                            ? 'border-[#1b1b18] bg-[#1b1b18] text-white dark:border-[#EDEDEC] dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                            : 'border-[#e3e3e0] bg-white dark:border-[#3E3E3A] dark:bg-[#161615]'
                                    "
                                    @click="bonusPlusOneAbility = ab.key"
                                >
                                    {{ ab.short }} (+1)
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-else class="mt-3">
                        <span class="block text-xs font-medium">
                            Select 3 Abilities for +1 Bonus Each ({{
                                bonusThreeAbilities.length
                            }}
                            / 3):
                        </span>
                        <div class="mt-1.5 flex flex-wrap gap-1.5">
                            <button
                                v-for="ab in ABILITY_INFO.filter(
                                    (a) =>
                                        allowAnyBackgroundAbility ||
                                        currentBackgroundDef.abilities.includes(
                                            a.key,
                                        ),
                                )"
                                :key="ab.key"
                                type="button"
                                class="cursor-pointer rounded-xs border px-3 py-1.5 text-xs font-semibold"
                                :class="
                                    bonusThreeAbilities.includes(ab.key)
                                        ? 'border-[#1b1b18] bg-[#1b1b18] text-white dark:border-[#EDEDEC] dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                        : 'border-[#e3e3e0] bg-white dark:border-[#3E3E3A] dark:bg-[#161615]'
                                "
                                @click="toggleThreeAbilityBonus(ab.key)"
                            >
                                {{ ab.short }} (+1)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 4: SPECIES, LINEAGE & TRAITS -->
        <div v-if="currentStep === 4" class="mt-6 space-y-6">
            <div>
                <h3
                    class="text-sm font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                >
                    Step 4: Choose Your Species &amp; Lineage Traits
                </h3>
                <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                    Your species determines your size, speed, darkvision, and
                    innate ancestral traits.
                </p>
            </div>

            <!-- 10 Species Cards Grid -->
            <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 lg:grid-cols-5">
                <button
                    v-for="sp in DND_SPECIES"
                    :key="sp.name"
                    type="button"
                    class="flex cursor-pointer flex-col justify-between rounded-sm border p-3 text-left transition-all"
                    :class="
                        selectedSpeciesName === sp.name
                            ? 'border-[#1b1b18] bg-[#1b1b18]/5 shadow-[inset_0px_0px_0px_1px_#1b1b18] dark:border-[#EDEDEC] dark:bg-[#EDEDEC]/10 dark:shadow-[inset_0px_0px_0px_1px_#EDEDEC]'
                            : 'border-[#e3e3e0] bg-[#FDFDFC] hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:hover:border-[#EDEDEC]'
                    "
                    @click="selectedSpeciesName = sp.name"
                >
                    <div>
                        <div class="text-sm font-bold">{{ sp.name }}</div>
                        <p
                            class="mt-1 line-clamp-2 text-[10px] text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            {{ sp.summary }}
                        </p>
                    </div>
                    <div
                        class="mt-2 text-[10px] font-semibold text-[#706f6c] dark:text-[#A1A09A]"
                    >
                        {{ sp.speed }} ft &middot;
                        {{ sp.sizeOptions.join('/') }}
                    </div>
                </button>
            </div>

            <!-- Selected Species Details -->
            <div
                class="rounded-md border border-[#e3e3e0] bg-[#FDFDFC] p-4 sm:p-5 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
            >
                <div
                    class="flex flex-wrap items-start justify-between gap-3 border-b border-[#e3e3e0] pb-3.5 dark:border-[#3E3E3A]"
                >
                    <div>
                        <h4 class="text-base font-bold">
                            {{ currentSpeciesDef.name }}
                        </h4>
                        <p
                            class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            {{ currentSpeciesDef.description }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span
                            class="rounded-xs bg-[#1b1b18] px-2.5 py-1 text-xs font-semibold text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]"
                        >
                            Speed: {{ effectiveBaseSpeed }} ft
                        </span>
                    </div>
                </div>

                <!-- Size Choice (if Medium or Small allowed) -->
                <div class="mt-4 flex flex-wrap items-center gap-4">
                    <div>
                        <span
                            class="block text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Size
                        </span>
                        <div class="mt-1.5 flex gap-2">
                            <button
                                v-for="sz in currentSpeciesDef.sizeOptions"
                                :key="sz"
                                type="button"
                                class="cursor-pointer rounded-xs border px-3 py-1 text-xs font-semibold"
                                :class="
                                    selectedSize === sz
                                        ? 'border-[#1b1b18] bg-[#1b1b18] text-white dark:border-[#EDEDEC] dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                        : 'border-[#e3e3e0] bg-white dark:border-[#3E3E3A] dark:bg-[#161615]'
                                "
                                @click="selectedSize = sz"
                            >
                                {{ sz }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Species Lineage / Ancestry Picker -->
                <div
                    v-if="
                        currentSpeciesDef.lineages &&
                        currentSpeciesDef.lineages.length > 0
                    "
                    class="mt-5 border-t border-[#e3e3e0] pt-4 dark:border-[#3E3E3A]"
                >
                    <h5
                        class="text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                    >
                        Choose {{ currentSpeciesDef.lineageLabel || 'Lineage' }}
                    </h5>
                    <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        <button
                            v-for="lin in currentSpeciesDef.lineages"
                            :key="lin.name"
                            type="button"
                            class="cursor-pointer rounded-sm border p-3 text-left transition-all"
                            :class="
                                selectedLineageName === lin.name
                                    ? 'border-[#1b1b18] bg-[#1b1b18] text-white dark:border-[#EDEDEC] dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                    : 'border-[#e3e3e0] bg-white hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#161615]'
                            "
                            @click="selectedLineageName = lin.name"
                        >
                            <div class="text-xs font-bold">{{ lin.name }}</div>
                            <p
                                class="mt-1 text-[11px]"
                                :class="
                                    selectedLineageName === lin.name
                                        ? 'text-white/85 dark:text-[#1b1b18]/85'
                                        : 'text-[#706f6c] dark:text-[#A1A09A]'
                                "
                            >
                                {{ lin.summary }}
                            </p>
                        </button>
                    </div>
                </div>

                <!-- Human Special Choices: Skillful (+1 Skill) & Versatile (+1 Origin Feat) -->
                <div
                    v-if="selectedSpeciesName === 'Human'"
                    class="mt-5 grid gap-4 border-t border-[#e3e3e0] pt-4 sm:grid-cols-2 dark:border-[#3E3E3A]"
                >
                    <div>
                        <label
                            class="block text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Human Skillful Trait (+1 Bonus Skill)
                        </label>
                        <select
                            v-model="humanExtraSkill"
                            class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-white px-3 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#161615]"
                        >
                            <option
                                v-for="sk in SKILL_LIST"
                                :key="sk.key"
                                :value="sk.key"
                            >
                                {{ sk.label }} ({{ sk.ability.toUpperCase() }})
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Human Versatile Trait (+1 Bonus Origin Feat)
                        </label>
                        <select
                            v-model="humanVersatileFeat"
                            class="mt-1.5 block w-full rounded-sm border border-[#e3e3e0] bg-white px-3 py-1.5 text-xs dark:border-[#3E3E3A] dark:bg-[#161615]"
                        >
                            <option
                                v-for="f in ORIGIN_FEATS"
                                :key="f.name"
                                :value="f.name"
                            >
                                {{ f.name }} — {{ f.summary }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Elf Keen Senses Skill Picker -->
                <div
                    v-if="selectedSpeciesName === 'Elf'"
                    class="mt-4 border-t border-[#e3e3e0] pt-4 dark:border-[#3E3E3A]"
                >
                    <label
                        class="block text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                    >
                        Elf Keen Senses Skill
                    </label>
                    <div class="mt-2 flex gap-2">
                        <button
                            v-for="sk in ['insight', 'perception', 'survival']"
                            :key="sk"
                            type="button"
                            class="cursor-pointer rounded-xs border px-3 py-1 text-xs font-semibold capitalize"
                            :class="
                                elfKeenSenseSkill === sk
                                    ? 'border-[#1b1b18] bg-[#1b1b18] text-white dark:border-[#EDEDEC] dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                    : 'border-[#e3e3e0] bg-white dark:border-[#3E3E3A] dark:bg-[#161615]'
                            "
                            @click="elfKeenSenseSkill = sk"
                        >
                            {{ sk }}
                        </button>
                    </div>
                </div>

                <!-- Species Traits List -->
                <div
                    class="mt-5 border-t border-[#e3e3e0] pt-4 dark:border-[#3E3E3A]"
                >
                    <h5
                        class="text-xs font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                    >
                        Species Traits Included
                    </h5>
                    <div class="mt-2.5 grid gap-2.5 sm:grid-cols-2">
                        <div
                            v-for="tr in currentSpeciesDef.traits"
                            :key="tr.name"
                            class="rounded-sm border border-[#e3e3e0] bg-white p-3 dark:border-[#3E3E3A] dark:bg-[#161615]"
                        >
                            <div class="text-xs font-bold">{{ tr.name }}</div>
                            <p
                                class="mt-0.5 text-[11px] text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                {{ tr.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 5: ABILITY SCORES & HIT POINTS -->
        <div v-if="currentStep === 5" class="mt-6 space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3
                        class="text-sm font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                    >
                        Step 5: Ability Scores &amp; Hit Points
                    </h3>
                    <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                        Generate your base scores using Standard Array, Point
                        Buy, or 4d6 Roll/Manual entry. Your Background bonuses
                        from Step 3 are automatically added.
                    </p>
                </div>

                <!-- Generation Method Selector -->
                <div class="flex flex-wrap items-center gap-2">
                    <div
                        class="inline-flex rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] p-0.5 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                    >
                        <button
                            type="button"
                            class="cursor-pointer rounded-xs px-3 py-1 text-xs font-semibold"
                            :class="
                                abilityMethod === 'standard'
                                    ? 'bg-[#1b1b18] text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                    : 'text-[#706f6c] dark:text-[#A1A09A]'
                            "
                            @click="abilityMethod = 'standard'"
                        >
                            Standard Array
                        </button>
                        <button
                            type="button"
                            class="cursor-pointer rounded-xs px-3 py-1 text-xs font-semibold"
                            :class="
                                abilityMethod === 'pointbuy'
                                    ? 'bg-[#1b1b18] text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                    : 'text-[#706f6c] dark:text-[#A1A09A]'
                            "
                            @click="abilityMethod = 'pointbuy'"
                        >
                            Point Buy ({{ pointBuyRemaining }} pts left)
                        </button>
                        <button
                            type="button"
                            class="cursor-pointer rounded-xs px-3 py-1 text-xs font-semibold"
                            :class="
                                abilityMethod === 'manual'
                                    ? 'bg-[#1b1b18] text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                                    : 'text-[#706f6c] dark:text-[#A1A09A]'
                            "
                            @click="abilityMethod = 'manual'"
                        >
                            Roll / Manual
                        </button>
                    </div>

                    <button
                        type="button"
                        class="cursor-pointer rounded-sm border border-[#e3e3e0] px-2.5 py-1 text-xs font-medium hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#EDEDEC]"
                        @click="applyClassRecommendedAbilities"
                    >
                        Auto-Recommend for {{ selectedClassName }}
                    </button>

                    <button
                        v-if="abilityMethod === 'manual'"
                        type="button"
                        class="cursor-pointer rounded-sm border border-[#1b1b18] bg-[#1b1b18] px-2.5 py-1 text-xs font-medium text-white dark:border-[#EDEDEC] dark:bg-[#EDEDEC] dark:text-[#1b1b18]"
                        @click="rollAllAbilities"
                    >
                        Roll All (4d6 Drop Lowest)
                    </button>
                </div>
            </div>

            <!-- 6 Ability Score Cards -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                <div
                    v-for="ab in ABILITY_INFO"
                    :key="ab.key"
                    class="flex flex-col justify-between rounded-md border border-[#e3e3e0] bg-[#FDFDFC] p-3.5 text-center dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                >
                    <div>
                        <span
                            class="block text-[11px] font-bold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            {{ ab.label }}
                        </span>

                        <!-- Final Score & Modifier -->
                        <div class="mt-2 text-2xl font-bold">
                            {{ finalAbilities[ab.key] }}
                        </div>
                        <div
                            class="mx-auto mt-1 inline-block rounded-xs bg-[#1b1b18] px-2 py-0.5 font-mono text-xs font-semibold text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]"
                        >
                            {{ formatSigned(abilityMod(ab.key)) }}
                        </div>
                    </div>

                    <!-- Base Input Control -->
                    <div
                        class="mt-3 border-t border-[#e3e3e0] pt-2.5 dark:border-[#3E3E3A]"
                    >
                        <span
                            class="block text-[10px] text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Base Score
                        </span>

                        <!-- Standard Array Dropdown -->
                        <select
                            v-if="abilityMethod === 'standard'"
                            :value="baseAbilities[ab.key]"
                            class="mt-1 block w-full rounded-xs border border-[#e3e3e0] bg-white px-2 py-1 text-center text-xs font-semibold dark:border-[#3E3E3A] dark:bg-[#161615]"
                            @change="
                                assignStandardArrayValue(
                                    ab.key,
                                    Number(
                                        ($event.target as HTMLSelectElement)
                                            .value,
                                    ),
                                )
                            "
                        >
                            <option
                                v-for="val in STANDARD_ARRAY"
                                :key="val"
                                :value="val"
                            >
                                {{ val }}
                            </option>
                        </select>

                        <!-- Point Buy +/- -->
                        <div
                            v-else-if="abilityMethod === 'pointbuy'"
                            class="mt-1 flex items-center justify-center gap-1.5"
                        >
                            <button
                                type="button"
                                class="flex h-6 w-6 cursor-pointer items-center justify-center rounded-xs border border-[#e3e3e0] bg-white text-xs font-bold hover:border-[#1b1b18] disabled:opacity-40 dark:border-[#3E3E3A] dark:bg-[#161615]"
                                :disabled="baseAbilities[ab.key] <= 8"
                                @click="adjustPointBuy(ab.key, -1)"
                            >
                                -
                            </button>
                            <span class="w-6 font-mono text-xs font-bold">
                                {{ baseAbilities[ab.key] }}
                            </span>
                            <button
                                type="button"
                                class="flex h-6 w-6 cursor-pointer items-center justify-center rounded-xs border border-[#e3e3e0] bg-white text-xs font-bold hover:border-[#1b1b18] disabled:opacity-40 dark:border-[#3E3E3A] dark:bg-[#161615]"
                                :disabled="baseAbilities[ab.key] >= 15"
                                @click="adjustPointBuy(ab.key, 1)"
                            >
                                +
                            </button>
                        </div>

                        <!-- Manual / Roll -->
                        <div v-else class="mt-1 flex items-center gap-1">
                            <input
                                v-model.number="baseAbilities[ab.key]"
                                type="number"
                                min="3"
                                max="20"
                                class="w-full rounded-xs border border-[#e3e3e0] bg-white py-1 text-center text-xs font-bold dark:border-[#3E3E3A] dark:bg-[#161615]"
                            />
                            <button
                                type="button"
                                title="Roll 4d6 drop lowest"
                                class="cursor-pointer rounded-xs border border-[#e3e3e0] bg-white px-1.5 py-1 text-[10px] font-semibold hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#161615]"
                                @click="rollSingleAbility(ab.key)"
                            >
                                Roll
                            </button>
                        </div>

                        <!-- Background Bonus Badge -->
                        <div
                            class="mt-1.5 text-[10px] font-semibold text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Bg Bonus:
                            <strong
                                :class="
                                    backgroundBonusFor(ab.key) > 0
                                        ? 'text-[#1b1b18] dark:text-[#EDEDEC]'
                                        : ''
                                "
                            >
                                +{{ backgroundBonusFor(ab.key) }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hit Points Breakdown Card -->
            <div
                class="flex flex-col justify-between gap-4 rounded-md border border-[#e3e3e0] bg-[#FDFDFC] p-4 sm:flex-row sm:items-center dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
            >
                <div>
                    <h4 class="text-sm font-bold">
                        Starting Hit Points (Level {{ level }}
                        {{ selectedClassName }})
                    </h4>
                    <p
                        class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                    >
                        Auto-calculated from {{ selectedClassName }} Hit Die
                        (d{{ currentClassDef.hitDie }}) + CON modifier ({{
                            formatSigned(abilityMod('con'))
                        }})
                        <span
                            v-if="
                                selectedOriginFeatName === 'Tough' ||
                                (selectedSpeciesName === 'Human' &&
                                    humanVersatileFeat === 'Tough')
                            "
                        >
                            + Tough Feat (+{{ 2 * level }} HP)
                        </span>
                        <span v-if="currentSpeciesDef.hpPerLevelBonus">
                            + Dwarven Toughness (+{{
                                currentSpeciesDef.hpPerLevelBonus * level
                            }}
                            HP)
                        </span>
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <span
                            class="block text-[10px] font-semibold text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Calculated Max HP
                        </span>
                        <span class="text-xl font-bold"
                            >{{ calculatedMaxHp }} HP</span
                        >
                    </div>
                    <div>
                        <label
                            class="block text-[10px] font-medium text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Custom Override
                        </label>
                        <input
                            v-model.number="customHpOverride"
                            type="number"
                            min="1"
                            :placeholder="String(calculatedMaxHp)"
                            class="mt-0.5 w-20 rounded-xs border border-[#e3e3e0] bg-white px-2 py-1 text-center text-xs font-bold dark:border-[#3E3E3A] dark:bg-[#161615]"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 6: STARTING EQUIPMENT & FINAL REVIEW -->
        <div v-if="currentStep === 6" class="mt-6 space-y-6">
            <div>
                <h3
                    class="text-sm font-semibold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                >
                    Step 6: Starting Equipment &amp; Character Summary
                </h3>
                <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                    Choose your starting equipment package or starting gold,
                    review your complete 2024 build, and create your character
                    sheet.
                </p>
            </div>

            <!-- Starting Equipment Package Choice -->
            <div class="grid gap-3 sm:grid-cols-2">
                <button
                    type="button"
                    class="cursor-pointer rounded-md border p-4 text-left transition-all"
                    :class="
                        equipmentChoice === 'package_a'
                            ? 'border-[#1b1b18] bg-[#1b1b18]/5 shadow-[inset_0px_0px_0px_1px_#1b1b18] dark:border-[#EDEDEC] dark:bg-[#EDEDEC]/10 dark:shadow-[inset_0px_0px_0px_1px_#EDEDEC]'
                            : 'border-[#e3e3e0] bg-[#FDFDFC] hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a]'
                    "
                    @click="equipmentChoice = 'package_a'"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold"
                            >Option A: Class &amp; Background Gear</span
                        >
                        <span
                            class="rounded-xs bg-[#1b1b18] px-2 py-0.5 text-[10px] font-semibold text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]"
                        >
                            Recommended
                        </span>
                    </div>
                    <p
                        class="mt-1.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                    >
                        <strong>{{ selectedClassName }}:</strong>
                        {{ currentClassDef.startingEquipment.packageALabel }}
                    </p>
                    <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                        <strong>{{ selectedBackgroundName }}:</strong>
                        {{ currentBackgroundDef.startingGearSummary }}
                    </p>
                </button>

                <button
                    type="button"
                    class="cursor-pointer rounded-md border p-4 text-left transition-all"
                    :class="
                        equipmentChoice === 'gold_only'
                            ? 'border-[#1b1b18] bg-[#1b1b18]/5 shadow-[inset_0px_0px_0px_1px_#1b1b18] dark:border-[#EDEDEC] dark:bg-[#EDEDEC]/10 dark:shadow-[inset_0px_0px_0px_1px_#EDEDEC]'
                            : 'border-[#e3e3e0] bg-[#FDFDFC] hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#0a0a0a]'
                    "
                    @click="equipmentChoice = 'gold_only'"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold"
                            >Option B: Starting Gold Buy</span
                        >
                        <span
                            class="rounded-xs border border-[#e3e3e0] px-2 py-0.5 text-[10px] font-semibold dark:border-[#3E3E3A]"
                        >
                            {{
                                currentClassDef.startingEquipment.goldB +
                                currentBackgroundDef.startingGold
                            }}
                            GP
                        </span>
                    </div>
                    <p
                        class="mt-1.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                    >
                        Start with
                        {{ currentClassDef.startingEquipment.goldB }} GP from
                        {{ selectedClassName }} +
                        {{ currentBackgroundDef.startingGold }} GP from
                        {{ selectedBackgroundName }} and pick items from the
                        Compendium on your sheet.
                    </p>
                </button>
            </div>

            <!-- Complete Character Build Review -->
            <div
                class="rounded-md border border-[#e3e3e0] bg-[#FDFDFC] p-4 sm:p-5 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
            >
                <h4
                    class="text-xs font-bold tracking-wider text-[#706f6c] uppercase dark:text-[#A1A09A]"
                >
                    Character Sheet Preview
                </h4>

                <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        class="rounded-sm border border-[#e3e3e0] bg-white p-3 dark:border-[#3E3E3A] dark:bg-[#161615]"
                    >
                        <span
                            class="block text-[10px] font-semibold text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Identity
                        </span>
                        <div class="mt-1 text-sm font-bold">
                            {{ charName.trim() || 'Unnamed Hero' }}
                        </div>
                        <div class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                            Level {{ level }} &middot;
                            {{ selectedSize }} &middot;
                            {{ effectiveBaseSpeed }} ft
                        </div>
                    </div>

                    <div
                        class="rounded-sm border border-[#e3e3e0] bg-white p-3 dark:border-[#3E3E3A] dark:bg-[#161615]"
                    >
                        <span
                            class="block text-[10px] font-semibold text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Class &amp; Subclass
                        </span>
                        <div class="mt-1 text-sm font-bold">
                            {{ selectedClassName }}
                            <span v-if="selectedSubclass" class="font-normal"
                                >({{ selectedSubclass }})</span
                            >
                        </div>
                        <div class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                            Hit Die: {{ level }}d{{ currentClassDef.hitDie }}
                            &middot; Saves:
                            {{
                                currentClassDef.savingThrows
                                    .map((s) => s.toUpperCase())
                                    .join(', ')
                            }}
                        </div>
                    </div>

                    <div
                        class="rounded-sm border border-[#e3e3e0] bg-white p-3 dark:border-[#3E3E3A] dark:bg-[#161615]"
                    >
                        <span
                            class="block text-[10px] font-semibold text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Background &amp; Origin Feat
                        </span>
                        <div class="mt-1 text-sm font-bold">
                            {{ selectedBackgroundName }}
                        </div>
                        <div class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                            Feat: {{ selectedOriginFeatName }}
                        </div>
                    </div>

                    <div
                        class="rounded-sm border border-[#e3e3e0] bg-white p-3 dark:border-[#3E3E3A] dark:bg-[#161615]"
                    >
                        <span
                            class="block text-[10px] font-semibold text-[#706f6c] uppercase dark:text-[#A1A09A]"
                        >
                            Species &amp; Lineage
                        </span>
                        <div class="mt-1 text-sm font-bold">
                            {{ selectedSpeciesName }}
                        </div>
                        <div class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                            {{ selectedLineageName || 'Standard Lineage' }}
                        </div>
                    </div>
                </div>

                <!-- Proficient Skills List -->
                <div class="mt-4">
                    <span class="block text-xs font-semibold">
                        Proficient Skills ({{ proficientSkillNames.length }}):
                    </span>
                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                        <span
                            v-for="sName in proficientSkillNames"
                            :key="sName"
                            class="rounded-xs border border-[#e3e3e0] bg-white px-2 py-0.5 text-xs font-medium dark:border-[#3E3E3A] dark:bg-[#161615]"
                        >
                            {{ sName }}
                        </span>
                    </div>
                </div>

                <!-- Generated Features & Traits Preview -->
                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <div>
                        <span class="block text-xs font-semibold">
                            Features, Origin Feat &amp; Traits (Auto-Added to
                            Sheet):
                        </span>
                        <pre
                            class="mt-1.5 max-h-48 overflow-y-auto rounded-sm border border-[#e3e3e0] bg-white p-3 font-sans text-xs whitespace-pre-wrap text-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#EDEDEC]"
                            >{{ generatedFeaturesText }}</pre>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold">
                            Proficiencies, Languages &amp; Starting Gear:
                        </span>
                        <pre
                            class="mt-1.5 max-h-48 overflow-y-auto rounded-sm border border-[#e3e3e0] bg-white p-3 font-sans text-xs whitespace-pre-wrap text-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#EDEDEC]"
                            >{{ generatedProficienciesText }}

Starting Weapons: {{
                                equipmentChoice === 'package_a'
                                    ? startingWeapons
                                          .map((w) => w.name)
                                          .join(', ') || 'None'
                                    : 'None'
                            }}
Starting Armor: {{
                                equipmentChoice === 'package_a'
                                    ? startingArmors
                                          .map((a) => a.name)
                                          .join(', ') || 'Unarmored'
                                    : 'Unarmored'
                            }}
Starting Spells: {{ startingSpells.map((s) => s.name).join(', ') || 'None' }}
Starting Gold: {{ startingGold }} GP</pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Wizard Navigation Footer -->
        <div
            class="mt-6 flex flex-col items-stretch justify-between gap-3 border-t border-[#e3e3e0] pt-4 sm:flex-row sm:items-center dark:border-[#3E3E3A]"
        >
            <div class="flex items-center gap-2">
                <button
                    v-if="currentStep > 1"
                    type="button"
                    class="cursor-pointer rounded-sm border border-[#e3e3e0] px-4 py-2 text-xs font-semibold transition-colors hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#EDEDEC]"
                    @click="prevStep"
                >
                    &larr; Previous Step
                </button>
                <button
                    type="button"
                    class="cursor-pointer rounded-sm border border-[#e3e3e0] px-3 py-2 text-xs font-medium text-[#706f6c] transition-colors hover:border-[#1b1b18] hover:text-[#1b1b18] dark:border-[#3E3E3A] dark:text-[#A1A09A] dark:hover:border-[#EDEDEC] dark:hover:text-[#EDEDEC]"
                    @click="emit('close')"
                >
                    Cancel
                </button>
            </div>

            <div
                class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-center"
            >
                <button
                    v-if="currentStep < 6"
                    type="button"
                    :disabled="isSubmitting"
                    class="cursor-pointer rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-4 py-2 text-xs font-medium text-[#706f6c] transition-colors hover:border-[#1b1b18] hover:text-[#1b1b18] disabled:opacity-50 dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#A1A09A] dark:hover:border-[#EDEDEC] dark:hover:text-[#EDEDEC]"
                    @click="submitCharacter"
                >
                    Quick Create Now
                </button>

                <button
                    v-if="currentStep < 6"
                    type="button"
                    class="cursor-pointer rounded-sm border border-black bg-[#1b1b18] px-5 py-2.5 text-xs font-semibold text-white transition-colors hover:bg-black sm:py-2 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                    @click="nextStep"
                >
                    Next: {{ steps[currentStep].title }} &rarr;
                </button>

                <button
                    v-else
                    type="button"
                    :disabled="isSubmitting"
                    class="cursor-pointer rounded-sm border border-black bg-[#1b1b18] px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-black disabled:opacity-60 sm:py-2 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                    @click="submitCharacter"
                >
                    <span v-if="isSubmitting">Creating Character...</span>
                    <span v-else>Create &amp; Open Character Sheet &rarr;</span>
                </button>
            </div>
        </div>
    </section>
</template>
