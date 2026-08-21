// /resources/js/components/postal-code-formatter.js

/**
 * Formats a postal/zip input to match the selected country, live as the
 * user types, and swaps the field's label between "Postal Code" (Canada)
 * and "Zip Code" (United States). Matched by country name rather than a
 * hardcoded ID so it isn't coupled to seed-data ordering.
 */
export function enablePostalCodeFormatting(formId) {
    const form = document.getElementById(formId);
    if (!form) return;

    const countrySelect = form.querySelector('select[name="countryId"]');
    const postalInput = form.querySelector('input[name="postalCode"]');
    const label = form.querySelector('[data-postal-code-label]');

    if (!countrySelect || !postalInput) return;

    const formatCanadian = (raw) => {
        const alnum = raw.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 6);
        return alnum.length > 3 ? `${alnum.slice(0, 3)} ${alnum.slice(3)}` : alnum;
    };

    const formatUS = (raw) => raw.replace(/\D/g, '').slice(0, 5);

    const getCountryName = () => (countrySelect.selectedOptions[0]?.textContent || '').trim();

    const applyMode = ({ clearValue = false } = {}) => {
        const countryName = getCountryName();

        if (clearValue) postalInput.value = '';

        if (countryName === 'Canada') {
            if (label) label.textContent = 'Postal Code';
            postalInput.placeholder = 'L4M 1A1';
            postalInput.maxLength = 7;
            postalInput.value = formatCanadian(postalInput.value);
        } else if (countryName === 'United States') {
            if (label) label.textContent = 'Zip Code';
            postalInput.placeholder = '20019';
            postalInput.maxLength = 5;
            postalInput.value = formatUS(postalInput.value);
        } else {
            if (label) label.textContent = 'Postal / Zip Code';
            postalInput.placeholder = '';
            postalInput.maxLength = 10;
        }
    };

    // A user actively switching the country invalidates whatever was typed
    // for the old country's format, so clear it rather than reformat it.
    countrySelect.addEventListener('change', () => applyMode({ clearValue: true }));

    postalInput.addEventListener('input', () => {
        const countryName = getCountryName();
        if (countryName === 'Canada') {
            postalInput.value = formatCanadian(postalInput.value);
        } else if (countryName === 'United States') {
            postalInput.value = formatUS(postalInput.value);
        }
    });

    // Initial state — handles both Add (no country yet) and Edit (pre-filled country)
    applyMode();
}
