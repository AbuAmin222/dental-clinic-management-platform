import Swal from "sweetalert2";

export function useNotifications() {
    const notify = (title, text, icon = "success") => {
        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            confirmButtonColor: "#4f46e5",
        });
    };

    const toast = (message, icon = "success") => {
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
        Toast.fire({
            icon: icon,
            title: message,
        });
    };

    const confirmAction = (
        callback,
        title = "Are you sure?",
        text = "You won't be able to revert this!",
    ) => {
        Swal.fire({
            title: title,
            text: text,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, proceed!",
        }).then((result) => {
            if (result.isConfirmed) {
                callback();
            }
        });
    };

    const TimerSwal = (title, text, timer, icon = "success") => {
        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            confirmButtonColor: "#dc2626",
            toast: true,
            position: "top-end",
            timer: timer,
            showConfirmButton: false,
            timerProgressBar: true,
        });
    };

    return { notify, toast, confirmAction, TimerSwal };
}
