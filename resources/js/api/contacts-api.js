// /resources/js/api/contacts-api.js

/**
 * Fetches the contact role list for the Official Role dropdown -- a real
 * endpoint instead of legacy cas-sports' pattern of scraping a hidden
 * <select> already rendered into the page.
 */
export async function fetchContactRoles() {
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    try {
        const res = await fetch(`${baseUrl}api/contact-roles`);
        const json = await res.json();
        return json.success ? json.data.map(r => ({ id: r.id, name: r.role_name })) : [];
    } catch (error) {
        console.error('Fetch Contact Roles Error:', error);
        return [];
    }
}
