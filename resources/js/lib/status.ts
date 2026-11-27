import type { GameStatus } from '@/types/library';

// Each status owns one ink (see DESIGN.md). Literal class strings so
// Tailwind can see them. Cyan is reserved for "playing" and nothing else.
export interface StatusInk {
    text: string;
    bg: string;
    border: string;
    dot: string;
}

export const STATUS_INK: Record<GameStatus, StatusInk> = {
    playing: {
        text: 'text-cyan',
        bg: 'bg-cyan/12',
        border: 'border-cyan/45',
        dot: 'bg-cyan',
    },
    finished: {
        text: 'text-gold',
        bg: 'bg-gold/12',
        border: 'border-gold/45',
        dot: 'bg-gold',
    },
    abandoned: {
        text: 'text-red',
        bg: 'bg-red/12',
        border: 'border-red/45',
        dot: 'bg-red',
    },
    backlog: {
        text: 'text-lilac',
        bg: 'bg-lilac/12',
        border: 'border-lilac/45',
        dot: 'bg-lilac',
    },
};
