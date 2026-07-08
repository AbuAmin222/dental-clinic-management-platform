import { ref } from "vue";

export function useFileHandle(form, notify = null) {
    // --- Reactive States ---
    const isDragging = ref(false);
    const aiStatus = ref("idle"); // idle, scanning, success, error
    const scanProgress = ref(0);
    const scanMessage = ref("");

    const identityPreview = ref(null);
    const profilePreview = ref(null);
    const xrayPreview = ref(null);

    // --- Core Logic ---
    const processFile = (file, field) => {
        if (!file) return;

        // Validation: 4MB Limit
        if (file.size > 4 * 1024 * 1024) {
            if (notify) {
                notify("File Too Large", "Maximum size is 4MB", "error");
            } else {
                alert("File is too large! Maximum size is 4MB.");
            }
            return;
        }

        form[field] = file;

        const reader = new FileReader();
        reader.onload = (e) => {
            if (field === "profile_photo") {
                profilePreview.value = e.target.result;
            }
            if (field === "identity_photo") {
                identityPreview.value = e.target.result;
                simulateAIValidation();
            }
            if (field === "xray_image") {
                xrayPreview.value = e.target.result;
                simulateAIValidation("xray");
            }
        };

        reader.readAsDataURL(file);
    };

    const handleFileUpload = (e, field) => {
        processFile(e.target.files[0], field);
    };

    const handleDrop = (e, field) => {
        isDragging.value = false;
        const file = e.dataTransfer.files[0];
        processFile(file, field);
    };

    const removeFile = (field) => {
        form[field] = null;
        if (field === "identity_photo") {
            identityPreview.value = null;
            aiStatus.value = "idle";
            scanProgress.value = 0;
        }
        if (field === "profile_photo") {
            profilePreview.value = null;
        }
        if (field === "xray_image") {
            xrayPreview.value = null;
            aiStatus.value = "idle";
            scanProgress.value = 0;
        }
    };

    // --- AI Simulation Logic ---
    const simulateAIValidation = (type = null) => {
        aiStatus.value = "scanning";
        scanProgress.value = 0;

        const steps =
            type === "xray"
                ? [
                      "Uploading image...",
                      "Checking file integrity...",
                      "Attaching to patient record...",
                      "Upload complete!",
                  ]
                : [
                      "Detecting ID Card...",
                      "Extracting Identity Number...",
                      "Verifying with Database...",
                      "Success!",
                  ];

        let i = 0;
        const interval = setInterval(() => {
            scanProgress.value += 25;
            scanMessage.value = steps[i] || "Finalizing...";
            i++;

            if (scanProgress.value >= 100) {
                clearInterval(interval);
                aiStatus.value = "success";
            }
        }, 350);
    };

    return {
        isDragging,
        aiStatus,
        scanProgress,
        scanMessage,
        identityPreview,
        profilePreview,
        xrayPreview,
        handleFileUpload,
        handleDrop,
        removeFile,
    };
}
