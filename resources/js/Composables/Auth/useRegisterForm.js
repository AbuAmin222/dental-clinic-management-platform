/**
 * @file useRegisterForm.js
 * @description Centralized state orchestration hub handling multi-step workflow logic, real-time remote validation pipelines, and secure registration commits.
 */

import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";
import {
    BASE_FIELDS,
    ROLE_FIELDS,
    FILE_FIELDS,
    validateByRole,
    patterns,
    isValidDate,
    isDateOverride,
    filterNumbers,
    calculateAge,
    today,
    validatePatientInfo,
    debounce,
} from "@/Utils";
import { useFileHandle } from "./useFileHandle";
import { useNotifications } from "@/Composables";

/**
 * @type {Readonly<Object>}
 * @description Absolute terminal navigation boundaries defined categorically per operational domain type.
 */
const ROLE_MAX_STEPS = Object.freeze({
    patient: 5,
    doctor: 6,
    receptionist: 7,
});

/**
 * @description Main composition wrapper engine driving complete multi-role account instantiation operations.
 * @returns {Object} Unified orchestration variables, tracking states, validation parameters, and server dispatch functions.
 */
export function useRegisterForm() {
    const { notify, toast, TimerSwal } = useNotifications();
    const step = ref(1);

    const form = useForm({
        ...BASE_FIELDS,
        ...ROLE_FIELDS.patient,
        ...ROLE_FIELDS.doctor,
        ...ROLE_FIELDS.receptionist,
        ...FILE_FIELDS,
    });

    const emailStatus = ref({
        loading: false,
        valid: null,
        message: "",
    });

    const fileLogic = useFileHandle(form, notify);
    const { identityPreview, profilePreview } = fileLogic;

    /**
     * @description Core network dispatcher carrying out server-side uniqueness evaluation sweeps.
     * @param {string} emailValue - Clean email address character payload string.
     * @returns {Promise<void>}
     * @requires Dynamic route engine mapping configuration endpoints.
     */
    const executeEmailServerCheck = async (emailValue) => {
        try {
            const response = await axios.post(route("register.check-email"), {
                email: emailValue,
            });
            if (response.data?.available) {
                emailStatus.value = {
                    loading: false,
                    valid: true,
                    message: "Email passed validation filter.",
                };
            } else {
                emailStatus.value = {
                    loading: false,
                    valid: false,
                    message: "This email is already occupied.",
                };
            }
        } catch (error) {
            let serverMessage = "Validation connection interrupted.";
            if (error.response) {
                const status = error.response.status;
                if (status === 422)
                    serverMessage =
                        error.response.data.errors?.email?.[0] ||
                        error.response.data.message;
                else if (status === 419)
                    serverMessage =
                        "CSRF token mismatch. Please refresh the page.";
                else if (status === 500)
                    serverMessage =
                        "Internal Server Error (500). Please check backend systems.";
            } else if (error.request) {
                serverMessage =
                    "No response received from server. Check link connection.";
            }
            emailStatus.value = {
                loading: false,
                valid: false,
                message: serverMessage,
            };
        }
    };

    /**
     * @description Internal micro-optimized debounce channel throttling outgoing server verification checks.
     * @requires debounce module integration framework.
     */
    const debouncedEmailCheck = debounce((value) => {
        executeEmailServerCheck(value);
    }, 500);

    /**
     * @description Intercepts real-time input entry values, filtering invalid layouts before dispatching server inquiries.
     * @param {string} emailValue - Evaluated email field character string.
     * @returns {void}
     */
    const checkEmailRealTime = (emailValue) => {
        if (!emailValue) {
            emailStatus.value = { loading: false, valid: null, message: "" };
            return;
        }

        if (!patterns.email.test(emailValue)) {
            emailStatus.value = {
                loading: false,
                valid: false,
                message: "Invalid email format structural layout.",
            };
            return;
        }

        emailStatus.value.loading = true;
        debouncedEmailCheck(emailValue);
    };

    const computedToday = computed(() =>
        typeof today === "function" ? today() : today,
    );
    const isPasswordSecure = computed(() => form.password.length >= 8);
    const isPasswordMatched = computed(
        () =>
            form.password === form.password_confirmation &&
            form.password !== "",
    );
    const isLicenseValid = computed(() =>
        patterns.license.test(form.license_number),
    );
    const isExperienceValid = computed(
        () => form.experience_years >= 0 && form.experience_years <= 60,
    );
    const isFinalStep = computed(
        () => step.value === (ROLE_MAX_STEPS[form.role] || 5),
    );

    /**
     * @description Increments navigation steps if current active stage attributes pass strict validation constraints.
     * @returns {void}
     */
    const nextStep = () => {
        if (!validateByRole(form, step.value)) {
            TimerSwal(
                "OOPS Here is an Error!!!",
                "Please fill right your data",
                2500,
                "error",
            );
            return;
        }

        if (step.value < 4) {
            step.value++;
        } else if (step.value === 4) {
            step.value = ROLE_MAX_STEPS[form.role] || 5;
        }
    };

    /**
     * @description Decrements navigation step boundaries back down safely to base layout checkpoints.
     * @returns {void}
     */
    const prevStep = () => {
        if (step.value > 4) step.value = 4;
        else if (step.value > 1) step.value--;
    };

    /**
     * @description Sanitizes and filters the multi-step form fields to build a clean payload tailored strictly to the user's role context.
     * @param {Object} data - Full un-filtered active form data state object.
     * @param {boolean} [includeFiles=true] - Optional flag determining structural compilation of file buffers.
     * @returns {Object} A purified and structured API data collection object.
     */
    const buildPayload = (data, includeFiles = true) => {
        const payload = {};
        Object.keys(BASE_FIELDS).forEach((key) => {
            payload[key] = data[key];
        });

        const specificFields = ROLE_FIELDS[data.role] || {};
        Object.keys(specificFields).forEach((key) => {
            payload[key] = data[key];
        });

        if (includeFiles) {
            Object.keys(FILE_FIELDS).forEach((key) => {
                payload[key] = data[key];
            });
        }
        return payload;
    };

    /**
     * @description Submits the clean operational payload to backend infrastructure controllers via Inertia core protocol drivers.
     * @param {string} routeName - Dedicated named laravel resource routing key pointer.
     * @param {boolean} [isStep=true] - Pointer identifying application execution type context (wizard vs single form).
     * @returns {void}
     */
    const submit = (routeName, isStep = true) => {
        const isValid = isStep
            ? validateByRole(form, step.value)
            : validatePatientInfo(form);

        if (!isValid) {
            notify(
                "Validation Error",
                "Please check the required fields",
                "error",
            );
            return;
        }

        form.transform((data) => buildPayload(data, isStep)).post(
            route(routeName),
            {
                onSuccess: () => {
                    toast("Registration completed successfully");
                    form.reset();
                    if (isStep) {
                        step.value = 1;
                        identityPreview.value = null;
                        profilePreview.value = null;
                    }
                },
                onError: () => {
                    notify(
                        "Error",
                        "Something went wrong, please try again",
                        "error",
                    );
                },
                onFinish: () => {
                    if (form.password)
                        form.reset("password", "password_confirmation");
                },
            },
        );
    };

    return {
        form,
        step,
        patterns,
        filterNumbers,
        isPasswordSecure,
        isPasswordMatched,
        isLicenseValid,
        isExperienceValid,
        isValidDate,
        isDateOverride,
        calculateAge,
        today: computedToday,
        emailStatus,
        checkEmailRealTime,
        ...fileLogic,
        isFinalStep,
        nextStep,
        prevStep,
        submit,
    };
}
