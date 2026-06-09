/**
 * @param {string} key
 * @returns {string}
 */
function t(key) {
    return key.split('.').reduce((obj, k) => obj?.[k], window.APP_LANG) ?? key;
}

/**
 * Convert date to format YYYY-MM-DD
 * @param {Date} date - The date to format
 * @returns {string} The date in YYYY-MM-DD format
 */
function formatDateToString(date) {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
}

/**
 * @param {string} str
 * @returns {string}
 */
function escapeHTML(str) {
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");
}

/**
 * @param {string} str
 * @returns {string}
 */
function bold(str) {    // Bold function if needed
    return `<strong>${escapeHTML(str)}</strong>`;
}

/**
 * @param {string} title
 * @returns {string}
 */
function translate_category(title) {
    const map = {
        "Saving":             "categories.saving",
        "Groceries":          "categories.groceries",
        "Leisure":            "categories.leisure",
        "Rent & Utilities":   "categories.rent_utilities",
        "Health":             "categories.health",
        "Clothing & Needed":  "categories.clothing",
        "Other":              "categories.other",
        "Withdrawal":         "categories.withdrawal",
        "Interest":           "categories.interest",
    };
    const key = map[title];
    if (!key) return title;
    return t(key) !== key ? t(key) : title;
}
