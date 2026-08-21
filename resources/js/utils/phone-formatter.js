/**
 * /resources/js/utils/phone-formatter.js
 *
 * Auto-formats phone numbers (US/Canada style) as user types.
 * Attaches a single delegated listener covering every phone-like input on
 * the page (name containing "phone", e.g. phone, phone_2, landlord_phone,
 * business_phone, reference_phone), so it only needs to be called once
 * globally rather than per form/modal.
 */

let attached = false;

export function attachPhoneFormatter() {
    if (attached) return;
    attached = true;

    document.addEventListener('input', (e) => {
        const input = e.target;
        if (!(input instanceof HTMLInputElement) || !/phone/i.test(input.name)) return;

        let digits = input.value.replace(/\D/g, '');
        if (digits.length > 10) digits = digits.substring(0, 10);

        let formatted = digits;
        if (digits.length > 6) formatted = `(${digits.substring(0, 3)}) ${digits.substring(3, 6)}-${digits.substring(6)}`;
        else if (digits.length > 3) formatted = `(${digits.substring(0, 3)}) ${digits.substring(3)}`;
        else if (digits.length > 0) formatted = `(${digits}`;
        input.value = formatted;
    });
}
