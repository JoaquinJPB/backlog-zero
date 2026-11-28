import type { GameSummary } from '@/types/games';

export const GAME_STATUSES = [
    'playing',
    'finished',
    'abandoned',
    'backlog',
] as const;

export type GameStatus = (typeof GAME_STATUSES)[number];

// Mirrors App\Http\Resources\Library\LibraryEntryResource.
export interface LibraryEntry {
    id: number;
    status: GameStatus;
    /** 1-10: half stars. */
    rating: number | null;
    /** 100%: every achievement. */
    completed: boolean;
    platform_id: number | null;
    platform?: string | null;
    started_on: string | null;
    finished_on: string | null;
    game?: GameSummary;
}

export interface PlatformOption {
    id: number;
    name: string;
}

export function allowsFinishDate(status: GameStatus): boolean {
    return status === 'finished' || status === 'abandoned';
}
