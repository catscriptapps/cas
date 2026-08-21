// /resources/js/api/user-types-api.js

export async function fetchUserTypes() {
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const url = `${baseUrl}api/user-types`;

    const res = await fetch(url);
    const json = await res.json();

    // Map the API response to the format our form expects
    return json.success ? json.data.map(t => ({
        id: t.user_type_id,
        name: t.user_type
    })) : [];
}