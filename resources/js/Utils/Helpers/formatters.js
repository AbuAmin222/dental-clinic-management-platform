/**
 * Disable anything in fields and enable just numbers
 * @param {Event|String} input
 * @returns {String}
 */
export const filterNumbers = (input) => {
    const value = typeof input === "object" ? input.target.value : input;
    return value.replace(/\D/g, "");
};

/**
 * Disable age
 * @param {Date} birthday
 * @returns {Date}
 */
export const calculateAge = (birthday) => {
    if (!birthday) return "";
    const ageDifMs = Date.now() - new Date(birthday).getTime();
    const ageDate = new Date(ageDifMs);
    return Math.abs(ageDate.getUTCFullYear() - 1970);
};

/**
 * Disable current date.
 * @returns {Date}
 */
export const today = () => new Date().toISOString().split("T")[0];

/**
 *
 * @param {*} value
 * @param {*} currency
 * @param {*} locale
 * @returns
 */
export function formatCurrency(value, currency = "USD", locale = "en-US") {
    if (value === null || value === undefined || isNaN(value)) return "$0.00";
    return new Intl.NumberFormat(locale, {
        style: "currency",
        currency: currency,
    }).format(value);
}

/**
 *
 * @param {*} fn
 * @param {*} delay
 * @returns
 */
export function debounce(fn, delay) {
    let timeout;
    return function (...args) {
        if (timeout) clearTimeout(timeout);
        timeout = setTimeout(() => {
            fn(...args);
        }, delay);
    };
}
