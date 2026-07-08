import { useNotifications } from "@/Composables";

const { TimerSwal } = useNotifications();

/**
 * Handle input data
 * onlyNumber: enable only numbers
 * email: disable email formate
 * phone: start with (059|056) then (7) numbers
 * identity: enable only 9 numbers
 * license: disable first 2 capital charachter (A-Z) then (-) then (5 | 8) number
 */
export const patterns = {
    onlyNumber: /^\d+$/,
    email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    phone: /^(059|056)\d{7}$/,
    identity: /^\d{9}$/,
    license: /^[A-Z]{2}-\d{5,8}$/,
};

/**
 * True: if date in past.
 * False: if date in future.
 * @param {date} date
 * @returns {Boolean}
 */
export const isValidDate = (date) => date && new Date(date) < new Date();

/**
 * Ture: if date in past and not less than 30-12-1920
 * False: if date in future and less than 30-12-1920
 * @param {date} date
 * @returns {Boolean}
 */
export const isDateOverride = (date) => {
    return date && new Date(date) < new Date("1920-12-30");
};

/**
 * Make error message to dedicated field in didecated form
 * @param {FormData} form
 * @param {string} field
 * @param {Message} msg
 */
const setError = (form, field, msg) => {
    form.setError(field, msg);
};

/**
 * Check if role selected?
 * @param {FormData} form
 * @returns {True|ErrorMessage}
 */
export const roleChoice = (form) => {
    if (!form.role) setError(form, "role", "Please select an account type.");
    return !form.hasErrors;
};

/**
 *
 * @param {FormData} form
 * @returns {True|ErrorMessage}
 */
export const personalInfo = (form) => {
    if (!form.first_name)
        setError(form, "first_name", "First name is required.");

    if (!form.middle_name)
        setError(form, "middle_name", "Middle name is required.");

    if (!form.last_name) setError(form, "last_name", "Last name is required.");

    if (!patterns.identity.test(form.identity_number))
        setError(form, "identity_number", "Must be 9 digits.");

    if (!isValidDate(form.date_of_birth))
        setError(form, "date_of_birth", "Invalid date of birth.");

    if (!form.gender) setError(form, "gender", "Gender is required.");

    return !form.hasErrors;
};

export const contactInfo = (form) => {
    if (!form.username) setError(form, "username", "Username is required.");

    if (!patterns.email.test(form.email))
        setError(form, "email", "Invalid email.");

    if (!form.password) setError(form, "password", "Password too weak.");

    if (!form.password_confirmation)
        setError(form, "password_confirmation", "Passwords do not match.");

    if (!form.phone) {
        setError(form, "phone", "Phone is required.");
    }
    if (form.phone && !patterns.phone.test(form.phone))
        setError(form, "phone", "Phone syntax error.");

    if (!form.address) setError(form, "address", "Address is required.");

    return !form.hasErrors;
};

export const identityInfo = (form) => {
    if (!form.identity_photo)
        setError(form, "identity_photo", "Identity photo is required.");

    if (!form.profile_photo)
        setError(form, "profile_photo", "Personal|Profile photo is required.");
    return !form.hasErrors;
};

export const roleSpecificRules = {
    patient: (form) => {
        if (!form.blood_group) {
            setError(
                form,
                "blood_group",
                "Blood group is required for patients",
            );
        }
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

        if (!form.emergency_contact_phone)
            setError(
                form,
                "emergency_contact_phone",
                "Emergency contact phone is required.",
            );
        if (
            form.emergency_contact_phone &&
            !patterns.phone.test(form.emergency_contact_phone)
        )
            setError(
                form,
                "emergency_contact_phone",
                "Emergency contact phone syntax error.",
            );

        return !form.hasErrors;
    },
    doctor: (form) => {
        if (!form.license_number) {
            setError(
                form,
                "license_number",
                "Medical license number is required",
            );
        }
        if (!form.specialization_id || form.specialization_id.value === "")
            setError(form, "specialization_id", "Specialization is required.");

        if (!form.license_number)
            setError(
                form,
                "license_number",
                "Medical license number is required.",
            );
        if (!patterns.license.test(form.license_number)) {
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
};

export const validateByRole = (form, currentStep) => {
    form.clearErrors();

    switch (currentStep) {
        case 1:
            roleChoice(form);
            break;
        case 2:
            personalInfo(form);
            break;
        case 3:
            contactInfo(form);

            break;
        case 4:
            identityInfo(form);
            break;
        case 5:
        case 6:
        case 7:
            roleSpecificRules[form.role](form);
            break;
    }
    return !form.hasErrors;
};

export const validatePatientInfo = (form) => {
    form.clearErrors();
    personalInfo(form);
    contactInfo(form);
    roleSpecificRules[form.role](form);

    return !form.hasErrors;
};
