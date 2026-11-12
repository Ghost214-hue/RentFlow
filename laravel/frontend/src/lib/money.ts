/**
 * Money handling.
 *
 * RentFlow currency is KES. Amounts arrive from the server as STRINGS
 * (DECIMAL(12,2) cast to string) and must stay strings: doing arithmetic on
 * them in the browser as JS numbers is how totals drift.
 *
 * Formatting is the ONLY permitted operation here.
 */

const KES = new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

const PLAIN = new Intl.NumberFormat('en-KE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

/**
 * Parse a server money string for display only.
 *
 * Returns NaN for junk input so a bad value renders visibly rather than
 * silently becoming 0.00.
 */
export function toNumber(amount: string | null | undefined): number {
    if (amount === null || amount === undefined || amount === '') {
        return 0;
    }

    const parsed = Number(amount);

    return Number.isFinite(parsed) ? parsed : Number.NaN;
}

/** Format as "KES 6,500.00". Input is a string; no arithmetic is performed. */
export function formatMoney(amount: string | null | undefined): string {
    const value = toNumber(amount);

    if (!Number.isFinite(value)) {
        return 'KES 0.00';
    }

    return KES.format(value);
}

/** Format without the currency symbol, for table cells. */
export function formatAmount(amount: string | null | undefined): string {
    const value = toNumber(amount);

    return Number.isFinite(value) ? PLAIN.format(value) : '0.00';
}

/**
 * Normalise user input from a text field into a server-ready money string.
 * Strips anything that is not a digit or a single decimal point.
 *
 * NOTE: this validates SHAPE, not value. The server remains the authority.
 */
export function normaliseMoneyInput(raw: string): string {
    const cleaned = raw.replace(/[^0-9.]/g, '');

    const firstDot = cleaned.indexOf('.');

    if (firstDot === -1) {
        return cleaned;
    }

    // Keep only the first dot, and at most 2 decimals after it.
    const whole = cleaned.slice(0, firstDot);
    const decimals = cleaned.slice(firstDot + 1).replace(/\./g, '').slice(0, 2);

    return `${whole}.${decimals}`;
}

/** True when the string is a well-formed, non-negative 2dp amount. */
export function isValidMoney(raw: string): boolean {
    return /^\d+(\.\d{1,2})?$/.test(raw);
}