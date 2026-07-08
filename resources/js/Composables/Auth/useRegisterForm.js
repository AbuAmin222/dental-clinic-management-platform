import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
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
} from "@/Utils";
import { useFileHandle } from "./useFileHandle";
import { useNotifications } from "@/Composables";

export function useRegisterForm() {
    const { notify, toast, TimerSwal } = useNotifications();
    const step = ref(1);

    // Initializing form with all possible fields
    const form = useForm({
        ...BASE_FIELDS,
        ...ROLE_FIELDS.patient,
        ...ROLE_FIELDS.doctor,
        ...ROLE_FIELDS.receptionist,
        ...FILE_FIELDS,
    });

    // Email State Container
    const emailStatus = ref({
        loading: false,
        valid: null,
        message: "",
    });

    let debounceTimeout = null;

    const checkEmailRealTime = (emailValue) => {
        // 1. إعادة تعيين المؤقت مع كل نقرة جديدة لحماية السيرفر (Debounce Mechanism)
        clearTimeout(debounceTimeout);

        // إذا كان الحقل فارغاً، أعد الحالة للافتراضية
        if (!emailValue) {
            emailStatus.value = { loading: false, valid: null, message: "" };
            return;
        }

        // 2. فحص أولي سريع بالـ Regex في الـ Front-end قبل مراسلة السيرفر
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailValue)) {
            emailStatus.value = {
                loading: false,
                valid: false,
                message: "Invalid email format structural layout.",
            };
            return;
        }

        // تفعيل مؤشر الانتظار (Loader)
        emailStatus.value.loading = true;

        // 3. تأخير إرسال الطلب لمدة 500ms بعد توقف المستخدم عن الكتابة
        debounceTimeout = setTimeout(async () => {
            try {
                const response = await axios.post(
                    route("register.check-email"),
                    {
                        email: emailValue,
                    },
                );

                if (response.data.available) {
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

                    if (status === 422) {
                        // في حال فشل التحقق الخاص بـ Laravel نفسه
                        serverMessage =
                            error.response.data.errors?.email?.[0] ||
                            error.response.data.message;
                    } else if (status === 419) {
                        // في حال وجود مشكلة في توثيق الـ CSRF Token للجلسة
                        serverMessage =
                            "CSRF token mismatch. Please refresh the page.";
                    } else if (status === 500) {
                        // في حال حدوث خطأ داخلي في كود الـ Controller بالخلفية
                        serverMessage =
                            "Internal Server Error (500). Please check Laravel logs.";
                    }
                } else if (error.request) {
                    // في حال عدم وجود استجابة من السيرفر تماماً (السيرفر مطفأ مثلاً)
                    serverMessage =
                        "No response received from server. Check your connection.";
                }

                emailStatus.value = {
                    loading: false,
                    valid: false,
                    message: serverMessage,
                };
            }
        }, 500);
    };

    const fileLogic = useFileHandle(form);
    const { identityPreview, profilePreview } = fileLogic;

    // --- Computed Properties ---
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

    // --- Step Navigation Logic ---
    const isFinalStep = computed(() => {
        const roles = { patient: 5, doctor: 6, receptionist: 7 };
        return step.value === (roles[form.role] || 5);
    });

    let nextStep = () => {
        if (!validateByRole(form, step.value)) {
            TimerSwal(
                "OOPS Here is an Error!!!",
                "Please fill right your data",
                2500,
                "error",
            );
            return;
        } else if (step.value < 4) {
            step.value++;
        } else if (step.value === 4) {
            if (form.role === "patient") step.value = 5;
            else if (form.role === "doctor") step.value = 6;
            else if (form.role === "receptionist") step.value = 7;
            else {
                return;
            }
        }
    };

    const prevStep = () => {
        if (step.value > 4) step.value = 4;
        else if (step.value > 1) step.value--;
    };

    // --- Submission Logic ---
    const submit = (routeName, isStep = true) => {
        if (isStep === false) {
            if (!validatePatientInfo(form)) {
                notify(
                    "Validation Error",
                    "Please check the required fields",
                    "error",
                );
                return;
            }

            form.transform((data) => {
                const payload = {};
                // 1. Fill base fields
                Object.keys(BASE_FIELDS).forEach(
                    (key) => (payload[key] = data[key]),
                );
                // 2. Fill specific role fields
                const specificFields = ROLE_FIELDS[data.role] || {};
                Object.keys(specificFields).forEach(
                    (key) => (payload[key] = data[key]),
                );
                return payload;
            }).post(route(routeName), {
                onSuccess: () => {
                    toast("Registration completed successfully");
                    form.reset();
                },
                onError: () => {
                    notify(
                        "Error",
                        "Something went wrong, please try again",
                        "error",
                    );
                },
                // onFinish: () => form.reset(),
            });
        } else {
            if (!validateByRole(form, step.value)) {
                notify(
                    "Validation Error",
                    "Please check the required fields",
                    "error",
                );
                return;
            }
            form.transform((data) => {
                const payload = {};
                // 1. Fill base fields
                Object.keys(BASE_FIELDS).forEach(
                    (key) => (payload[key] = data[key]),
                );
                // 2. Fill specific role fields
                const specificFields = ROLE_FIELDS[data.role] || {};
                Object.keys(specificFields).forEach(
                    (key) => (payload[key] = data[key]),
                );

                // Fill Files
                Object.keys(FILE_FIELDS).forEach(
                    (key) => (payload[key] = data[key]),
                );

                return payload;
            }).post(route(routeName), {
                onSuccess: () => {
                    toast("Registration completed successfully");
                    form.reset();
                    step.value = 1;
                    identityPreview.value = null;
                    profilePreview.value = null;
                },
                onError: () => {
                    notify(
                        "Error",
                        "Something went wrong, please try again",
                        "error",
                    );
                },
                onFinish: () => form.reset("password", "password_confirmation"),
            });
        }
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
