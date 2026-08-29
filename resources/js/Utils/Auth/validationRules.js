/**
 * @file validationRules.js
 * @description Client-side validation for the multi-step registration wizard, kept
 * separate from the Vue components so it can be unit-tested without mounting anything.
 * This mirrors the server's validation for immediate feedback — the server-side Form
 * Requests remain the actual source of truth and re-validate everything regardless.
 */

/** @type {Readonly<Object<string, RegExp>>} */
export const patterns = Object.freeze({
    onlyNumber: /^\d+$/,
    email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    phone: /^(059|056)\d{7}$/,
    identity: /^\d{9}$/,
    license: /^[A-Z]{2}-\d{5,8}$/,
});

/**
 * @param {string|Date} date
 * @returns {boolean} True if date is a real date strictly before today.
 */
export const isValidDate = (date) => date && new Date(date) < new Date();

/**
 * Rejects unrealistic birthdates (anything before 1920-12-30) — a basic sanity bound, not
 * a real age-eligibility rule.
 * @param {string|Date} date
 * @returns {boolean}
 */
export const isDateOverride = (date) =>
    date && new Date(date) < new Date("1920-12-30");

const setError = (form, field, msg) => {
    form.setError(field, msg);
};

/** Step 1: account type must be chosen. @param {Object} form @returns {boolean} */
export const roleChoice = (form) => {
    if (!form.role) setError(form, "role", "Please select an account type.");
    return !form.hasErrors;
};

/** Step 2: name, national ID, date of birth, gender. @param {Object} form @returns {boolean} */
export const personalInfo = (form) => {
    if (!form.first_name)
        setError(form, "first_name", "First name is required.");
    if (!form.middle_name)
        setError(form, "middle_name", "Middle name is required.");
    if (!form.last_name) setError(form, "last_name", "Last name is required.");

    if (!patterns.identity.test(form.identity_number)) {
        setError(form, "identity_number", "Must be 9 digits.");
    }
    if (!isValidDate(form.date_of_birth)) {
        setError(form, "date_of_birth", "Invalid date of birth.");
    }
    if (!form.gender) setError(form, "gender", "Gender is required.");

    return !form.hasErrors;
};

/** Step 3: username, email, password, phone, address. @param {Object} form @returns {boolean} */
export const contactInfo = (form) => {
    if (!form.username) setError(form, "username", "Username is required.");
    if (!patterns.email.test(form.email))
        setError(form, "email", "Invalid email.");
    if (!form.password) setError(form, "password", "Password too weak.");
    if (form.password !== form.password_confirmation) {
        setError(form, "password_confirmation", "Passwords do not match.");
    }
    if (!form.phone) {
        setError(form, "phone", "Phone is required.");
    } else if (!patterns.phone.test(form.phone)) {
        setError(form, "phone", "Phone syntax error.");
    }
    if (!form.address) setError(form, "address", "Address is required.");

    return !form.hasErrors;
};

/** Step 4: identity + profile photo must both be attached. @param {Object} form @returns {boolean} */
export const identityInfo = (form) => {
    if (!form.identity_photo)
        setError(form, "identity_photo", "Identity photo is required.");
    if (!form.profile_photo)
        setError(form, "profile_photo", "Personal profile photo is required.");
    return !form.hasErrors;
};

/**
 * Final-step rules, one per role — only the block matching form.role runs.
 * @type {Readonly<Object<string, (form: Object) => boolean>>}
 */
export const roleSpecificRules = Object.freeze({
    patient: (form) => {
        if (!form.blood_group)
            setError(
                form,
                "blood_group",
                "Blood group is required for patients.",
            );
        if (!form.emergency_contact_name)
            setError(
                form,
                "emergency_contact_name",
                "Emergency contact name is required.",
            );

        if (!form.emergency_contact_phone) {
            setError(
                form,
                "emergency_contact_phone",
                "Emergency contact phone is required.",
            );
        } else if (!patterns.phone.test(form.emergency_contact_phone)) {
            setError(
                form,
                "emergency_contact_phone",
                "Emergency contact phone syntax error.",
            );
        }
        return !form.hasErrors;
    },
    doctor: (form) => {
        if (!form.specialization_id || form.specialization_id.value === "") {
            setError(form, "specialization_id", "Specialization is required.");
        }
        if (!form.license_number) {
            setError(
                form,
                "license_number",
                "Medical license number is required.",
            );
        } else if (!patterns.license.test(form.license_number)) {
            setError(
                form,
                "license_number",
                "Medical License Number syntax error.",
            );
        }
        if (
            form.experience_years === null ||
            form.experience_years === "" ||
            form.experience_years < 0 ||
            form.experience_years > 60
        ) {
            setError(
                form,
                "experience_years",
                "Experience years must be between 0 and 60.",
            );
        }
        return !form.hasErrors;
    },
    receptionist: (form) => {
        if (!form.department_id || form.department_id.value === "")
            setError(form, "department", "Department is required.");
        if (!form.employee_number)
            setError(form, "employee_number", "Employee ID is required.");
        if (!form.hiring_date)
            setError(form, "hiring_date", "Hiring date is required.");
        return !form.hasErrors;
    },
});

// Maps wizard step number -> validator for that step. Steps 5-7 all resolve to the same
// role-specific rules because different roles reach the "final step" at different step
// numbers (see ROLE_MAX_STEPS in useRegisterForm.js) — whichever number the wizard lands
// on for the final step, this falls through to the correct per-role rules.
const STEP_VALIDATORS = Object.freeze({
    1: roleChoice,
    2: personalInfo,
    3: contactInfo,
    4: identityInfo,
    5: (form) => roleSpecificRules[form.role]?.(form) ?? true,
    6: (form) => roleSpecificRules[form.role]?.(form) ?? true,
    7: (form) => roleSpecificRules[form.role]?.(form) ?? true,
});

/**
 * Validates only the current wizard step (clears prior errors first).
 * @param {Object} form
 * @param {number} currentStep
 * @returns {boolean}
 */
export const validateByRole = (form, currentStep) => {
    form.clearErrors();
    const validator = STEP_VALIDATORS[currentStep];
    return validator ? validator(form) : !form.hasErrors;
};

/**
 * Validates personal info + contact info + role-specific fields in one pass. Used by
 * flat (non-wizard) forms such as receptionist-side patient registration, where all
 * fields are on a single screen instead of split across steps.
 * @param {Object} form
 * @returns {boolean}
 */
export const validatePatientInfo = (form) => {
    form.clearErrors();
    personalInfo(form);
    contactInfo(form);
    if (roleSpecificRules[form.role]) {
        roleSpecificRules[form.role](form);
    }
    return !form.hasErrors;
};
