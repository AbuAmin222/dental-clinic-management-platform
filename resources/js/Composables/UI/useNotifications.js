/**
 * @file useNotifications.js
 * @description Abstracted notification service overlay layer isolating visual component system behaviors.
 */

import Swal from "sweetalert2";

/**
 * @description Encapsulates application global notification workflows.
 * @returns {Object} An API interface containing modal, flash toast, confirmation, and timed notify services.
 */
export function useNotifications() {
    /**
     * @description Spawns a structural confirmation modal to inform user of an isolated process resolution.
     * @param {string} title - The prominent header description text.
     * @param {string} text - Detailed explanation message content body.
     * @param {string} [icon="success"] - Design token layout icon status indicator (success, error, warning).
     * @returns {void}
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
     * @description Renders a swift non-blocking overhead toast notice which automatically unmounts.
     * @param {string} message - The notification context to display.
     * @param {string} [icon="success"] - Visual status accent identifier token.
     * @returns {void}
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
     * @description Interrupts normal flows to display a high-severity confirm dialog before execution of critical features.
     * @param {Function} callback - The executable business logic function deferred until confirmation.
     * @param {string} [title="Are you sure?"] - Prompt statement title header.
     * @param {string} [text="You won't be able to revert this!"] - Safety warning message body.
     * @returns {void}
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
     * @description Fires a transient auto-dismissing toast monitor overlay utilizing tracking progress visualization bars.
     * @param {string} title - Prompt title identifier header.
     * @param {string} text - Safety context notification body.
     * @param {number} timer - Runtime duration lifecycle tracker value in milliseconds.
     * @param {string} [icon="success"] - System status design display indicator token.
     * @returns {void}
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
