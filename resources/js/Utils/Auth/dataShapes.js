/**
 * @file dataShapes.js
 * @description Immutable architectural schemas defining unified application registry form data configurations.
 */

/**
 * @type {Readonly<Object>}
 * @description Core global identity parameters common across all system users.
 */
export const BASE_FIELDS = Object.freeze({
    role: "patient",
    first_name: "",
    middle_name: "",
    last_name: "",
    username: "",
    email: "",
    password: "",
    password_confirmation: "",
    identity_number: "",
    phone: "",
    date_of_birth: "",
    gender: "",
    address: "",
    terms: false,
});

/**
 * @type {Readonly<Object>}
 * @description Specialized business domain data payloads split distinctly per capability role context.
 */
export const ROLE_FIELDS = Object.freeze({
    patient: {
        blood_group: "",
        allergies: "",
        chronic_diseases: "",
        emergency_contact_name: "",
        emergency_contact_phone: "",
    },
    doctor: {
        specialization_id: "",
        license_number: "",
        experience_years: 0,
        bio: "",
    },
    receptionist: {
        department_id: "",
        employee_number: "",
        hiring_date: "",
    },
});

/**
 * @type {Readonly<Object>}
 * @description File attachment structural payloads for binary identity documentation storage mappings.
 */
export const FILE_FIELDS = Object.freeze({
    identity_photo: null,
    profile_photo: null,
});
