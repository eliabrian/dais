import type {
    AbilityKey,
    SheetArmorItem,
    SheetInventoryItem,
    SheetSpellItem,
    SheetWeaponItem,
} from '@/types/character';

export interface ClassFeatureOption {
    id: string;
    name: string;
    description: string;
    choices?: {
        label: string;
        options: { name: string; summary: string }[];
    };
}

export interface DndClassDefinition {
    name: string;
    tagline: string;
    description: string;
    hitDie: number;
    primaryAbility: string;
    savingThrows: AbilityKey[];
    skillChoicesCount: number;
    availableSkills: string[];
    recommendedSkills: string[];
    armorProficiencies: string;
    weaponProficiencies: string;
    toolProficiencies?: string;
    weaponMasteryCount: number;
    recommendedMasteries: string[];
    spellcastingAbility: 'none' | 'int' | 'wis' | 'cha';
    recommendedAbilities: Record<AbilityKey, number>;
    subclasses: string[];
    features: ClassFeatureOption[];
    startingEquipment: {
        packageALabel: string;
        goldA: number;
        goldB: number;
        weapons: Omit<SheetWeaponItem, 'id'>[];
        armors: Omit<SheetArmorItem, 'id'>[];
        inventory: Omit<SheetInventoryItem, 'id'>[];
    };
    recommendedSpells?: Omit<SheetSpellItem, 'id'>[];
}

export interface OriginFeatDefinition {
    name: string;
    category: 'Origin Feat';
    summary: string;
    description: string;
    benefits: string[];
    hpPerLevelBonus?: number;
    extraSkillsCount?: number;
}

export interface BackgroundFeatureOption {
    name: string;
    description: string;
}

export interface DndBackgroundDefinition {
    name: string;
    summary: string;
    description: string;
    abilities: [AbilityKey, AbilityKey, AbilityKey];
    defaultPlusTwo: AbilityKey;
    defaultPlusOne: AbilityKey;
    originFeat: string;
    skills: [string, string];
    toolProficiency: string;
    startingGold: number;
    startingGearSummary: string;
    features: BackgroundFeatureOption[];
}

export interface SpeciesLineageOption {
    name: string;
    summary: string;
    speedOverride?: number;
}

export interface DndSpeciesDefinition {
    name: string;
    summary: string;
    description: string;
    defaultSize: 'Medium' | 'Small';
    sizeOptions: ('Medium' | 'Small')[];
    speed: number;
    hpPerLevelBonus?: number;
    grantsHeroicInspiration?: boolean;
    grantsExtraSkill?: boolean;
    grantsExtraOriginFeat?: boolean;
    lineageLabel?: string;
    lineages?: SpeciesLineageOption[];
    traits: {
        name: string;
        description: string;
    }[];
    languages: string[];
}

export const ABILITY_INFO: {
    key: AbilityKey;
    label: string;
    short: string;
    description: string;
}[] = [
    {
        key: 'str',
        label: 'Strength',
        short: 'STR',
        description:
            'Physical might, melee attacks, athletics, and carrying capacity.',
    },
    {
        key: 'dex',
        label: 'Dexterity',
        short: 'DEX',
        description:
            'Agility, reflexes, ranged/finesse attacks, AC, and initiative.',
    },
    {
        key: 'con',
        label: 'Constitution',
        short: 'CON',
        description:
            'Endurance, vitality, hit points per level, and concentration.',
    },
    {
        key: 'int',
        label: 'Intelligence',
        short: 'INT',
        description:
            'Memory, reasoning, arcane magic, investigation, and lore.',
    },
    {
        key: 'wis',
        label: 'Wisdom',
        short: 'WIS',
        description:
            'Perception, intuition, divine/primal magic, and willpower.',
    },
    {
        key: 'cha',
        label: 'Charisma',
        short: 'CHA',
        description:
            'Force of personality, leadership, social influence, and innate magic.',
    },
];

