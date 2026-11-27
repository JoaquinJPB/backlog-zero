// Mirrors App\Http\Resources\Games\GameSummaryResource.
export interface GameSummary {
    slug: string;
    name: string;
    year: number | null;
    cover_image_id: string | null;
    platforms: string[];
}

// Mirrors App\Http\Resources\Games\GameResource.
export interface GamePlatform {
    name: string;
    abbreviation: string | null;
}

export interface GameDetail {
    slug: string;
    name: string;
    summary: string | null;
    cover_image_id: string | null;
    release_date: string | null;
    year: number | null;
    platforms: GamePlatform[];
    genres: string[];
    igdb_url: string;
}
