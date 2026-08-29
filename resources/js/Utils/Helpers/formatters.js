/**
 * @file formatters.js
 * @description Pure utility functions for data transformation and UI formatting.
 */

/**
 * Strips everything except digits from an input, accepting either a raw string or an
 * input/change event (reads event.target.value in that case).
 * @param {Event|string} input
 * @returns {string} Digits only.
 */
export const filterNumbers = (input) => {
    const value =
        typeof input === "object" && input !== null
            ? input.target.value
            : input;
    return String(value || "").replace(/\D/g, "");
};

/**
 * @param {string|Date} birthday
 * @returns {number|string} Age in whole years, or "" if birthday is missing/invalid.
 */
export const calculateAge = (birthday) => {
    if (!birthday) return "";
    const birthTimestamp = new Date(birthday).getTime();
    if (isNaN(birthTimestamp)) return "";

    const ageDifMs = Date.now() - birthTimestamp;
    const ageDate = new Date(ageDifMs);
    return Math.abs(ageDate.getUTCFullYear() - 1970);
};

/**
 * @returns {string} Today's date as YYYY-MM-DD, for use as a native <input type="date"> min/max.
 */
export const today = () => new Date().toISOString().split("T")[0];

/**
 * Formats a numeric value as localized currency. Defaults to ILS/en-US because every
 * money value in this system (invoices, pricing, salaries) is ILS — callers only need to
 * override this when displaying a value known to be in a different currency (e.g. a raw
 * PayPal amount before conversion).
 * @param {number|string} value
 * @param {string} [currency="ILS"] - ISO 4217 currency code.
 * @param {string} [locale="en-US"] - Locale for digit grouping/symbol placement.
 * @returns {string}
 */
export function formatCurrency(value, currency = "ILS", locale = "en-US") {
    const numericValue = Number(value);
    if (value === null || value === undefined || isNaN(numericValue)) {
        return new Intl.NumberFormat(locale, {
            style: "currency",
            currency,
        }).format(0);
    }
    return new Intl.NumberFormat(locale, {
        style: "currency",
        currency,
    }).format(numericValue);
}

/**
 * Delays calling fn until `delay` ms have passed since the last call — standard use is
 * search-as-you-type inputs that shouldn't fire a request on every keystroke.
 * @param {Function} fn
 * @param {number} delay - Milliseconds.
 * @returns {Function}
 */
export function debounce(fn, delay) {
    let timeout;
    return function (...args) {
        if (timeout) clearTimeout(timeout);
        timeout = setTimeout(() => {
            fn.apply(this, args);
        }, delay);
    };
}