export const SKILL_LIST: {
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

export const WEAPON_MASTERY_OPTIONS: {
    name: string;
    summary: string;
}[] = [
    {
        name: 'Cleave',
        summary:
            'On a melee hit, make an extra attack against a second creature within 5 ft (Greataxe, Halberd).',
    },
    {
        name: 'Graze',
        summary:
            'On a missed attack roll, deal damage equal to your ability modifier (Greatsword, Glaive).',
    },
    {
        name: 'Nick',
        summary:
            'Make the extra attack of the Light property as part of the Attack action instead of a Bonus Action (Dagger, Scimitar).',
    },
    {
        name: 'Push',
        summary:
            'Push a Large or smaller target up to 10 ft straight away on a hit (Warhammer, Heavy Crossbow).',
    },
    {
        name: 'Sap',
        summary:
            'Target has Disadvantage on its next attack roll before the start of your next turn (Longsword, Spear, Mace).',
    },
    {
        name: 'Slow',
        summary:
            "Reduce target's Speed by 10 ft until the start of your next turn on a hit (Longbow, Javelin, Light Crossbow).",
    },
    {
        name: 'Topple',
        summary:
            'Target must succeed on a CON save or fall Prone on a hit (Battleaxe, Quarterstaff, Lance).',
    },
    {
        name: 'Vex',
        summary:
            'Gain Advantage on your next attack roll against the target before the end of your next turn (Rapier, Shortsword, Shortbow, Handaxe).',
    },
];

export const ORIGIN_FEATS: OriginFeatDefinition[] = [
    {
        name: 'Alert',
        category: 'Origin Feat',
        summary:
            'Add Proficiency Bonus to Initiative; swap Initiative with a willing ally.',
        description:
            'Always on the lookout for danger, you gain enhanced reflexes at the start of combat.',
        benefits: [
            'Initiative Proficiency: Add your Proficiency Bonus to your Initiative rolls.',
            'Initiative Swap: Immediately after you roll Initiative, you can swap your Initiative with one willing ally in the same combat.',
        ],
    },
    {
        name: 'Crafter',
        category: 'Origin Feat',
        summary:
            "3 Artisan's Tools proficiencies · 20% discount on nonmagical items · Fast Crafting.",
        description:
            'You are adept at crafting everyday gear and improvising tools during rests.',
        benefits: [
            "Tool Proficiency: Gain proficiency with three different Artisan's Tools of your choice.",
            'Discount: Whenever you buy a nonmagical item, you receive a 20% discount on it.',
            'Fast Crafting: When you finish a Long Rest, you can craft one piece of gear from the Fast Crafting table that lasts until your next Long Rest.',
        ],
    },
    {
        name: 'Healer',
        category: 'Origin Feat',
        summary:
            "Battle Medic (spend Healer's Kit use to heal Hit Die + PB) · Healing Rerolls (reroll 1s).",
        description:
            'You are trained in battlefield triage and restorative magic efficiency.',
        benefits: [
            "Battle Medic: If you have a Healer's Kit, you can expend one use as a Utilize action to tend to a creature within 5 ft; that creature can expend one of its Hit Point Dice and regain that roll + your Proficiency Bonus.",
            'Healing Rerolls: Whenever you roll a die to determine HP restored with a spell or with Battle Medic, you can reroll the die if it rolls a 1.',
        ],
    },
    {
        name: 'Lucky',
        category: 'Origin Feat',
        summary:
            'Luck Points equal to Proficiency Bonus · Spend for Advantage on d20 Test or impose Disadvantage.',
        description: 'Inexplicable fortune favors you in moments of crisis.',
        benefits: [
            'Luck Points: You have a number of Luck Points equal to your Proficiency Bonus, regained on a Long Rest.',
            'Advantage: Spend 1 Luck Point when you roll a d20 for a d20 Test to give yourself Advantage on the roll.',
            'Disadvantage: Spend 1 Luck Point when a creature rolls a d20 for an attack roll against you to impose Disadvantage on that roll.',
        ],
    },
    {
        name: 'Magic Initiate (Cleric)',
        category: 'Origin Feat',
        summary:
            '2 Cleric Cantrips + 1 Level 1 Cleric Spell (cast 1/day free + with spell slots).',
        description:
            'You have learned foundational divine magic from the Cleric spell list.',
        benefits: [
            'Two Cantrips: Learn two cantrips of your choice from the Cleric spell list (e.g. Guidance, Sacred Flame).',
            'Level 1 Spell: Choose a Level 1 Cleric spell (e.g. Bless, Healing Word). You always have it prepared and can cast it once without a spell slot per Long Rest, as well as using any spell slots you have.',
        ],
    },
    {
        name: 'Magic Initiate (Druid)',
        category: 'Origin Feat',
        summary:
            '2 Druid Cantrips + 1 Level 1 Druid Spell (cast 1/day free + with spell slots).',
        description:
            'You have attuned to primal magic from the Druid spell list.',
        benefits: [
            'Two Cantrips: Learn two cantrips of your choice from the Druid spell list (e.g. Guidance, Shillelagh).',
            'Level 1 Spell: Choose a Level 1 Druid spell (e.g. Cure Wounds, Thunderwave). You always have it prepared and can cast it once without a spell slot per Long Rest.',
        ],
    },
    {
        name: 'Magic Initiate (Wizard)',
        category: 'Origin Feat',
        summary:
            '2 Wizard Cantrips + 1 Level 1 Wizard Spell (cast 1/day free + with spell slots).',
        description:
            'You have studied fundamental arcane formulas from the Wizard spell list.',
        benefits: [
            'Two Cantrips: Learn two cantrips of your choice from the Wizard spell list (e.g. Fire Bolt, Mage Hand).',
            'Level 1 Spell: Choose a Level 1 Wizard spell (e.g. Shield, Magic Missile). You always have it prepared and can cast it once without a spell slot per Long Rest.',
        ],
    },
    {
        name: 'Musician',
        category: 'Origin Feat',
        summary:
            '3 Musical Instrument proficiencies · Encouraging Song grants Heroic Inspiration to allies after a rest.',
        description: 'Your music bolsters the spirits of your companions.',
        benefits: [
            'Instrument Training: Gain proficiency with three Musical Instruments of your choice.',
            'Encouraging Song: As you finish a Short or Long Rest, you can play a song on a Musical Instrument and give Heroic Inspiration to allies who hear it (up to your Proficiency Bonus).',
        ],
    },
    {
        name: 'Savage Attacker',
        category: 'Origin Feat',
        summary:
            'Once per turn on a weapon hit, roll weapon damage dice twice and use either roll.',
        description:
            'You have trained to deal particularly devastating blows in close quarters.',
        benefits: [
            'Savage Strikes: Once per turn when you hit a target with a weapon, you can roll the weapon’s damage dice twice and use either roll against the target.',
        ],
    },
    {
        name: 'Skilled',
        category: 'Origin Feat',
        summary:
            'Gain proficiency in any combination of 3 skills or tools of your choice.',
        description:
            'You have an exceptionally broad range of training and expertise.',
        benefits: [
            'Broad Expertise: Gain proficiency in any combination of three skills or tools of your choice.',
            'Repeatable: You can take this feat more than once.',
        ],
        extraSkillsCount: 3,
    },
    {
        name: 'Tavern Brawler',
        category: 'Origin Feat',
        summary:
            'Enhanced Unarmed Strike (1d4 + STR) · Damage Rerolls · Improvised Weaponry · Push 5 ft.',
        description:
            'Accustomed to rough-and-tumble brawls, you fight fiercely with bare hands and improvised objects.',
        benefits: [
            'Enhanced Unarmed Strike: Your Unarmed Strike deals 1d4 + STR modifier Bludgeoning damage.',
            'Damage Rerolls: Whenever you roll a 1 on a damage die for your Unarmed Strike, you can reroll the die.',
            'Improvised Weaponry: You have proficiency with improvised weapons.',
            'Push: When you hit a creature with an Unarmed Strike as part of the Attack action, you can deal damage AND push it 5 ft away once per turn.',
        ],
    },
    {
        name: 'Tough',
        category: 'Origin Feat',
        summary:
            'Hit Point maximum increases by 2 for every character level (+2 HP per level).',
        description:
            'Your body is unusually resilient and capable of withstanding punishment.',
        benefits: [
            'Vital Fortitude: Your Hit Point maximum increases by an amount equal to twice your character level when you gain this feat (+2 HP per level).',
        ],
        hpPerLevelBonus: 2,
    },
];

export const DND_BACKGROUNDS: DndBackgroundDefinition[] = [
    {
        name: 'Acolyte',
        summary: 'INT, WIS, CHA · Magic Initiate (Cleric) · Insight, Religion',
        description:
            'You devoted yourself to service in a temple, performing sacred rites and studying religious doctrine.',
        abilities: ['int', 'wis', 'cha'],
        defaultPlusTwo: 'wis',
        defaultPlusOne: 'int',
        originFeat: 'Magic Initiate (Cleric)',
        skills: ['insight', 'religion'],
        toolProficiency: "Calligrapher's Supplies",
        startingGold: 50,
        startingGearSummary:
            "Calligrapher's Supplies, Book (prayers), Holy Symbol, Parchment (10), Robe, 8 GP",
        features: [
            {
                name: 'Shelter of the Faithful',
                description:
                    'You and your companions can expect free healing and care at temples and shrines of your faith, and priests of your temple will support you at a modest lifestyle.',
            },
            {
                name: 'Temple Scholar',
                description:
                    'You know the liturgical ceremonies and hierarchies of major pantheons and have access to ecclesiastical archives.',
            },
        ],
    },
    {
        name: 'Artisan',
        summary: 'STR, DEX, INT · Crafter · Investigation, Persuasion',
        description:
            'You began mopping floors and scrubbing counters in a craftsperson’s workshop before becoming an apprentice and master of your trade.',
        abilities: ['str', 'dex', 'int'],
        defaultPlusTwo: 'int',
        defaultPlusOne: 'dex',
        originFeat: 'Crafter',
        skills: ['investigation', 'persuasion'],
        toolProficiency: "Artisan's Tools (Smith's, Mason's, or Carpenter's)",
        startingGold: 50,
        startingGearSummary:
            "Artisan's Tools, 2 Pouches, Traveler's Clothes, 32 GP",
        features: [
            {
                name: 'Guild Membership',
                description:
                    'Your fellow guild members will provide you with lodging and food if necessary, and the guild hall offers a central place to meet patrons, allies, or hirelings.',
            },
            {
                name: 'Master Appraiser',
                description:
                    'You can quickly discern the craftsmanship, origin, and approximate market value of manufactured goods and structural work.',
            },
        ],
    },
    {
        name: 'Charlatan',
        summary: 'DEX, CON, CHA · Skilled · Deception, Sleight of Hand',
        description:
            'You traveled from town to town selling snake oil, forging documents, and reading people’s desires like an open book.',
        abilities: ['dex', 'con', 'cha'],
        defaultPlusTwo: 'cha',
        defaultPlusOne: 'dex',
        originFeat: 'Skilled',
        skills: ['deception', 'sleight_of_hand'],
        toolProficiency: 'Forgery Kit',
        startingGold: 50,
        startingGearSummary: 'Forgery Kit, Costume, Fine Clothes, 15 GP',
        features: [
            {
                name: 'False Identity',
                description:
                    'You have created a second identity that includes documentation, established acquaintances, and disguises that allow you to assume that persona.',
            },
            {
                name: 'Con Artist Pedigree',
                description:
                    'You know the secret signs of grifters and black-market fences in bustling taverns and markets.',
            },
        ],
    },
    {
        name: 'Criminal',
        summary: 'DEX, CON, INT · Alert · Sleight of Hand, Stealth',
        description:
            'You eked out a living in dark alleys, cutting purses or burgling manor houses while staying one step ahead of the watch.',
        abilities: ['dex', 'con', 'int'],
        defaultPlusTwo: 'dex',
        defaultPlusOne: 'con',
        originFeat: 'Alert',
        skills: ['sleight_of_hand', 'stealth'],
        toolProficiency: "Thieves' Tools",
        startingGold: 50,
        startingGearSummary:
            "2 Daggers, Thieves' Tools, Crowbar, 2 Pouches, Traveler's Clothes, 16 GP",
        features: [
            {
                name: 'Criminal Contact',
                description:
                    'You have a reliable and trustworthy contact who acts as your liaison to a network of other criminals, smugglers, and fences.',
            },
            {
                name: 'Underworld Ear',
                description:
                    'You can quickly locate the local thieves’ guild drop-points, safe houses, or illicit markets in any settlement.',
            },
        ],
    },
    {
        name: 'Entertainer',
        summary: 'STR, DEX, CHA · Musician · Acrobatics, Performance',
        description:
            'You spent your youth following roving fairs and carnivals, dazzling crowds with music, acrobatics, or dramatic storytelling.',
        abilities: ['str', 'dex', 'cha'],
        defaultPlusTwo: 'cha',
        defaultPlusOne: 'dex',
        originFeat: 'Musician',
        skills: ['acrobatics', 'performance'],
        toolProficiency: 'Musical Instrument',
        startingGold: 50,
        startingGearSummary:
            "Musical Instrument, 2 Costumes, Mirror, Perfume, Traveler's Clothes, 11 GP",
        features: [
            {
                name: 'By Popular Demand',
                description:
                    'You can always find a place to perform in an inn or tavern, receiving free lodging and food of a modest or comfortable standard.',
            },
            {
                name: 'Stage Presence',
                description:
                    'Your performances make you memorable to local patrons and nobles who often invite you to social gatherings.',
            },
        ],
    },
    {
        name: 'Farmer',
        summary: 'STR, CON, WIS · Tough · Animal Handling, Nature',
        description:
            'You grew up close to the land, tending crops and livestock through harsh seasons that built your grit and patience.',
        abilities: ['str', 'con', 'wis'],
        defaultPlusTwo: 'con',
        defaultPlusOne: 'str',
        originFeat: 'Tough',
        skills: ['animal_handling', 'nature'],
        toolProficiency: "Carpenter's Tools",
        startingGold: 50,
        startingGearSummary:
            "Sickle, Carpenter's Tools, Healer's Kit, Iron Pot, Shovel, Traveler's Clothes, 30 GP",
        features: [
            {
                name: 'Folk Hospitality',
                description:
                    'Since you come from the ranks of the common folk, you fit in among them with ease and can find rustic shelter and aid from rural homesteaders.',
            },
            {
                name: 'Weatherwise',
                description:
                    'Years of working the fields give you an uncanny sense for impending weather shifts, soil quality, and animal behavior.',
            },
        ],
    },
    {
        name: 'Guard',
        summary: 'STR, INT, WIS · Alert · Athletics, Perception',
        description:
            'Your feet ache when you remember the countless hours you spent at your post in a watchtower or city gate, scanning for trouble.',
        abilities: ['str', 'int', 'wis'],
        defaultPlusTwo: 'str',
        defaultPlusOne: 'wis',
        originFeat: 'Alert',
        skills: ['athletics', 'perception'],
        toolProficiency: 'Gaming Set',
        startingGold: 50,
        startingGearSummary:
            "Spear, Light Crossbow, 20 Bolts, Gaming Set, Hooded Lantern, Manacles, Traveler's Clothes, 12 GP",
        features: [
            {
                name: 'Watcher’s Eye',
                description:
                    'Your experience enforcing the law and dealing with lawbreakers gives you a feel for local garrison routines, watch posts, and smuggling checkpoints.',
            },
            {
                name: ' Garrison Courtesy',
                description:
                    'Local town guards and militia recognize your bearing and are more inclined to share public patrol reports with you.',
            },
        ],
    },
    {
        name: 'Guide',
        summary: 'DEX, CON, WIS · Magic Initiate (Druid) · Stealth, Survival',
        description:
            'You came of age outdoors, far from settled lands, guiding travelers and explorers through treacherous wilds.',
        abilities: ['dex', 'con', 'wis'],
        defaultPlusTwo: 'wis',
        defaultPlusOne: 'dex',
        originFeat: 'Magic Initiate (Druid)',
        skills: ['stealth', 'survival'],
        toolProficiency: "Cartographer's Tools",
        startingGold: 50,
        startingGearSummary:
            "Shortbow, 20 Arrows, Cartographer's Tools, Bedroll, Quiver, Tent, Traveler's Clothes, 3 GP",
        features: [
            {
                name: 'Wanderer',
                description:
                    'You have an excellent memory for maps and geography, and you can always recall the general layout of terrain, settlements, and fresh water sources.',
            },
            {
                name: 'Trailblazer',
                description:
                    'You know how to read subtle trail markers left by rangers, druids, and outlanders.',
            },
        ],
    },
    {
        name: 'Hermit',
        summary: 'CON, WIS, CHA · Healer · Medicine, Religion',
        description:
            'You spent your early years secluded in a hut or monastery located well beyond the outskirts of civilization, contemplating mysteries.',
        abilities: ['con', 'wis', 'cha'],
        defaultPlusTwo: 'wis',
        defaultPlusOne: 'con',
        originFeat: 'Healer',
        skills: ['medicine', 'religion'],
        toolProficiency: 'Herbalism Kit',
        startingGold: 50,
        startingGearSummary:
            "Quarterstaff, Herbalism Kit, Bedroll, Book (philosophy), Lamp, Oil (3), Traveler's Clothes, 16 GP",
        features: [
            {
                name: 'Discovery',
                description:
                    'The quiet seclusion of your extended hermitage gave you access to a unique and powerful discovery about the cosmos, nature, or ancient history.',
            },
            {
                name: 'Sanctuary of Solitude',
                description:
                    'You know hidden grottos, shrines, and wilderness refuges where travelers rarely tread.',
            },
        ],
    },
    {
        name: 'Merchant',
        summary: 'CON, INT, CHA · Lucky · Animal Handling, Persuasion',
        description:
            'You were apprenticed to a trader, caravan master, or shopkeeper, learning the fundamentals of commerce and negotiation.',
        abilities: ['con', 'int', 'cha'],
        defaultPlusTwo: 'cha',
        defaultPlusOne: 'int',
        originFeat: 'Lucky',
        skills: ['animal_handling', 'persuasion'],
        toolProficiency: "Navigator's Tools",
        startingGold: 50,
        startingGearSummary:
            "Navigator's Tools, 2 Pouches, Fine Clothes, 22 GP",
        features: [
            {
                name: 'Supply Chain Network',
                description:
                    'You know trade routes, caravan schedules, and merchant houses across major towns, allowing you to source rare goods or secure transport.',
            },
            {
                name: 'Eye for Coin',
                description:
                    'You can quickly assess trade tariffs, local supply shortages, and reputable moneylenders.',
            },
        ],
    },
    {
        name: 'Noble',
        summary: 'STR, INT, CHA · Skilled · History, Persuasion',
        description:
            'You were raised in a castle, surrounded by wealth, power, and privilege, tutored in leadership and diplomacy.',
        abilities: ['str', 'int', 'cha'],
        defaultPlusTwo: 'cha',
        defaultPlusOne: 'int',
        originFeat: 'Skilled',
        skills: ['history', 'persuasion'],
        toolProficiency: 'Gaming Set',
        startingGold: 50,
        startingGearSummary:
            'Gaming Set, Fine Clothes, Perfume, Signet Ring, 29 GP',
        features: [
            {
                name: 'Position of Privilege',
                description:
                    'Thanks to your noble birth, people are inclined to think the best of you. You are welcome in high society, and common folk make every effort to accommodate you.',
            },
            {
                name: 'Courtly Etiquette',
                description:
                    'You can secure audiences with local aristocrats, magistrates, and heraldic officials.',
            },
        ],
    },
    {
        name: 'Sage',
        summary: 'CON, INT, WIS · Magic Initiate (Wizard) · Arcana, History',
        description:
            'You spent your formative years traveling between manors and monasteries, performing odd jobs in exchange for access to libraries.',
        abilities: ['con', 'int', 'wis'],
        defaultPlusTwo: 'int',
        defaultPlusOne: 'wis',
        originFeat: 'Magic Initiate (Wizard)',
        skills: ['arcana', 'history'],
        toolProficiency: "Calligrapher's Supplies",
        startingGold: 50,
        startingGearSummary:
            "Quarterstaff, Calligrapher's Supplies, Book (history), Parchment (8), Robe, 8 GP",
        features: [
            {
                name: 'Researcher',
                description:
                    'When you attempt to learn or recall a piece of lore, if you do not know that information, you often know where and from whom you can obtain it.',
            },
            {
                name: 'Academic Credentials',
                description:
                    'Universities, arcane colleges, and great libraries recognize your scholarly standing.',
            },
        ],
    },
    {
        name: 'Sailor',
        summary: 'STR, DEX, WIS · Tavern Brawler · Acrobatics, Perception',
        description:
            'You lived as a seafarer, wind at your back and decks swaying beneath your feet, facing storms and deep-sea perils.',
        abilities: ['str', 'dex', 'wis'],
        defaultPlusTwo: 'dex',
        defaultPlusOne: 'wis',
        originFeat: 'Tavern Brawler',
        skills: ['acrobatics', 'perception'],
        toolProficiency: "Navigator's Tools",
        startingGold: 50,
        startingGearSummary:
            "Dagger, Navigator's Tools, Silk Rope, Traveler's Clothes, 20 GP",
        features: [
            {
                name: "Ship's Passage",
                description:
                    'When you need to, you can secure free passage on a sailing ship for yourself and your adventuring companions in exchange for working with the crew.',
            },
            {
                name: 'Port Lore',
                description:
                    'You know the customs, taverns, and dockside rumors of coastal ports and river towns.',
            },
        ],
    },
    {
        name: 'Scribe',
        summary: 'DEX, INT, WIS · Skilled · Investigation, Perception',
        description:
            'You spent formative years in a scriptorium, copying legal codices, treaties, and ancient texts with painstaking precision.',
        abilities: ['dex', 'int', 'wis'],
        defaultPlusTwo: 'int',
        defaultPlusOne: 'dex',
        originFeat: 'Skilled',
        skills: ['investigation', 'perception'],
        toolProficiency: "Calligrapher's Supplies",
        startingGold: 50,
        startingGearSummary:
            "Calligrapher's Supplies, Fine Clothes, Lamp, Oil (3), Parchment (12), 23 GP",
        features: [
            {
                name: 'Archivist’s Eye',
                description:
                    'You can swiftly navigate civic chanceries, court records, and bureaucratic archives, spotting clauses or ciphered notes others miss.',
            },
            {
                name: 'Notary Seal',
                description:
                    'Clerks, magistrates, and chroniclers respect your training in official documentation.',
            },
        ],
    },
    {
        name: 'Soldier',
        summary: 'STR, DEX, CON · Savage Attacker · Athletics, Intimidation',
        description:
            'You began training for war as soon as you reached adulthood and carry precious few memories of life before taking up arms.',
        abilities: ['str', 'dex', 'con'],
        defaultPlusTwo: 'str',
        defaultPlusOne: 'con',
        originFeat: 'Savage Attacker',
        skills: ['athletics', 'intimidation'],
        toolProficiency: 'Gaming Set',
        startingGold: 50,
        startingGearSummary:
            "Spear, Shortbow, 20 Arrows, Gaming Set, Healer's Kit, Quiver, Traveler's Clothes, 14 GP",
        features: [
            {
                name: 'Military Rank',
                description:
                    'You have a military rank from your career as a soldier. Soldiers loyal to your former military organization still recognize your authority and influence.',
            },
            {
                name: 'Veteran Tactician',
                description:
                    'You can quickly assess fortifications, troop morale, and battlefield supply lines.',
            },
        ],
    },
    {
        name: 'Wayfarer',
        summary: 'DEX, WIS, CHA · Lucky · Insight, Stealth',
        description:
            'You grew up on the streets surrounded by similarly ill-fated castoffs, surviving through resourcefulness, loyalty, and wit.',
        abilities: ['dex', 'wis', 'cha'],
        defaultPlusTwo: 'dex',
        defaultPlusOne: 'cha',
        originFeat: 'Lucky',
        skills: ['insight', 'stealth'],
        toolProficiency: "Thieves' Tools",
        startingGold: 50,
        startingGearSummary:
            "2 Daggers, Thieves' Tools, Gaming Set, Bedroll, 2 Pouches, Traveler's Clothes, 16 GP",
        features: [
            {
                name: 'City Secrets',
                description:
                    'You know the secret patterns and flow to cities and can find passages through the urban sprawl that others would miss, moving twice as fast between locations.',
            },
            {
                name: 'Streetwise Survivor',
                description:
                    'Urchins, beggars, and street vendors recognize you as one of their own and share word of local happenings.',
            },
        ],
    },
];

export const DND_SPECIES: DndSpeciesDefinition[] = [
    {
        name: 'Human',
        summary:
            'Medium or Small · 30 ft · Resourceful, Skillful (+1 Skill), Versatile (+1 Origin Feat)',
        description:
            'Found throughout the multiverse, Humans are as varied as they are numerous, renowned for their adaptability, ambition, and ingenuity.',
        defaultSize: 'Medium',
        sizeOptions: ['Medium', 'Small'],
        speed: 30,
        grantsHeroicInspiration: true,
        grantsExtraSkill: true,
        grantsExtraOriginFeat: true,
        traits: [
            {
                name: 'Resourceful',
                description:
                    'You gain Heroic Inspiration whenever you finish a Long Rest.',
            },
            {
                name: 'Skillful',
                description:
                    'You gain proficiency in one skill of your choice.',
            },
            {
                name: 'Versatile',
                description:
                    'You gain an Origin feat of your choice (Skilled is recommended).',
            },
        ],
        languages: ['Common', 'One additional language of choice'],
    },
    {
        name: 'Aasimar',
        summary:
            'Medium or Small · 30 ft · Celestial Resistance, Darkvision, Healing Hands, Celestial Revelation',
        description:
            'Aasimar are mortals who carry a spark of the Upper Planes within their souls, able to kindle light and healing.',
        defaultSize: 'Medium',
        sizeOptions: ['Medium', 'Small'],
        speed: 30,
        lineageLabel: 'Celestial Revelation (Level 3+)',
        lineages: [
            {
                name: 'Heavenly Wings',
                summary:
                    'Gain spectral wings and a Fly Speed equal to your Speed (Radiant bonus damage).',
            },
            {
                name: 'Inner Radiance',
                summary:
                    'Searing light radiates from you for 10 ft, dealing Radiant damage.',
            },
            {
                name: 'Necrotic Shroud',
                summary:
                    'Foes within 10 ft must save or become Frightened (Necrotic bonus damage).',
            },
        ],
        traits: [
            {
                name: 'Celestial Resistance',
                description:
                    'You have Resistance to Necrotic damage and Radiant damage.',
            },
            {
                name: 'Darkvision (60 ft)',
                description: 'You have Darkvision with a range of 60 feet.',
            },
            {
                name: 'Healing Hands',
                description:
                    'As a Magic action once per Long Rest, touch a creature and roll a number of d4s equal to your Proficiency Bonus to restore HP.',
            },
            {
                name: 'Light Bearer',
                description:
                    'You know the Light cantrip (Charisma is your spellcasting ability for it).',
            },
        ],
        languages: ['Common', 'Celestial'],
    },
    {
        name: 'Dragonborn',
        summary:
            'Medium · 30 ft · Draconic Ancestry, Breath Weapon, Damage Resistance, Darkvision',
        description:
            'The ancestors of Dragonborn hatched from the eggs of chromatic and metallic dragons, proud warriors carrying elemental breath.',
        defaultSize: 'Medium',
        sizeOptions: ['Medium'],
        speed: 30,
        lineageLabel: 'Draconic Ancestry',
        lineages: [
            {
                name: 'Red Dragon (Fire)',
                summary: 'Fire Breath Weapon & Fire Resistance',
            },
            {
                name: 'Gold Dragon (Fire)',
                summary: 'Fire Breath Weapon & Fire Resistance',
            },
            {
                name: 'Brass Dragon (Fire)',
                summary: 'Fire Breath Weapon & Fire Resistance',
            },
            {
                name: 'Blue Dragon (Lightning)',
                summary: 'Lightning Breath Weapon & Lightning Resistance',
            },
            {
                name: 'Bronze Dragon (Lightning)',
                summary: 'Lightning Breath Weapon & Lightning Resistance',
            },
            {
                name: 'Silver Dragon (Cold)',
                summary: 'Cold Breath Weapon & Cold Resistance',
            },
            {
                name: 'White Dragon (Cold)',
                summary: 'Cold Breath Weapon & Cold Resistance',
            },
            {
                name: 'Black Dragon (Acid)',
                summary: 'Acid Breath Weapon & Acid Resistance',
            },
            {
                name: 'Copper Dragon (Acid)',
                summary: 'Acid Breath Weapon & Acid Resistance',
            },
            {
                name: 'Green Dragon (Poison)',
                summary: 'Poison Breath Weapon & Poison Resistance',
            },
        ],
        traits: [
            {
                name: 'Breath Weapon',
                description:
                    'When you take the Attack action, you can replace one attack with an exhalation of energy in a 15-ft Cone or 30-ft Line (1d10 damage, scaling with level; DEX save). Uses equal to Proficiency Bonus per Long Rest.',
            },
            {
                name: 'Damage Resistance',
                description:
                    'You have Resistance to the damage type determined by your Draconic Ancestry.',
            },
            {
                name: 'Darkvision (60 ft)',
                description: 'You have Darkvision with a range of 60 feet.',
            },
            {
                name: 'Draconic Flight (Level 5)',
                description:
                    'At level 5, sprout spectral wings as a Bonus Action for 10 minutes (Fly Speed = Speed, 1/Long Rest).',
            },
        ],
        languages: ['Common', 'Draconic'],
    },
    {
        name: 'Dwarf',
        summary:
            'Medium · 30 ft · Darkvision (120 ft), Dwarven Resilience, Dwarven Toughness (+1 HP/lvl), Stonecunning',
        description:
            'Raised from the earth in elder days, Dwarves are resilient as the mountains, with unmatched endurance and craftsmanship.',
        defaultSize: 'Medium',
        sizeOptions: ['Medium'],
        speed: 30,
        hpPerLevelBonus: 1,
        traits: [
            {
                name: 'Darkvision (120 ft)',
                description:
                    'You have superior Darkvision with a range of 120 feet.',
            },
            {
                name: 'Dwarven Resilience',
                description:
                    'You have Resistance to Poison damage and Advantage on saving throws to avoid or end the Poisoned condition.',
            },
            {
                name: 'Dwarven Toughness',
                description:
                    'Your Hit Point maximum increases by 1, and it increases by 1 again whenever you gain a level.',
            },
            {
                name: 'Stonecunning',
                description:
                    'As a Bonus Action, gain Tremorsense with a range of 60 feet for 10 minutes while on a stone surface (uses = Proficiency Bonus per Long Rest).',
            },
        ],
        languages: ['Common', 'Dwarvish'],
    },
    {
        name: 'Elf',
        summary:
            'Medium · 30–35 ft · Darkvision, Elven Lineage (Drow/High/Wood), Fey Ancestry, Keen Senses, Trance',
        description:
            'Created by the god Corellon, Elves possess keen senses, innate magic, and a deep connection to the Feywild.',
        defaultSize: 'Medium',
        sizeOptions: ['Medium'],
        speed: 30,
        lineageLabel: 'Elven Lineage',
        lineages: [
            {
                name: 'High Elf',
                summary:
                    '30 ft Speed · Prestidigitation cantrip (swappable on Long Rest) · Detect Magic (Lvl 3), Misty Step (Lvl 5).',
            },
            {
                name: 'Wood Elf',
                summary:
                    '35 ft Speed · Druidcraft cantrip · Longstrider (Lvl 3), Pass without Trace (Lvl 5).',
                speedOverride: 35,
            },
            {
                name: 'Drow',
                summary:
                    '120 ft Darkvision · Dancing Lights cantrip · Faerie Fire (Lvl 3), Darkness (Lvl 5).',
            },
        ],
        traits: [
            {
                name: 'Darkvision (60 ft / 120 ft Drow)',
                description:
                    'You have Darkvision with a range of 60 feet (120 feet if Drow).',
            },
            {
                name: 'Fey Ancestry',
                description:
                    'You have Advantage on saving throws you make to avoid or end the Charmed condition.',
            },
            {
                name: 'Keen Senses',
                description:
                    'You have proficiency in the Insight, Perception, or Survival skill.',
            },
            {
                name: 'Trance',
                description:
                    'Magic can’t put you to sleep, and you can finish a Long Rest in 4 hours of meditation.',
            },
        ],
        languages: ['Common', 'Elvish'],
    },
    {
        name: 'Gnome',
        summary:
            'Small · 30 ft · Darkvision, Gnomish Cunning (Adv on INT/WIS/CHA saves), Gnomish Lineage',
        description:
            'Magical folk created by gods of invention, Gnomes thrive on curiosity, humor, and clever trickery.',
        defaultSize: 'Small',
        sizeOptions: ['Small'],
        speed: 30,
        lineageLabel: 'Gnomish Lineage',
        lineages: [
            {
                name: 'Forest Gnome',
                summary:
                    'Know Minor Illusion cantrip and can cast Speak with Animals (uses = Proficiency Bonus/Long Rest).',
            },
            {
                name: 'Rock Gnome',
                summary:
                    'Know Mending and Prestidigitation cantrips; can create tiny clockwork devices.',
            },
        ],
        traits: [
            {
                name: 'Darkvision (60 ft)',
                description: 'You have Darkvision with a range of 60 feet.',
            },
            {
                name: 'Gnomish Cunning',
                description:
                    'You have Advantage on Intelligence, Wisdom, and Charisma saving throws.',
            },
        ],
        languages: ['Common', 'Gnomish'],
    },
    {
        name: 'Goliath',
        summary:
            'Medium · 35 ft · Giant Ancestry, Large Form (Lvl 5), Powerful Build',
        description:
            'Towering descendants of giants, Goliaths seek heights of physical challenge and invoke the supernatural boons of their ancestors.',
        defaultSize: 'Medium',
        sizeOptions: ['Medium'],
        speed: 35,
        lineageLabel: 'Giant Ancestry',
        lineages: [
            {
                name: "Cloud's Jaunt (Cloud Giant)",
                summary:
                    'Bonus Action teleport up to 30 feet to an unoccupied space you can see.',
            },
            {
                name: "Fire's Burn (Fire Giant)",
                summary:
                    'On a hit, deal an extra 1d10 Fire damage to the target.',
            },
            {
                name: "Frost's Chill (Frost Giant)",
                summary:
                    'On a hit, deal an extra 1d6 Cold damage and reduce target Speed by 10 ft.',
            },
            {
                name: "Hill's Tumble (Hill Giant)",
                summary:
                    'On a hit against a Large or smaller creature, knock it Prone.',
            },
            {
                name: "Stone's Endurance (Stone Giant)",
                summary:
                    'Reaction when taking damage: roll 1d12 + CON modifier to reduce that damage.',
            },
            {
                name: "Storm's Thunder (Storm Giant)",
                summary:
                    'Reaction when damaged by a creature within 60 ft: deal 1d8 Thunder damage to it.',
            },
        ],
        traits: [
            {
                name: 'Giant Ancestry',
                description:
                    'You can use your chosen supernatural Giant Ancestry boon a number of times equal to your Proficiency Bonus per Long Rest.',
            },
            {
                name: 'Large Form (Level 5)',
                description:
                    'Starting at level 5, change your size to Large as a Bonus Action for 10 minutes (Advantage on STR checks, +10 ft Speed, 1/Long Rest).',
            },
            {
                name: 'Powerful Build',
                description:
                    'You have Advantage on any ability check to end the Grappled condition, and you count as one size larger for carrying capacity.',
            },
        ],
        languages: ['Common', 'Giant'],
    },
    {
        name: 'Halfling',
        summary:
            'Small · 30 ft · Brave, Halfling Nimbleness, Luck (reroll 1s on d20), Naturally Stealthy',
        description:
            'Guided by community and luck, Halflings possess a practical courage and uncanny knack for slipping out of danger.',
        defaultSize: 'Small',
        sizeOptions: ['Small'],
        speed: 30,
        traits: [
            {
                name: 'Brave',
                description:
                    'You have Advantage on saving throws you make to avoid or end the Frightened condition.',
            },
            {
                name: 'Halfling Nimbleness',
                description:
                    'You can move through the space of any creature that is a size larger than you.',
            },
            {
                name: 'Luck',
                description:
                    'When you roll a 1 on the d20 of a d20 Test, you can reroll the die, and you must use the new roll.',
            },
            {
                name: 'Naturally Stealthy',
                description:
                    'You can take the Hide action even when you are obscured only by a creature that is at least one size larger than you.',
            },
        ],
        languages: ['Common', 'Halfling'],
    },
    {
        name: 'Orc',
        summary:
            'Medium · 30 ft · Adrenaline Rush (Bonus Action Dash + Temp HP), Darkvision (120 ft), Relentless Endurance',
        description:
            'Equipped with gifts to wander great plains and endure harsh battles, Orcs charge into danger with unmatched tenacity.',
        defaultSize: 'Medium',
        sizeOptions: ['Medium'],
        speed: 30,
        traits: [
            {
                name: 'Adrenaline Rush',
                description:
                    'You can take the Dash action as a Bonus Action and gain Temporary HP equal to your Proficiency Bonus (uses = Proficiency Bonus per Short or Long Rest).',
            },
            {
                name: 'Darkvision (120 ft)',
                description: 'You have Darkvision with a range of 120 feet.',
            },
            {
                name: 'Relentless Endurance',
                description:
                    'When you are reduced to 0 Hit Points but not killed outright, you can drop to 1 Hit Point instead (1/Long Rest).',
            },
        ],
        languages: ['Common', 'Orc'],
    },
    {
        name: 'Tiefling',
        summary:
            'Medium or Small · 30 ft · Darkvision, Fiendish Legacy (Abyssal/Chthonic/Infernal), Otherworldly Presence',
        description:
            'Touched by the Lower Planes, Tieflings wield fiendish resistance and innate planar spells.',
        defaultSize: 'Medium',
        sizeOptions: ['Medium', 'Small'],
        speed: 30,
        lineageLabel: 'Fiendish Legacy',
        lineages: [
            {
                name: 'Infernal Legacy',
                summary:
                    'Fire Resistance · Fire Bolt cantrip · Hellish Rebuke (Lvl 3), Darkness (Lvl 5).',
            },
            {
                name: 'Abyssal Legacy',
                summary:
                    'Poison Resistance · Poison Spray cantrip · Ray of Sickness (Lvl 3), Hold Person (Lvl 5).',
            },
            {
                name: 'Chthonic Legacy',
                summary:
                    'Necrotic Resistance · Chill Touch cantrip · False Life (Lvl 3), Ray of Enfeeblement (Lvl 5).',
            },
        ],
        traits: [
            {
                name: 'Darkvision (60 ft)',
                description: 'You have Darkvision with a range of 60 feet.',
            },
            {
                name: 'Fiendish Legacy',
                description:
                    'You gain Resistance and spells determined by your chosen Fiendish Legacy (Abyssal, Chthonic, or Infernal).',
            },
            {
                name: 'Otherworldly Presence',
                description: 'You know the Thaumaturgy cantrip.',
            },
        ],
        languages: ['Common', 'Infernal'],
    },
];

export const DND_CLASSES: DndClassDefinition[] = [
    {
        name: 'Barbarian',
        tagline: 'Primal warrior of relentless rage and raw resilience',
        description:
            'Barbarians are mighty warriors powered by primal forces of the multiverse that manifest as a Rage.',
        hitDie: 12,
        primaryAbility: 'Strength',
        savingThrows: ['str', 'con'],
        skillChoicesCount: 2,
        availableSkills: [
            'animal_handling',
            'athletics',
            'intimidation',
            'nature',
            'perception',
            'survival',
        ],
        recommendedSkills: ['athletics', 'perception'],
        armorProficiencies: 'Light Armor, Medium Armor, Shields',
        weaponProficiencies: 'Simple Weapons, Martial Weapons',
        weaponMasteryCount: 2,
        recommendedMasteries: ['Cleave', 'Topple'],
        spellcastingAbility: 'none',
        recommendedAbilities: {
            str: 15,
            dex: 13,
            con: 14,
            int: 8,
            wis: 12,
            cha: 10,
        },
        subclasses: [
            'Path of the Berserker',
            'Path of the Wild Heart',
            'Path of the World Tree',
            'Path of the Zealot',
        ],
        features: [
            {
                id: 'rage',
                name: 'Rage',
                description:
                    'Enter a Rage as a Bonus Action: gain Resistance to Bludgeoning, Piercing, and Slashing damage, +2 Rage Damage on STR attacks, and Advantage on STR checks and saves.',
            },
            {
                id: 'unarmored_defense_barb',
                name: 'Unarmored Defense',
                description:
                    'While not wearing any armor, your base Armor Class equals 10 + your Dexterity modifier + your Constitution modifier (you can use a Shield).',
            },
            {
                id: 'weapon_mastery_barb',
                name: 'Weapon Mastery',
                description:
                    'Use the mastery properties of two kinds of Simple or Martial Melee weapons of your choice.',
            },
        ],
        startingEquipment: {
            packageALabel: 'Greataxe, 4 Handaxes, Explorer’s Pack, 15 GP',
            goldA: 15,
            goldB: 75,
            weapons: [
                {
                    name: 'Greataxe',
                    category: 'Martial Melee',
                    damage: '1d12',
                    damage_type: 'slashing',
                    ability: 'str',
                    mastery: 'Cleave',
                    properties: ['Heavy', 'Two-Handed'],
                    range: 'Melee',
                    weight: '7 lb',
                },
                {
                    name: 'Handaxe',
                    category: 'Simple Melee',
                    damage: '1d6',
                    damage_type: 'slashing',
                    ability: 'str',
                    mastery: 'Vex',
                    properties: ['Light', 'Thrown (range 20/60)'],
                    range: '20/60 ft',
                    weight: '2 lb',
                },
            ],
            armors: [],
            inventory: [
                {
                    name: "Explorer's Pack",
                    quantity: 1,
                    weight: '59 lb',
                    summary:
                        'Backpack, bedroll, mess kit, tinderbox, 10 torches, 10 rations, waterskin, 50 ft rope',
                },
            ],
        },
    },
    {
        name: 'Bard',
        tagline: 'Inspiring performer weaving magic through music and words',
        description:
            'Invoking magic through music, dance, and verse, Bards are expert inspires of allies and masters of varied skills.',
        hitDie: 8,
        primaryAbility: 'Charisma',
        savingThrows: ['dex', 'cha'],
        skillChoicesCount: 3,
        availableSkills: SKILL_LIST.map((s) => s.key),
        recommendedSkills: ['persuasion', 'performance', 'deception'],
        armorProficiencies: 'Light Armor',
        weaponProficiencies: 'Simple Weapons',
        toolProficiencies: 'Three Musical Instruments',
        weaponMasteryCount: 0,
        recommendedMasteries: [],
        spellcastingAbility: 'cha',
        recommendedAbilities: {
            str: 8,
            dex: 14,
            con: 13,
            int: 12,
            wis: 10,
            cha: 15,
        },
        subclasses: [
            'College of Dance',
            'College of Glamour',
            'College of Lore',
            'College of Valor',
        ],
        features: [
            {
                id: 'bardic_inspiration',
                name: 'Bardic Inspiration (d6)',
                description:
                    'As a Bonus Action, inspire a creature within 60 ft with a d6 Bardic Inspiration die (lasts 1 hour; uses = CHA modifier per Long Rest).',
            },
            {
                id: 'spellcasting_bard',
                name: 'Spellcasting (Charisma)',
                description:
                    'Cast Bard spells using Charisma as your spellcasting ability and a Musical Instrument as a spellcasting focus.',
            },
        ],
        startingEquipment: {
            packageALabel:
                'Leather Armor, 2 Daggers, Musical Instrument, Entertainer’s Pack, 19 GP',
            goldA: 19,
            goldB: 90,
            weapons: [
                {
                    name: 'Dagger',
                    category: 'Simple Melee',
                    damage: '1d4',
                    damage_type: 'piercing',
                    ability: 'dex_or_str',
                    mastery: 'Nick',
                    properties: ['Finesse', 'Light', 'Thrown (range 20/60)'],
                    range: '20/60 ft',
                    weight: '1 lb',
                },
            ],
            armors: [
                {
                    name: 'Leather Armor',
                    category: 'Light',
                    ac_base: 11,
                    dex_bonus: 'full',
                    stealth_disadv: false,
                    equipped: true,
                    weight: '10 lb',
                },
            ],
            inventory: [
                {
                    name: 'Lute (Musical Instrument)',
                    quantity: 1,
                    weight: '2 lb',
                    summary: 'Bardic spellcasting focus and instrument',
                },
            ],
        },
        recommendedSpells: [
            {
                name: 'Vicious Mockery',
                level: 0,
                school: 'Enchantment',
                casting_time: '1 Action',
                range: '60 ft',
                components: 'V',
                duration: 'Instantaneous',
                save_ability: 'wis',
                damage: '1d4',
                damage_type: 'psychic',
                summary: 'Wis Save · 1d4 Psychic · Disadvantage on next attack',
                prepared: true,
            },
            {
                name: 'Healing Word',
                level: 1,
                school: 'Abjuration',
                casting_time: '1 Bonus Action',
                range: '60 ft',
                components: 'V',
                duration: 'Instantaneous',
                summary: '1 Bonus Action · 60 ft · 1d4 + Mod Heal',
                prepared: true,
            },
        ],
    },
    {
        name: 'Cleric',
        tagline:
            'Divine conduit wielding radiant power and restorative miracles',
        description:
            'Clerics draw power from the realms of the gods and harness it to work miracles, bolster allies, and smite foes.',
        hitDie: 8,
        primaryAbility: 'Wisdom',
        savingThrows: ['wis', 'cha'],
        skillChoicesCount: 2,
        availableSkills: [
            'history',
            'insight',
            'medicine',
            'persuasion',
            'religion',
        ],
        recommendedSkills: ['insight', 'religion'],
        armorProficiencies: 'Light Armor, Medium Armor, Shields',
        weaponProficiencies: 'Simple Weapons',
        weaponMasteryCount: 0,
        recommendedMasteries: [],
        spellcastingAbility: 'wis',
        recommendedAbilities: {
            str: 14,
            dex: 10,
            con: 13,
            int: 8,
            wis: 15,
            cha: 12,
        },
        subclasses: [
            'Life Domain',
            'Light Domain',
            'Trickery Domain',
            'War Domain',
        ],
        features: [
            {
                id: 'divine_order',
                name: 'Divine Order',
                description:
                    'You have dedicated yourself to one of the following sacred roles of your choice:',
                choices: {
                    label: 'Choose your Divine Order',
                    options: [
                        {
                            name: 'Protector',
                            summary:
                                'Gain proficiency with Martial weapons and training with Heavy armor.',
                        },
                        {
                            name: 'Thaumaturge',
                            summary:
                                'Know one extra Cleric cantrip, and add your Wisdom modifier to Intelligence (Arcana or Religion) checks.',
                        },
                    ],
                },
            },
            {
                id: 'spellcasting_cleric',
                name: 'Spellcasting (Wisdom)',
                description:
                    'Cast Cleric spells using Wisdom as your spellcasting ability and a Holy Symbol as a focus.',
            },
        ],
        startingEquipment: {
            packageALabel:
                'Chain Shirt, Shield, Mace, Holy Symbol, Priest’s Pack, 7 GP',
            goldA: 7,
            goldB: 110,
            weapons: [
                {
                    name: 'Mace',
                    category: 'Simple Melee',
                    damage: '1d6',
                    damage_type: 'bludgeoning',
                    ability: 'str',
                    mastery: 'Slow',
                    properties: [],
                    range: 'Melee',
                    weight: '4 lb',
                },
            ],
            armors: [
                {
                    name: 'Chain Shirt',
                    category: 'Medium',
                    ac_base: 13,
                    dex_bonus: 'max_2',
                    stealth_disadv: false,
                    equipped: true,
                    weight: '20 lb',
                },
                {
                    name: 'Shield',
                    category: 'Shield',
                    ac_bonus: 2,
                    dex_bonus: 'none',
                    stealth_disadv: false,
                    equipped: true,
                    weight: '6 lb',
                },
            ],
            inventory: [
                {
                    name: 'Holy Symbol',
                    quantity: 1,
                    weight: '1 lb',
                    summary: 'Divine spellcasting focus',
                },
            ],
        },
        recommendedSpells: [
            {
                name: 'Sacred Flame',
                level: 0,
                school: 'Evocation',
                casting_time: '1 Action',
                range: '60 ft',
                components: 'V, S',
                duration: 'Instantaneous',
                save_ability: 'dex',
                damage: '1d8',
                damage_type: 'radiant',
                summary: 'Dex Save · 1d8 Radiant',
                prepared: true,
            },
            {
                name: 'Guidance',
                level: 0,
                school: 'Divination',
                casting_time: '1 Action',
                range: 'Touch',
                components: 'V, S',
                duration: 'Concentration, up to 1 minute',
                summary: '+1d4 to one ability check',
                prepared: true,
            },
            {
                name: 'Bless',
                level: 1,
                school: 'Enchantment',
                casting_time: '1 Action',
                range: '30 ft',
                components: 'V, S, M',
                duration: 'Concentration, up to 1 minute',
                summary: '+1d4 to attacks & saves for 3 targets',
                prepared: true,
            },
            {
                name: 'Guiding Bolt',
                level: 1,
                school: 'Evocation',
                casting_time: '1 Action',
                range: '120 ft',
                components: 'V, S',
                duration: '1 round',
                attack_type: 'ranged_spell',
                damage: '4d6',
                damage_type: 'radiant',
                summary: '4d6 Radiant + Advantage on next attack',
                prepared: true,
            },
        ],
    },
    {
        name: 'Druid',
        tagline: 'Primal guardian channeling nature’s wrath and Wild Shape',
        description:
            'Druids belong to ancient orders that call on the forces of nature, harnessing primal spells and animal forms.',
        hitDie: 8,
        primaryAbility: 'Wisdom',
        savingThrows: ['int', 'wis'],
        skillChoicesCount: 2,
        availableSkills: [
            'animal_handling',
            'arcana',
            'insight',
            'medicine',
            'nature',
            'perception',
            'religion',
            'survival',
        ],
        recommendedSkills: ['nature', 'perception'],
        armorProficiencies: 'Light Armor, Shields',
        weaponProficiencies: 'Simple Weapons',
        toolProficiencies: 'Herbalism Kit',
        weaponMasteryCount: 0,
        recommendedMasteries: [],
        spellcastingAbility: 'wis',
        recommendedAbilities: {
            str: 8,
            dex: 14,
            con: 13,
            int: 12,
            wis: 15,
            cha: 10,
        },
        subclasses: [
            'Circle of the Land',
            'Circle of the Moon',
            'Circle of the Sea',
            'Circle of the Stars',
        ],
        features: [
            {
                id: 'primal_order',
                name: 'Primal Order',
                description:
                    'You have dedicated yourself to one of the following sacred roles of your choice:',
                choices: {
                    label: 'Choose your Primal Order',
                    options: [
                        {
                            name: 'Magician',
                            summary:
                                'Know one extra Druid cantrip, and add your Wisdom modifier to Intelligence (Arcana or Nature) checks.',
                        },
                        {
                            name: 'Warden',
                            summary:
                                'Gain proficiency with Martial weapons and training with Medium armor.',
                        },
                    ],
                },
            },
            {
                id: 'druidic',
                name: 'Druidic',
                description:
                    'You know Druidic, the secret language of Druids, and always have the Speak with Animals spell prepared.',
            },
            {
                id: 'spellcasting_druid',
                name: 'Spellcasting (Wisdom)',
                description:
                    'Cast Druid spells using Wisdom as your spellcasting ability.',
            },
        ],
        startingEquipment: {
            packageALabel:
                'Leather Armor, Shield, Sickle, Quarterstaff, Explorer’s Pack, Herbalism Kit, 9 GP',
            goldA: 9,
            goldB: 50,
            weapons: [
                {
                    name: 'Quarterstaff',
                    category: 'Simple Melee',
                    damage: '1d6',
                    damage_type: 'bludgeoning',
                    ability: 'str',
                    mastery: 'Topple',
                    properties: ['Versatile (1d8)'],
                    range: 'Melee',
                    weight: '4 lb',
                },
            ],
            armors: [
                {
                    name: 'Leather Armor',
                    category: 'Light',
                    ac_base: 11,
                    dex_bonus: 'full',
                    stealth_disadv: false,
                    equipped: true,
                    weight: '10 lb',
                },
                {
                    name: 'Shield',
                    category: 'Shield',
                    ac_bonus: 2,
                    dex_bonus: 'none',
                    stealth_disadv: false,
                    equipped: true,
                    weight: '6 lb',
                },
            ],
            inventory: [
                {
                    name: "Explorer's Pack",
                    quantity: 1,
                    weight: '59 lb',
                    summary: 'Standard wilderness exploration pack',
                },
            ],
        },
        recommendedSpells: [
            {
                name: 'Guidance',
                level: 0,
                school: 'Divination',
                casting_time: '1 Action',
                range: 'Touch',
                components: 'V, S',
                duration: 'Concentration, up to 1 minute',
                summary: '+1d4 to one ability check',
                prepared: true,
            },
            {
                name: 'Cure Wounds',
                level: 1,
                school: 'Abjuration',
                casting_time: '1 Action',
                range: 'Touch',
                components: 'V, S',
                duration: 'Instantaneous',
                summary: 'Touch · 2d8 + Mod Heal',
                prepared: true,
            },
            {
                name: 'Thunderwave',
                level: 1,
                school: 'Evocation',
                casting_time: '1 Action',
                range: 'Self (15-ft cube)',
                components: 'V, S',
                duration: 'Instantaneous',
                save_ability: 'con',
                damage: '2d8',
                damage_type: 'thunder',
                summary: 'Con Save · 2d8 Thunder · Push 10 ft',
                prepared: true,
            },
        ],
    },
    {
        name: 'Fighter',
        tagline: 'Master of martial combat, weapons, and tactical grit',
        description:
            'Fighters rule many battlefields. Questing knights, royal champions, elite soldiers, and hardened mercenaries—all share an unparalleled mastery with weapons and armor.',
        hitDie: 10,
        primaryAbility: 'Strength or Dexterity',
        savingThrows: ['str', 'con'],
        skillChoicesCount: 2,
        availableSkills: [
            'acrobatics',
            'animal_handling',
            'athletics',
            'history',
            'insight',
            'intimidation',
            'persuasion',
            'perception',
            'survival',
        ],
        recommendedSkills: ['athletics', 'perception'],
        armorProficiencies: 'Light Armor, Medium Armor, Heavy Armor, Shields',
        weaponProficiencies: 'Simple Weapons, Martial Weapons',
        weaponMasteryCount: 3,
        recommendedMasteries: ['Sap', 'Graze', 'Topple'],
        spellcastingAbility: 'none',
        recommendedAbilities: {
            str: 15,
            dex: 13,
            con: 14,
            int: 10,
            wis: 12,
            cha: 8,
        },
        subclasses: [
            'Battle Master',
            'Champion',
            'Eldritch Knight',
            'Psi Warrior',
        ],
        features: [
            {
                id: 'fighting_style',
                name: 'Fighting Style',
                description:
                    'You have honed your martial prowess and gain a Fighting Style feat of your choice:',
                choices: {
                    label: 'Choose your Fighting Style',
                    options: [
                        {
                            name: 'Defense',
                            summary:
                                '+1 bonus to Armor Class while wearing Light, Medium, or Heavy armor.',
                        },
                        {
                            name: 'Dueling',
                            summary:
                                '+2 bonus to damage rolls when wielding a melee weapon in one hand and no other weapons.',
                        },
                        {
                            name: 'Great Weapon Fighting',
                            summary:
                                'When rolling damage for a two-handed melee weapon, treat any 1 or 2 on a damage die as a 3.',
                        },
                        {
                            name: 'Archery',
                            summary:
                                '+2 bonus to attack rolls you make with Ranged weapons.',
                        },
                        {
                            name: 'Two-Weapon Fighting',
                            summary:
                                'Add your ability modifier to the damage of the extra attack made with a Light weapon.',
                        },
                        {
                            name: 'Protection',
                            summary:
                                'Use Reaction with a Shield to impose Disadvantage on attacks against an ally within 5 ft.',
                        },
                    ],
                },
            },
            {
                id: 'second_wind',
                name: 'Second Wind',
                description:
                    'As a Bonus Action, regain Hit Points equal to 1d10 + your Fighter level (2 uses, regain 1 on Short Rest, all on Long Rest). Also Tactical Mind at Level 2.',
            },
            {
                id: 'weapon_mastery_fighter',
                name: 'Weapon Mastery (3 Weapons)',
                description:
                    'Use the mastery properties of three kinds of Simple or Martial weapons of your choice.',
            },
        ],
        startingEquipment: {
            packageALabel:
                'Chain Mail, Longsword, Shield, Javelin, Dungeoneer’s Pack, 4 GP',
            goldA: 4,
            goldB: 155,
            weapons: [
                {
                    name: 'Longsword',
                    category: 'Martial Melee',
                    damage: '1d8',
                    damage_type: 'slashing',
                    ability: 'str',
                    mastery: 'Sap',
                    properties: ['Versatile (1d10)'],
                    range: 'Melee',
                    weight: '3 lb',
                },
                {
                    name: 'Javelin',
                    category: 'Simple Melee',
                    damage: '1d6',
                    damage_type: 'piercing',
                    ability: 'str',
                    mastery: 'Slow',
                    properties: ['Thrown (range 30/120)'],
                    range: '30/120 ft',
                    weight: '2 lb',
                },
            ],
            armors: [
                {
                    name: 'Chain Mail',
                    category: 'Heavy',
                    ac_base: 16,
                    dex_bonus: 'none',
                    stealth_disadv: true,
                    str_req: 13,
                    equipped: true,
                    weight: '55 lb',
                },
                {
                    name: 'Shield',
                    category: 'Shield',
                    ac_bonus: 2,
                    dex_bonus: 'none',
                    stealth_disadv: false,
                    equipped: true,
                    weight: '6 lb',
                },
            ],
            inventory: [
                {
                    name: "Dungeoneer's Pack",
                    quantity: 1,
                    weight: '61.5 lb',
                    summary:
                        'Backpack, crowbar, hammer, 10 pitons, 10 torches, tinderbox, 10 rations, waterskin, 50 ft rope',
                },
            ],
        },
    },
    {
        name: 'Monk',
        tagline: 'Unarmored martial artist harnessing internal Focus',
        description:
            'Monks use rigorous combat training and mental discipline to align themselves with the multiverse and channel internal reservoirs of power.',
        hitDie: 8,
        primaryAbility: 'Dexterity & Wisdom',
        savingThrows: ['str', 'dex'],
        skillChoicesCount: 2,
        availableSkills: [
            'acrobatics',
            'athletics',
            'history',
            'insight',
            'religion',
            'stealth',
        ],
        recommendedSkills: ['acrobatics', 'stealth'],
        armorProficiencies: 'None (Unarmored Defense)',
        weaponProficiencies:
            'Simple Weapons, Martial Weapons with the Light property',
        toolProficiencies: "One Artisan's Tool or Musical Instrument",
        weaponMasteryCount: 0,
        recommendedMasteries: [],
        spellcastingAbility: 'none',
        recommendedAbilities: {
            str: 12,
            dex: 15,
            con: 13,
            int: 10,
            wis: 14,
            cha: 8,
        },
        subclasses: [
            'Warrior of Mercy',
            'Warrior of Shadow',
            'Warrior of the Elements',
            'Warrior of the Open Hand',
        ],
        features: [
            {
                id: 'martial_arts',
                name: 'Martial Arts (1d6)',
                description:
                    'Use Dexterity instead of Strength for Unarmed Strikes and Monk weapons, deal 1d6 Martial Arts die damage, and make one Bonus Unarmed Strike every turn.',
            },
            {
                id: 'unarmored_defense_monk',
                name: 'Unarmored Defense',
                description:
                    'While not wearing armor or wielding a Shield, your base Armor Class equals 10 + your Dexterity modifier + your Wisdom modifier.',
            },
        ],
        startingEquipment: {
            packageALabel: 'Spear, 5 Daggers, Explorer’s Pack, 11 GP',
            goldA: 11,
            goldB: 50,
            weapons: [
                {
                    name: 'Spear',
                    category: 'Simple Melee',
                    damage: '1d6',
                    damage_type: 'piercing',
                    ability: 'dex_or_str',
                    mastery: 'Sap',
                    properties: ['Thrown (range 20/60)', 'Versatile (1d8)'],
                    range: '20/60 ft',
                    weight: '3 lb',
                },
                {
                    name: 'Dagger',
                    category: 'Simple Melee',
                    damage: '1d4',
                    damage_type: 'piercing',
                    ability: 'dex_or_str',
                    mastery: 'Nick',
                    properties: ['Finesse', 'Light', 'Thrown (range 20/60)'],
                    range: '20/60 ft',
                    weight: '1 lb',
                },
            ],
            armors: [],
            inventory: [
                {
                    name: "Explorer's Pack",
                    quantity: 1,
                    weight: '59 lb',
                    summary: 'Standard wilderness exploration pack',
                },
            ],
        },
    },
    {
        name: 'Paladin',
        tagline: 'Oath-bound holy champion uniting steel and divine magic',
        description:
            'Paladins are united by their oaths to stand against the forces of annihilation and corruption, smiting foes and healing the wounded.',
        hitDie: 10,
        primaryAbility: 'Strength & Charisma',
        savingThrows: ['wis', 'cha'],
        skillChoicesCount: 2,
        availableSkills: [
            'athletics',
            'insight',
            'intimidation',
            'medicine',
            'persuasion',
            'religion',
        ],
        recommendedSkills: ['athletics', 'persuasion'],
        armorProficiencies: 'Light Armor, Medium Armor, Heavy Armor, Shields',
        weaponProficiencies: 'Simple Weapons, Martial Weapons',
        weaponMasteryCount: 2,
        recommendedMasteries: ['Sap', 'Topple'],
        spellcastingAbility: 'cha',
        recommendedAbilities: {
            str: 15,
            dex: 10,
            con: 13,
            int: 8,
            wis: 12,
            cha: 14,
        },
        subclasses: [
            'Oath of Devotion',
            'Oath of Glory',
            'Oath of the Ancients',
            'Oath of Vengeance',
        ],
        features: [
            {
                id: 'lay_on_hands',
                name: 'Lay on Hands',
                description:
                    'You have a pool of healing power equal to 5 × your Paladin level. As a Bonus Action, touch a creature to restore HP or expend 5 HP to remove Poisoned.',
            },
            {
                id: 'spellcasting_paladin',
                name: 'Spellcasting (Charisma)',
                description:
                    'Prepare and cast Paladin spells starting at Level 1 using Charisma.',
            },
            {
                id: 'weapon_mastery_paladin',
                name: 'Weapon Mastery (2 Weapons)',
                description:
                    'Use the mastery properties of two kinds of weapons of your choice.',
            },
        ],
        startingEquipment: {
            packageALabel:
                'Chain Mail, Shield, Longsword, 6 Javelins, Holy Symbol, Priest’s Pack, 9 GP',
            goldA: 9,
            goldB: 150,
            weapons: [
                {
                    name: 'Longsword',
                    category: 'Martial Melee',
                    damage: '1d8',
                    damage_type: 'slashing',
                    ability: 'str',
                    mastery: 'Sap',
                    properties: ['Versatile (1d10)'],
                    range: 'Melee',
                    weight: '3 lb',
                },
                {
                    name: 'Javelin',
                    category: 'Simple Melee',
                    damage: '1d6',
                    damage_type: 'piercing',
                    ability: 'str',
                    mastery: 'Slow',
                    properties: ['Thrown (range 30/120)'],
                    range: '30/120 ft',
                    weight: '2 lb',
                },
            ],
            armors: [
                {
                    name: 'Chain Mail',
                    category: 'Heavy',
                    ac_base: 16,
                    dex_bonus: 'none',
                    stealth_disadv: true,
                    str_req: 13,
                    equipped: true,
                    weight: '55 lb',
                },
                {
                    name: 'Shield',
                    category: 'Shield',
                    ac_bonus: 2,
                    dex_bonus: 'none',
                    stealth_disadv: false,
                    equipped: true,
                    weight: '6 lb',
                },
            ],
            inventory: [
                {
                    name: 'Holy Symbol',
                    quantity: 1,
                    weight: '1 lb',
                    summary: 'Paladin spellcasting focus',
                },
            ],
        },
        recommendedSpells: [
            {
                name: 'Bless',
                level: 1,
                school: 'Enchantment',
                casting_time: '1 Action',
                range: '30 ft',
                components: 'V, S, M',
                duration: 'Concentration, up to 1 minute',
                summary: '+1d4 to attacks & saves for 3 targets',
                prepared: true,
            },
            {
                name: 'Cure Wounds',
                level: 1,
                school: 'Abjuration',
                casting_time: '1 Action',
                range: 'Touch',
                components: 'V, S',
                duration: 'Instantaneous',
                summary: 'Touch · 2d8 + Mod Heal',
                prepared: true,
            },
        ],
    },
    {
        name: 'Ranger',
        tagline:
            'Wilderness hunter combining martial precision and primal spells',
        description:
            'Far from bustling cities, Rangers keep their unending watch in the wilderness, hunting monsters with lethal quarry marks.',
        hitDie: 10,
        primaryAbility: 'Dexterity & Wisdom',
        savingThrows: ['str', 'dex'],
        skillChoicesCount: 3,
        availableSkills: [
            'animal_handling',
            'athletics',
            'insight',
            'investigation',
            'nature',
            'perception',
            'stealth',
            'survival',
        ],
        recommendedSkills: ['perception', 'stealth', 'survival'],
        armorProficiencies: 'Light Armor, Medium Armor, Shields',
        weaponProficiencies: 'Simple Weapons, Martial Weapons',
        weaponMasteryCount: 2,
        recommendedMasteries: ['Vex', 'Nick'],
        spellcastingAbility: 'wis',
        recommendedAbilities: {
            str: 12,
            dex: 15,
            con: 13,
            int: 10,
            wis: 14,
            cha: 8,
        },
        subclasses: ['Beast Master', 'Fey Wanderer', 'Gloom Stalker', 'Hunter'],
        features: [
            {
                id: 'favored_enemy',
                name: "Favored Enemy (Hunter's Mark)",
                description:
                    "You always have the Hunter's Mark spell prepared and can cast it twice without expending a spell slot per Long Rest.",
            },
            {
                id: 'spellcasting_ranger',
                name: 'Spellcasting (Wisdom)',
                description:
                    'Prepare and cast Ranger spells starting at Level 1 using Wisdom.',
            },
            {
                id: 'weapon_mastery_ranger',
                name: 'Weapon Mastery (2 Weapons)',
                description:
                    'Use the mastery properties of two kinds of weapons of your choice.',
            },
        ],
        startingEquipment: {
            packageALabel:
                'Studded Leather, Scimitar, Shortsword, Longbow, 20 Arrows, Explorer’s Pack, 7 GP',
            goldA: 7,
            goldB: 150,
            weapons: [
                {
                    name: 'Longbow',
                    category: 'Martial Ranged',
                    damage: '1d8',
                    damage_type: 'piercing',
                    ability: 'dex',
                    mastery: 'Slow',
                    properties: [
                        'Ammunition (range 150/600)',
                        'Heavy',
                        'Two-Handed',
                    ],
                    range: '150/600 ft',
                    weight: '2 lb',
                },
                {
                    name: 'Shortsword',
                    category: 'Martial Melee',
                    damage: '1d6',
                    damage_type: 'piercing',
                    ability: 'dex_or_str',
                    mastery: 'Vex',
                    properties: ['Finesse', 'Light'],
                    range: 'Melee',
                    weight: '2 lb',
                },
                {
                    name: 'Scimitar',
                    category: 'Martial Melee',
                    damage: '1d6',
                    damage_type: 'slashing',
                    ability: 'dex_or_str',
                    mastery: 'Nick',
                    properties: ['Finesse', 'Light'],
                    range: 'Melee',
                    weight: '3 lb',
                },
            ],
            armors: [
                {
                    name: 'Studded Leather',
                    category: 'Light',
                    ac_base: 12,
                    dex_bonus: 'full',
                    stealth_disadv: false,
                    equipped: true,
                    weight: '13 lb',
                },
            ],
            inventory: [
                {
                    name: "Explorer's Pack",
                    quantity: 1,
                    weight: '59 lb',
                    summary: 'Standard wilderness exploration pack',
                },
            ],
        },
        recommendedSpells: [
            {
                name: 'Cure Wounds',
                level: 1,
                school: 'Abjuration',
                casting_time: '1 Action',
                range: 'Touch',
                components: 'V, S',
                duration: 'Instantaneous',
                summary: 'Touch · 2d8 + Mod Heal',
                prepared: true,
            },
        ],
    },
    {
        name: 'Rogue',
        tagline:
            'Deadly specialist of stealth, precision strikes, and expertise',
        description:
            'Rogues rely on skill, stealth, and their foes’ vulnerabilities to get the upper hand in any situation.',
        hitDie: 8,
        primaryAbility: 'Dexterity',
        savingThrows: ['dex', 'int'],
        skillChoicesCount: 4,
        availableSkills: [
            'acrobatics',
            'athletics',
            'deception',
            'insight',
            'intimidation',
            'investigation',
            'perception',
            'persuasion',
            'sleight_of_hand',
            'stealth',
        ],
        recommendedSkills: [
            'stealth',
            'sleight_of_hand',
            'perception',
            'investigation',
        ],
        armorProficiencies: 'Light Armor',
        weaponProficiencies:
            'Simple Weapons, Martial Weapons with Finesse or Light property',
        toolProficiencies: "Thieves' Tools",
        weaponMasteryCount: 2,
        recommendedMasteries: ['Vex', 'Nick'],
        spellcastingAbility: 'none',
        recommendedAbilities: {
            str: 8,
            dex: 15,
            con: 13,
            int: 14,
            wis: 12,
            cha: 10,
        },
        subclasses: ['Arcane Trickster', 'Assassin', 'Soulknife', 'Thief'],
        features: [
            {
                id: 'expertise_rogue',
                name: 'Expertise (2 Skills)',
                description:
                    'Your Proficiency Bonus is doubled for ability checks you make using two of your skill proficiencies (Sleight of Hand and Stealth recommended).',
            },
            {
                id: 'sneak_attack',
                name: 'Sneak Attack (1d6)',
                description:
                    'Once per turn, deal an extra 1d6 damage (scaling with level) to one creature you hit with a Finesse or Ranged weapon if you have Advantage or an ally within 5 ft of the target.',
            },
            {
                id: 'thieves_cant',
                name: "Thieves' Cant",
                description:
                    "You know Thieves' Cant and one additional language of your choice.",
            },
            {
                id: 'weapon_mastery_rogue',
                name: 'Weapon Mastery (2 Weapons)',
                description:
                    'Use the mastery properties of two kinds of weapons of your choice.',
            },
        ],
        startingEquipment: {
            packageALabel:
                'Leather Armor, 2 Daggers, Shortsword, Shortbow, 20 Arrows, Thieves’ Tools, Burglar’s Pack, 8 GP',
            goldA: 8,
            goldB: 100,
            weapons: [
                {
                    name: 'Shortsword',
                    category: 'Martial Melee',
                    damage: '1d6',
                    damage_type: 'piercing',
                    ability: 'dex_or_str',
                    mastery: 'Vex',
                    properties: ['Finesse', 'Light'],
                    range: 'Melee',
                    weight: '2 lb',
                },
                {
                    name: 'Dagger',
                    category: 'Simple Melee',
                    damage: '1d4',
                    damage_type: 'piercing',
                    ability: 'dex_or_str',
                    mastery: 'Nick',
                    properties: ['Finesse', 'Light', 'Thrown (range 20/60)'],
                    range: '20/60 ft',
                    weight: '1 lb',
                },
                {
                    name: 'Shortbow',
                    category: 'Simple Ranged',
                    damage: '1d6',
                    damage_type: 'piercing',
                    ability: 'dex',
                    mastery: 'Vex',
                    properties: ['Ammunition (range 80/320)', 'Two-Handed'],
                    range: '80/320 ft',
                    weight: '2 lb',
                },
            ],
            armors: [
                {
                    name: 'Leather Armor',
                    category: 'Light',
                    ac_base: 11,
                    dex_bonus: 'full',
                    stealth_disadv: false,
                    equipped: true,
                    weight: '10 lb',
                },
            ],
            inventory: [
                {
                    name: "Thieves' Tools",
                    quantity: 1,
                    weight: '1 lb',
                    summary: 'Lockpicks, small file, pliers, and mirror',
                },
            ],
        },
    },
    {
        name: 'Sorcerer',
        tagline: 'Innate spellcaster brimming with raw arcane bloodline power',
        description:
            'Sorcerers wield magic that is stamped into their very being, unleashing Innate Sorcery and Metamagic.',
        hitDie: 6,
        primaryAbility: 'Charisma',
        savingThrows: ['con', 'cha'],
        skillChoicesCount: 2,
        availableSkills: [
            'arcana',
            'deception',
            'insight',
            'intimidation',
            'persuasion',
            'religion',
        ],
        recommendedSkills: ['arcana', 'persuasion'],
        armorProficiencies: 'None',
        weaponProficiencies: 'Simple Weapons',
        weaponMasteryCount: 0,
        recommendedMasteries: [],
        spellcastingAbility: 'cha',
        recommendedAbilities: {
            str: 8,
            dex: 13,
            con: 14,
            int: 12,
            wis: 10,
            cha: 15,
        },
        subclasses: [
            'Aberrant Sorcery',
            'Clockwork Sorcery',
            'Draconic Sorcery',
            'Wild Magic Sorcery',
        ],
        features: [
            {
                id: 'innate_sorcery',
                name: 'Innate Sorcery',
                description:
                    'As a Bonus Action, unleash your simmering magic for 1 minute: the spell save DC of your Sorcerer spells increases by 1, and you have Advantage on the attack rolls of Sorcerer spells you cast (2/Long Rest).',
            },
            {
                id: 'spellcasting_sorcerer',
                name: 'Spellcasting (Charisma)',
                description:
                    'Cast Sorcerer spells using Charisma as your spellcasting ability.',
            },
        ],
        startingEquipment: {
            packageALabel:
                'Spear, 2 Daggers, Arcane Focus, Dungeoneer’s Pack, 28 GP',
            goldA: 28,
            goldB: 50,
            weapons: [
                {
                    name: 'Dagger',
                    category: 'Simple Melee',
                    damage: '1d4',
                    damage_type: 'piercing',
                    ability: 'dex_or_str',
                    mastery: 'Nick',
                    properties: ['Finesse', 'Light', 'Thrown (range 20/60)'],
                    range: '20/60 ft',
                    weight: '1 lb',
                },
            ],
            armors: [],
            inventory: [
                {
                    name: "Dungeoneer's Pack",
                    quantity: 1,
                    weight: '61.5 lb',
                    summary: 'Standard dungeon exploration pack',
                },
            ],
        },
        recommendedSpells: [
            {
                name: 'Fire Bolt',
                level: 0,
                school: 'Evocation',
                casting_time: '1 Action',
                range: '120 ft',
                components: 'V, S',
                duration: 'Instantaneous',
                attack_type: 'ranged_spell',
                damage: '1d10',
                damage_type: 'fire',
                summary: '120 ft · 1d10 Fire',
                prepared: true,
            },
            {
                name: 'Ray of Frost',
                level: 0,
                school: 'Evocation',
                casting_time: '1 Action',
                range: '60 ft',
                components: 'V, S',
                duration: 'Instantaneous',
                attack_type: 'ranged_spell',
                damage: '1d8',
                damage_type: 'cold',
                summary: '60 ft · 1d8 Cold · -10 ft Speed',
                prepared: true,
            },
            {
                name: 'Magic Missile',
                level: 1,
                school: 'Evocation',
                casting_time: '1 Action',
                range: '120 ft',
                components: 'V, S',
                duration: 'Instantaneous',
                damage: '3x (1d4+1)',
                damage_type: 'force',
                summary: '120 ft · 3x (1d4+1) Force',
                prepared: true,
            },
            {
                name: 'Shield',
                level: 1,
                school: 'Abjuration',
                casting_time: '1 Reaction',
                range: 'Self',
                components: 'V, S',
                duration: '1 round',
                summary: '1 Reaction · +5 AC until next turn',
                prepared: true,
            },
        ],
    },
    {
        name: 'Warlock',
        tagline: 'Occult seeker bound to an otherworldly patron’s pacts',
        description:
            'Warlocks quest for knowledge that lies hidden in the fabric of the multiverse, unlocking Eldritch Invocations and Pact Magic.',
        hitDie: 8,
        primaryAbility: 'Charisma',
        savingThrows: ['wis', 'cha'],
        skillChoicesCount: 2,
        availableSkills: [
            'arcana',
            'deception',
            'history',
            'intimidation',
            'investigation',
            'nature',
            'religion',
        ],
        recommendedSkills: ['arcana', 'deception'],
        armorProficiencies: 'Light Armor',
        weaponProficiencies: 'Simple Weapons',
        weaponMasteryCount: 0,
        recommendedMasteries: [],
        spellcastingAbility: 'cha',
        recommendedAbilities: {
            str: 8,
            dex: 14,
            con: 13,
            int: 12,
            wis: 10,
            cha: 15,
        },
        subclasses: [
            'Archfey Patron',
            'Celestial Patron',
            'Fiend Patron',
            'Great Old One Patron',
        ],
        features: [
            {
                id: 'eldritch_invocations',
                name: 'Eldritch Invocations (1 Invocation)',
                description:
                    'You have unearthed Eldritch Invocations, pieces of forbidden knowledge that imbue you with an abiding magical ability:',
                choices: {
                    label: 'Choose your Level 1 Eldritch Invocation',
                    options: [
                        {
                            name: 'Pact of the Blade',
                            summary:
                                'Conjure a pact melee weapon and use your Charisma modifier for its attack and damage rolls.',
                        },
                        {
                            name: 'Pact of the Chain',
                            summary:
                                'Learn Find Familiar and summon special forms (Imp, Pseudodragon, Quasit, Sphinx of Wonder).',
                        },
                        {
                            name: 'Pact of the Tome',
                            summary:
                                'Conjure a Book of Shadows granting 3 cantrips and 2 Level 1 ritual spells from any class.',
                        },
                        {
                            name: 'Eldritch Mind',
                            summary:
                                'You have Advantage on Constitution saving throws that you make to maintain Concentration.',
                        },
                    ],
                },
            },
            {
                id: 'pact_magic',
                name: 'Pact Magic (Charisma)',
                description:
                    'Cast Warlock spells using Charisma; regain all expended Pact Magic spell slots on a Short or Long Rest.',
            },
        ],
        startingEquipment: {
            packageALabel:
                'Leather Armor, Sickle, 2 Daggers, Arcane Focus, Scholar’s Pack, 15 GP',
            goldA: 15,
            goldB: 100,
            weapons: [
                {
                    name: 'Dagger',
                    category: 'Simple Melee',
                    damage: '1d4',
                    damage_type: 'piercing',
                    ability: 'dex_or_str',
                    mastery: 'Nick',
                    properties: ['Finesse', 'Light', 'Thrown (range 20/60)'],
                    range: '20/60 ft',
                    weight: '1 lb',
                },
            ],
            armors: [
                {
                    name: 'Leather Armor',
                    category: 'Light',
                    ac_base: 11,
                    dex_bonus: 'full',
                    stealth_disadv: false,
                    equipped: true,
                    weight: '10 lb',
                },
            ],
            inventory: [
                {
                    name: 'Orb (Arcane Focus)',
                    quantity: 1,
                    weight: '3 lb',
                    summary: 'Occult spellcasting focus',
                },
            ],
        },
        recommendedSpells: [
            {
                name: 'Eldritch Blast',
                level: 0,
                school: 'Evocation',
                casting_time: '1 Action',
                range: '120 ft',
                components: 'V, S',
                duration: 'Instantaneous',
                attack_type: 'ranged_spell',
                damage: '1d10',
                damage_type: 'force',
                summary: '120 ft · 1d10 Force',
                prepared: true,
            },
            {
                name: 'Mage Hand',
                level: 0,
                school: 'Conjuration',
                casting_time: '1 Action',
                range: '30 ft',
                components: 'V, S',
                duration: '1 minute',
                summary: 'Spectral floating hand within 30 ft',
                prepared: true,
            },
        ],
    },
    {
        name: 'Wizard',
        tagline: 'Scholarly arcanist mastering the deepest spellbooks of magic',
        description:
            'Wizards are defined by their exhaustive study of magic’s inner workings, casting spells of explosive fire, arcing lightning, and subtle deception.',
        hitDie: 6,
        primaryAbility: 'Intelligence',
        savingThrows: ['int', 'wis'],
        skillChoicesCount: 2,
        availableSkills: [
            'arcana',
            'history',
            'insight',
            'investigation',
            'medicine',
            'nature',
            'religion',
        ],
        recommendedSkills: ['arcana', 'investigation'],
        armorProficiencies: 'None',
        weaponProficiencies: 'Simple Weapons',
        weaponMasteryCount: 0,
        recommendedMasteries: [],
        spellcastingAbility: 'int',
        recommendedAbilities: {
            str: 8,
            dex: 14,
            con: 13,
            int: 15,
            wis: 12,
            cha: 10,
        },
        subclasses: ['Abjurer', 'Diviner', 'Evoker', 'Illusionist'],
        features: [
            {
                id: 'spellcasting_wizard',
                name: 'Spellcasting & Spellbook (Intelligence)',
                description:
                    'Maintain a spellbook of Wizard spells, cast rituals without preparing them, and use Intelligence as your spellcasting ability.',
            },
            {
                id: 'arcane_recovery',
                name: 'Arcane Recovery',
                description:
                    'When you finish a Short Rest, you can recover expended spell slots with a combined level equal to or less than half your Wizard level (rounded up, 1/Long Rest).',
            },
            {
                id: 'ritual_adept',
                name: 'Ritual Adept',
                description:
                    'You can cast any spell as a Ritual if that spell has the Ritual tag and the spell is in your spellbook.',
            },
        ],
        startingEquipment: {
            packageALabel:
                '2 Daggers, Quarterstaff (Arcane Focus), Robe, Spellbook, Scholar’s Pack, 5 GP',
            goldA: 5,
            goldB: 55,
            weapons: [
                {
                    name: 'Quarterstaff',
                    category: 'Simple Melee',
                    damage: '1d6',
                    damage_type: 'bludgeoning',
                    ability: 'str',
                    mastery: 'Topple',
                    properties: ['Versatile (1d8)'],
                    range: 'Melee',
                    weight: '4 lb',
                },
            ],
            armors: [],
            inventory: [
                {
                    name: 'Spellbook',
                    quantity: 1,
                    weight: '3 lb',
                    summary:
                        'Leather-bound tome containing your arcane formulas',
                },
            ],
        },
        recommendedSpells: [
            {
                name: 'Fire Bolt',
                level: 0,
                school: 'Evocation',
                casting_time: '1 Action',
                range: '120 ft',
                components: 'V, S',
                duration: 'Instantaneous',
                attack_type: 'ranged_spell',
                damage: '1d10',
                damage_type: 'fire',
                summary: '120 ft · 1d10 Fire',
                prepared: true,
            },
            {
                name: 'Mage Hand',
                level: 0,
                school: 'Conjuration',
                casting_time: '1 Action',
                range: '30 ft',
                components: 'V, S',
                duration: '1 minute',
                summary: 'Spectral floating hand within 30 ft',
                prepared: true,
            },
            {
                name: 'Magic Missile',
                level: 1,
                school: 'Evocation',
                casting_time: '1 Action',
                range: '120 ft',
                components: 'V, S',
                duration: 'Instantaneous',
                damage: '3x (1d4+1)',
                damage_type: 'force',
                summary: '120 ft · 3x (1d4+1) Force',
                prepared: true,
            },
            {
                name: 'Shield',
                level: 1,
                school: 'Abjuration',
                casting_time: '1 Reaction',
                range: 'Self',
                components: 'V, S',
                duration: '1 round',
                summary: '1 Reaction · +5 AC until next turn',
                prepared: true,
            },
            {
                name: 'Detect Magic',
                level: 1,
                school: 'Divination',
                casting_time: '1 Action or Ritual',
                range: 'Self (30-ft sphere)',
                components: 'V, S',
                duration: 'Concentration, up to 10 minutes',
                summary: 'Sense magic within 30 ft',
                prepared: true,
            },
        ],
    },
];

export const POINT_BUY_COSTS: Record<number, number> = {
    8: 0,
    9: 1,
    10: 2,
    11: 3,
    12: 4,
    13: 5,
    14: 7,
    15: 9,
};

export const STANDARD_ARRAY = [15, 14, 13, 12, 10, 8];
