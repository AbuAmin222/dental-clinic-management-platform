/**
 * @file useNotifications.js
 * @description Thin wrapper around SweetAlert2 for the app's four notification patterns:
 * a blocking alert, an auto-dismissing toast, a destructive-action confirmation, and a
 * timed toast variant.
 */

import Swal from "sweetalert2";

export function useNotifications() {
    /**
     * Blocking alert dialog the user must dismiss.
     * @param {string} title
     * @param {string} text
     * @param {string} [icon="success"] - "success" | "error" | "warning" | "info" | "question"
     */
    const notify = (title, text, icon = "success") => {
        Swal.fire({
            title,
            text,
            icon,
            confirmButtonColor: "#4f46e5",
        });
    };

    /**
     * Non-blocking toast, top-right, auto-dismisses after 3s.
     * @param {string} message
     * @param {string} [icon="success"]
     */
    const toast = (message, icon = "success") => {
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
        Toast.fire({ icon, title: message });
    };

    /**
     * Confirmation dialog for destructive actions — callback only runs if the user
     * confirms.
     * @param {Function} callback
     * @param {string} [title="Are you sure?"]
     * @param {string} [text="You won't be able to revert this!"]
     */
    const confirmAction = (
        callback,
        title = "Are you sure?",
        text = "You won't be able to revert this!",
    ) => {
        Swal.fire({
            title,
            text,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, proceed!",
        }).then((result) => {
            if (result.isConfirmed && typeof callback === "function") {
                callback();
            }
        });
    };

    /**
     * Toast variant with a caller-controlled duration, for cases where the default 3s
     * in toast() isn't appropriate (e.g. a longer warning that needs more read time).
     * @param {string} title
     * @param {string} text
     * @param {number} timer - Milliseconds.
     * @param {string} [icon="success"]
     */
    const TimerSwal = (title, text, timer, icon = "success") => {
        Swal.fire({
            title,
            text,
            icon,
            confirmButtonColor: "#dc2626",
            toast: true,
            position: "top-end",
            timer,
            showConfirmButton: false,
            timerProgressBar: true,
        });
    };

    return { notify, toast, confirmAction, TimerSwal };
}
