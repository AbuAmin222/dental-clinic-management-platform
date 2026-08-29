/**
 * @file useFileHandle.js
 * @description Handles file selection (click and drag-and-drop), client-side size
 * validation, and preview generation for the three file fields used in registration:
 * identity photo, profile photo, and X-ray images.
 */

import { ref } from "vue";

const MAX_FILE_SIZE = 4 * 1024 * 1024; // 4MB

/**
 * @param {Object} form - The active Inertia form object; selected files are written
 *   directly onto its matching field (e.g. form.profile_photo).
 * @param {Function|null} [notify=null] - Optional (title, message, type) notifier. Falls
 *   back to a native alert() if not provided.
 * @returns {Object} Reactive preview/progress state plus handleFileUpload, handleDrop,
 *   and removeFile methods.
 */
export function useFileHandle(form, notify = null) {
    const isDragging = ref(false);

    // Purely a client-side "file is being read into memory" indicator — this does NOT
    // contact any server, and nothing here verifies identity or checks a database.
    // Earlier versions of this composable simulated fake steps like "Extracting Identity
    // Number..." and "Verifying with Database..." with a setInterval, which looked like
    // real verification was happening when none was. Renamed and reworded to be honest
    // about what's actually going on: a local file read.
    const uploadStatus = ref("idle"); // 'idle' | 'reading' | 'done'
    const uploadProgress = ref(0);

    const identityPreview = ref(null);
    const profilePreview = ref(null);
    const xrayPreview = ref(null);

    const previewRefForField = (field) => {
        if (field === "profile_photo") return profilePreview;
        if (field === "identity_photo") return identityPreview;
        if (field === "xray_image") return xrayPreview;
        return null;
    };

    const processFile = (file, field) => {
        if (!file) return;

        if (file.size > MAX_FILE_SIZE) {
            if (notify) {
                notify("File Too Large", "Maximum size is 4MB", "error");
            } else {
                alert("File is too large! Maximum size is 4MB.");
            }
            return;
        }

        form[field] = file;

        const preview = previewRefForField(field);
        if (!preview) return;

        uploadStatus.value = "reading";
        uploadProgress.value = 0;

        const reader = new FileReader();

        reader.onprogress = (event) => {
            if (event.lengthComputable) {
                uploadProgress.value = Math.round(
                    (event.loaded / event.total) * 100,
                );
            }
        };

        reader.onload = (event) => {
            preview.value = event.target.result;
            uploadProgress.value = 100;
            uploadStatus.value = "done";
        };

        reader.onerror = () => {
            uploadStatus.value = "idle";
            uploadProgress.value = 0;
            if (notify) {
                notify("Read Failed", "Could not read the selected file.", "error");
            }
        };

        reader.readAsDataURL(file);
    };

    /**
     * @param {Event} e - The native <input type="file"> change event.
     * @param {string} field - Target form field name (e.g. "profile_photo").
     */
    const handleFileUpload = (e, field) => {
        if (e.target.files && e.target.files[0]) {
            processFile(e.target.files[0], field);
        }
    };

    /**
     * @param {DragEvent} e - The native drop event.
     * @param {string} field - Target form field name.
     */
    const handleDrop = (e, field) => {
        isDragging.value = false;
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            processFile(e.dataTransfer.files[0], field);
        }
    };

    /**
     * @param {string} field - Target form field name to clear.
     */
    const removeFile = (field) => {
        form[field] = null;
        const preview = previewRefForField(field);
        if (preview) preview.value = null;
        uploadStatus.value = "idle";
        uploadProgress.value = 0;
    };

    return {
        isDragging,
        uploadStatus,
        uploadProgress,
        identityPreview,
        profilePreview,
        xrayPreview,
        handleFileUpload,
        handleDrop,
        removeFile,
    };
}
