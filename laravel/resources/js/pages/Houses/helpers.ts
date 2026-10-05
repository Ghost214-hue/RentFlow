/**
 * Up to two initials for the avatar chip, as in the legacy table.
 *
 * Tolerates a missing name. A renter row can have a null name in the database
 * (the column is NOT NULL, but a legacy import may predate that), and this
 * runs during render -- a throw here blanks the whole page rather than showing
 * a blank avatar.
 */
export function initials(name: string | null | undefined): string {
    const trimmed = (name ?? '').trim();

    if (trimmed === '') {
        return '?';
    }

    return trimmed
        .split(/\s+/)
        .map((part) => part.charAt(0))
        .join('')
        .slice(0, 2)
        .toUpperCase();
}