const Ziggy = {
    url: "http:\/\/127.0.0.1:8000",
    port: 8000,
    defaults: {},
    routes: {
        login: { uri: "login", methods: ["GET", "HEAD"] },
        "login.store": { uri: "login", methods: ["POST"] },
        logout: { uri: "logout", methods: ["POST"] },
        "password.request": {
            uri: "forgot-password",
            methods: ["GET", "HEAD"],
        },
        "password.reset": {
            uri: "reset-password\/{token}",
            methods: ["GET", "HEAD"],
            parameters: ["token"],
        },
        "password.email": { uri: "forgot-password", methods: ["POST"] },
        "password.update": { uri: "reset-password", methods: ["POST"] },
        register: { uri: "register", methods: ["GET", "HEAD"] },
        "register.store": { uri: "register", methods: ["POST"] },
        "user-profile-information.update": {
            uri: "user\/profile-information",
            methods: ["PUT"],
        },
        "user-password.update": { uri: "user\/password", methods: ["PUT"] },
        "password.confirm": {
            uri: "user\/confirm-password",
            methods: ["GET", "HEAD"],
        },
        "password.confirmation": {
            uri: "user\/confirmed-password-status",
            methods: ["GET", "HEAD"],
        },
        "password.confirm.store": {
            uri: "user\/confirm-password",
            methods: ["POST"],
        },
        "two-factor.login": {
            uri: "two-factor-challenge",
            methods: ["GET", "HEAD"],
        },
        "two-factor.login.store": {
            uri: "two-factor-challenge",
            methods: ["POST"],
        },
        "two-factor.enable": {
            uri: "user\/two-factor-authentication",
            methods: ["POST"],
        },
        "two-factor.confirm": {
            uri: "user\/confirmed-two-factor-authentication",
            methods: ["POST"],
        },
        "two-factor.disable": {
            uri: "user\/two-factor-authentication",
            methods: ["DELETE"],
        },
        "two-factor.qr-code": {
            uri: "user\/two-factor-qr-code",
            methods: ["GET", "HEAD"],
        },
        "two-factor.secret-key": {
            uri: "user\/two-factor-secret-key",
            methods: ["GET", "HEAD"],
        },
        "two-factor.recovery-codes": {
            uri: "user\/two-factor-recovery-codes",
            methods: ["GET", "HEAD"],
        },
        "two-factor.regenerate-recovery-codes": {
            uri: "user\/two-factor-recovery-codes",
            methods: ["POST"],
        },
        "terms.show": { uri: "terms-of-service", methods: ["GET", "HEAD"] },
        "policy.show": { uri: "privacy-policy", methods: ["GET", "HEAD"] },
        "profile.show": { uri: "user\/profile", methods: ["GET", "HEAD"] },
        "other-browser-sessions.destroy": {
            uri: "user\/other-browser-sessions",
            methods: ["DELETE"],
        },
        "current-user-photo.destroy": {
            uri: "user\/profile-photo",
            methods: ["DELETE"],
        },
        "current-user.destroy": { uri: "user", methods: ["DELETE"] },
        "api-tokens.index": {
            uri: "user\/api-tokens",
            methods: ["GET", "HEAD"],
        },
        "api-tokens.store": { uri: "user\/api-tokens", methods: ["POST"] },
        "api-tokens.update": {
            uri: "user\/api-tokens\/{token}",
            methods: ["PUT"],
            parameters: ["token"],
        },
        "api-tokens.destroy": {
            uri: "user\/api-tokens\/{token}",
            methods: ["DELETE"],
            parameters: ["token"],
        },
        "sanctum.csrf-cookie": {
            uri: "sanctum\/csrf-cookie",
            methods: ["GET", "HEAD"],
        },
        "livewire.upload-file": {
            uri: "livewire\/upload-file",
            methods: ["POST"],
        },
        "livewire.preview-file": {
            uri: "livewire\/preview-file\/{filename}",
            methods: ["GET", "HEAD"],
            parameters: ["filename"],
        },
        dashboard: { uri: "dashboard", methods: ["GET", "HEAD"] },
        "dental-records.xray": {
            uri: "dental-records\/{dentalRecord}\/xray",
            methods: ["GET", "HEAD"],
            parameters: ["dentalRecord"],
            bindings: { dentalRecord: "id" },
        },
        "user-profile-role.update": {
            uri: "user\/profile-role",
            methods: ["PUT"],
        },
        "doctor.dashboard": {
            uri: "doctor\/dashboard",
            methods: ["GET", "HEAD"],
        },
        "doctor.pricings.index": {
            uri: "doctor\/pricings",
            methods: ["GET", "HEAD"],
        },
        "doctor.pricings.store": { uri: "doctor\/pricings", methods: ["POST"] },
        "doctor.pricings.update": {
            uri: "doctor\/pricings\/{pricing}",
            methods: ["PUT"],
            parameters: ["pricing"],
            bindings: { pricing: "id" },
        },
        "doctor.pricings.destroy": {
            uri: "doctor\/pricings\/{pricing}",
            methods: ["DELETE"],
            parameters: ["pricing"],
            bindings: { pricing: "id" },
        },
        "doctor.dentalRecords.create": {
            uri: "doctor\/appointments\/{appointment}\/dental-record\/create",
            methods: ["GET", "HEAD"],
            parameters: ["appointment"],
            bindings: { appointment: "id" },
        },
        "doctor.dentalRecords.store": {
            uri: "doctor\/appointments\/{appointment}\/dental-record",
            methods: ["POST"],
            parameters: ["appointment"],
            bindings: { appointment: "id" },
        },
        "doctor.patients.history": {
            uri: "doctor\/patients\/{patient}\/history",
            methods: ["GET", "HEAD"],
            parameters: ["patient"],
            bindings: { patient: "id" },
        },
        "patient.dashboard": {
            uri: "patient\/dashboard",
            methods: ["GET", "HEAD"],
        },
        "patient.appointment.index": {
            uri: "patient\/appointment\/create",
            methods: ["GET", "HEAD"],
        },
        "patient.appointment.store": {
            uri: "patient\/appointment\/store",
            methods: ["POST"],
        },
        "patient.invoices.checkout": {
            uri: "patient\/invoices\/{invoice}\/checkout",
            methods: ["GET", "HEAD"],
            parameters: ["invoice"],
            bindings: { invoice: "id" },
        },
        "patient.invoices.pay": {
            uri: "patient\/invoices\/{invoice}\/pay",
            methods: ["POST"],
            parameters: ["invoice"],
            bindings: { invoice: "id" },
        },
        "patient.payment.callback": {
            uri: "patient\/payment\/callback\/{gateway}\/{tx}",
            methods: ["GET", "HEAD"],
            parameters: ["gateway", "tx"],
        },
        "patient.payment.sandbox.gateway": {
            uri: "patient\/payment\/sandbox-gateway",
            methods: ["GET", "HEAD"],
        },
        "receptionist.dashboard": {
            uri: "receptionist\/dashboard",
            methods: ["GET", "HEAD"],
        },
        "receptionist.patients.index": {
            uri: "receptionist\/patients",
            methods: ["GET", "HEAD"],
        },
        "receptionist.patients.create": {
            uri: "receptionist\/patients\/create",
            methods: ["GET", "HEAD"],
        },
        "receptionist.patients.store": {
            uri: "receptionist\/patients\/store",
            methods: ["POST"],
        },
        "receptionist.patients.show": {
            uri: "receptionist\/patients\/{patient}",
            methods: ["GET", "HEAD"],
            parameters: ["patient"],
            bindings: { patient: "id" },
        },
        "receptionist.appointments.index": {
            uri: "receptionist\/appointments",
            methods: ["GET", "HEAD"],
        },
        "receptionist.appointments.create": {
            uri: "receptionist\/appointments\/create",
            methods: ["GET", "HEAD"],
        },
        "receptionist.appointments.store": {
            uri: "receptionist\/appointments",
            methods: ["POST"],
        },
        "receptionist.appointments.updateStatus": {
            uri: "receptionist\/appointments\/{appointment}\/status",
            methods: ["PATCH"],
            parameters: ["appointment"],
            bindings: { appointment: "id" },
        },
        "receptionist.invoices.create": {
            uri: "receptionist\/appointments\/{appointment}\/invoice\/create",
            methods: ["GET", "HEAD"],
            parameters: ["appointment"],
            bindings: { appointment: "id" },
        },
        "receptionist.invoices.store": {
            uri: "receptionist\/appointments\/{appointment}\/invoice",
            methods: ["POST"],
            parameters: ["appointment"],
            bindings: { appointment: "id" },
        },
        "receptionist.invoices.destroy": {
            uri: "receptionist\/receptionist\/appointments\/{appointment}\/invoice",
            methods: ["DELETE"],
            parameters: ["appointment"],
            bindings: { appointment: "id" },
        },
        "storage.local": {
            uri: "storage\/{path}",
            methods: ["GET", "HEAD"],
            wheres: { path: ".*" },
            parameters: ["path"],
        },
        "livewire.update": { uri: "livewire\/update", methods: ["POST"] },
    },
};
if (typeof window !== "undefined" && typeof window.Ziggy !== "undefined") {
    Object.assign(Ziggy.routes, window.Ziggy.routes);
}
export { Ziggy };
