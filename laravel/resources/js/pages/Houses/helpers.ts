/** Up to two initials for the avatar chip, as in the legacy table. */
export function initials(name: string): string {
    return name
        .split(' ')
        .map((part) => part.charAt(0))
        .join('')
        .slice(0, 2)
        .toUpperCase();
}