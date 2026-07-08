export const BASE_FIELDS = {
    role: "patient", // default

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
};

export const ROLE_FIELDS = {
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
};

export const FILE_FIELDS = {
    identity_photo: null,
    profile_photo: null,
};
