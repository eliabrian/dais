import type { Character } from './character';

export type System = {
    id: string;
    name: string;
    code: string;
};

export type CampaignGm = {
    id: string;
    username: string;
    avatar_url?: string | null;
};

export type Campaign = {
    id: string;
    name: string;
    description: string | null;
    gm_id: string;
    invite_code: string;
    status: string;
    system_id: string;
    system?: System | null;
    gm?: CampaignGm | null;
    players?: CampaignGm[];
    characters?: Character[];
    players_count?: number;
    characters_count?: number;
    created_at: string;
    updated_at: string;
};
