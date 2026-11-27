// IGDB image CDN. Images are referenced by image_id, never downloaded.
// https://api-docs.igdb.com/#images
const IGDB_IMAGE_BASE = 'https://images.igdb.com/igdb/image/upload';

export type IgdbImageSize =
    | 'cover_small' // 90x128
    | 'cover_big' // 264x374
    | 'screenshot_med'
    | 'screenshot_big'
    | '720p'
    | '1080p';

export function igdbImageUrl(
    imageId: string,
    size: IgdbImageSize,
    retina = false,
): string {
    const suffix = retina ? '_2x' : '';

    return `${IGDB_IMAGE_BASE}/t_${size}${suffix}/${encodeURIComponent(imageId)}.jpg`;
}
