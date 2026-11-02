export type CompendiumType =
    | 'weapon'
    | 'armor'
    | 'spell'
    | 'gear'
    | 'species'
    | 'class'
    | 'background'
    | 'feat'
    | 'feature'
    | 'trait'
    | 'language';

export interface CompendiumEntry {
    id: string;
    system_id?: string;
    type: CompendiumType;
    name: string;
    summary?: string | null;
    description?: string | null;
    data?: Record<string, any> | null;
    is_srd?: boolean;
}
