/**
 * @file formatters.js
 * @description Pure utility functions for data transformation and UI formatting.
 */

/**
 * @description Filters input values dynamically to strip out non-numeric characters.
 * @param {Event|string} input - The raw input event object or a direct string value.
 * @returns {string} A sanitized string containing purely numeric digits.
 * @requires Standard JavaScript regular expression engine.
 */
export const filterNumbers = (input) => {
    const value =
        typeof input === "object" && input !== null
            ? input.target.value
            : input;
    return String(value || "").replace(/\D/g, "");
};

/**
 * @description Calculates the precise current age based on a given birthdate.
 * @param {string|Date} birthday - The date of birth string or Date object.
 * @returns {number|string} The calculated age as an integer, or an empty string if input is invalid.
 * @requires Native Date object and timestamp manipulation.
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
 * @description Retrieves the current calendar date formatted to ISO standards.
 * @returns {string} The current date string formatted as YYYY-MM-DD.
 * @requires Native standard Date string partitioning.
 */
export const today = () => new Date().toISOString().split("T")[0];

/**
 * @description Formats raw numeric values into localized currency presentation strings.
 * @param {number|string} value - The raw financial figure to format.
 * @param {string} [currency="USD"] - The standard three-letter ISO currency code.
 * @param {string} [locale="en-US"] - The regional locale identifier for layout structure.
 * @returns {string} The fully compiled localized currency string notation.
 * @requires Intl.NumberFormat localization API engine.
 */
export function formatCurrency(value, currency = "USD", locale = "en-US") {
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
 * @description High-performance debounce utility that delays function execution until a specific timeout has passed since the last call.
 * @param {Function} fn - The target core logic execution function to delay.
 * @param {number} delay - The execution cooldown period measured in milliseconds.
 * @returns {Function} A new closed state function tracking execution timeout intervals.
 * @requires Window timer handlers (setTimeout, clearTimeout).
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
