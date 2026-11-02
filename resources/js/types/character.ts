import type { System } from './campaign';

export type AbilityKey = 'str' | 'dex' | 'con' | 'int' | 'wis' | 'cha';

export type SkillProficiency = 'none' | 'proficient' | 'expertise';

export type SheetWeaponItem = {
    id: string;
    compendium_id?: string;
    name: string;
    category?: string;
    damage?: string;
    damage_type?: string;
    ability?: string;
    mastery?: string;
    properties?: string[];
    range?: string;
    weight?: string;
};

export type SheetArmorItem = {
    id: string;
    compendium_id?: string;
    name: string;
    category: string;
    ac_base?: number;
    ac_bonus?: number;
    dex_bonus?: 'full' | 'max_2' | 'none';
    stealth_disadv?: boolean;
    str_req?: number | null;
    weight?: string;
    equipped?: boolean;
};

export type SheetSpellItem = {
    id: string;
    compendium_id?: string;
    name: string;
    level: number;
    school?: string;
    casting_time?: string;
    range?: string;
    components?: string;
    duration?: string;
    summary?: string;
    description?: string;
    attack_type?: string;
    damage?: string;
    damage_type?: string;
    save_ability?: string;
    prepared?: boolean;
};

export type SheetInventoryItem = {
    id: string;
    compendium_id?: string;
    name: string;
    quantity: number;
    weight?: string;
    summary?: string;
    description?: string;
};

export type Dnd2024SheetData = {
    species?: string;
    class?: string;
    subclass?: string;
    background?: string;
    origin_feat?: string;
    level?: number;
    xp?: number;
    armor_class?: number;
    speed?: number;
    size?: string;
    heroic_inspiration?: boolean;
    exhaustion?: number;
    hp?: {
        current?: number;
        max?: number;
        temp?: number;
    };
    hit_dice?: {
        total?: string;
        remaining?: number;
    };
    death_saves?: {
        successes?: number;
        failures?: number;
    };
    abilities?: Record<AbilityKey, number>;
    saving_throws?: AbilityKey[];
    skills?: Record<string, SkillProficiency>;
    weapon_masteries?: string[];
    weapons_list?: SheetWeaponItem[];
    armors_list?: SheetArmorItem[];
    spells_list?: SheetSpellItem[];
    inventory_list?: SheetInventoryItem[];
    weapons_attacks?: string;
    armor_equipped?: string;
    features_traits?: string;
    proficiencies?: string;
    equipment?: string;
    coins?: {
        cp?: number;
        sp?: number;
        ep?: number;
        gp?: number;
        pp?: number;
    };
    spellcasting_ability?: 'none' | 'int' | 'wis' | 'cha';
    spells?: string;
    notes?: string;
    [key: string]: unknown;
};

export type CharacterCampaignRef = {
    id: string;
    name: string;
    gm_id?: string;
    system_id?: string;
};

export type Character = {
    id: string;
    user_id: string;
    campaign_id: string | null;
    system_id: string;
    name: string;
    avatar_url: string | null;
    sheet_data: Dnd2024SheetData;
    system?: System | null;
    campaign?: CharacterCampaignRef | null;
    user?: {
        id: string;
        username: string;
    } | null;
    created_at: string;
    updated_at: string;
};
