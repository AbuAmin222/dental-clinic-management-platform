/**
 * @file validationRules.js
 * @description Pure validation runtime context decoupled from UI logic, enforcing strict domain invariants.
 */

/**
 * @type {Readonly<Object>}
 * @description Centralized evaluation regex patterns enforcing syntax specifications.
 */
export const patterns = Object.freeze({
    onlyNumber: /^\d+$/,
    email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    phone: /^(059|056)\d{7}$/,
    identity: /^\d{9}$/,
    license: /^[A-Z]{2}-\d{5,8}$/,
});

/**
 * @description Validates if a specific date parameter sits safely inside the historical past.
 * @param {string|Date} date - The evaluation date entity.
 * @returns {boolean} True if the date represents a valid past point in time.
 */
export const isValidDate = (date) => date && new Date(date) < new Date();

/**
 * @description Hard safety bounds checking to prevent non-realistic historical date entry.
 * @param {string|Date} date - The evaluation date entity.
 * @returns {boolean} True if the birthdate provided is unrealistically older than 1920-12-30.
 */
export const isDateOverride = (date) =>
    date && new Date(date) < new Date("1920-12-30");

/**
 * @description Internal decoupled validation helper attaching structured errors into current form context.
 * @param {Object} form - The current active Inertia form tracker object.
 * @param {string} field - The specific domain property identifier string.
 * @param {string} msg - The error notification description text string.
 * @returns {void}
 * @requires Inertia Form instance setError operational capability hook.
 */
const setError = (form, field, msg) => {
    form.setError(field, msg);
};

/**
 * @description Verifies user selected an identity role class boundary context.
 * @param {Object} form - The current active form tracker instance.
 * @returns {boolean} True if no structural errors are currently active inside the instance scope.
 */
export const roleChoice = (form) => {
    if (!form.role) setError(form, "role", "Please select an account type.");
    return !form.hasErrors;
};

/**
 * @description Enforces strict evaluation constraints on core user personal credentials.
 * @param {Object} form - The current active form tracker instance.
 * @returns {boolean} True if core personal attributes bypass structural criteria validation filters.
 * @requires patterns.identity evaluation schema.
 */
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

/**
 * @description Evaluates connection coordinates, access identifiers, and standard communication schemas.
 * @param {Object} form - The current active form tracker instance.
 * @returns {boolean} True if all communication and safety check constraints evaluate perfectly.
 * @requires patterns.email and patterns.phone tracking criteria.
 */
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

/**
 * @description Verifies complete inclusion of binary physical security assets and portrait identification media.
 * @param {Object} form - The current active form tracker instance.
 * @returns {boolean} True if required document files are structurally mapped inside memory.
 */
export const identityInfo = (form) => {
    if (!form.identity_photo)
        setError(form, "identity_photo", "Identity photo is required.");
    if (!form.profile_photo)
        setError(form, "profile_photo", "Personal profile photo is required.");
    return !form.hasErrors;
};

/**
 * @type {Readonly<Object>}
 * @description Isolated Strategy sub-registry context mapping deep specialized verification rules per core user role.
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

/**
 * @type {Readonly<Object>}
 * @description Open-Closed Step strategy mapping registry handling decoupled step evaluations without conditional fallback logic.
 */
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
 * @description Evaluates active user data inputs Contextually based on the system wizard navigation index.
 * @param {Object} form - The current active form tracker instance.
 * @param {number} currentStep - The numeric pointer location index of the wizard layout.
 * @returns {boolean} True if the targeted milestone validation constraints pass cleanly.
 * @requires STEP_VALIDATORS state registry mapping.
 */
export const validateByRole = (form, currentStep) => {
    form.clearErrors();
    const validator = STEP_VALIDATORS[currentStep];
    return validator ? validator(form) : !form.hasErrors;
};

/**
 * @description Performs an un-chunked, complete structural compilation data validation sweep useful for monolithic flat forms.
 * @param {Object} form - The current active form tracker instance.
 * @returns {boolean} True if the holistic entity identity attributes align perfectly to definitions.
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
