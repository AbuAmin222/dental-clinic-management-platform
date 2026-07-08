<script setup>
import { ref, computed, watch } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import ActionSection from "@/Components/ActionSection.vue";
import ConfirmsPassword from "@/Components/ConfirmsPassword.vue";
import DangerButton from "@/Components/DangerButton.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
  requiresConfirmation: Boolean,
});

const page = usePage();
const enabling = ref(false);
const confirming = ref(false);
const disabling = ref(false);
const qrCode = ref(null);
const setupKey = ref(null);
const recoveryCodes = ref([]);

const confirmationForm = useForm({ code: "" });

const twoFactorEnabled = computed(
  () => !enabling.value && page.props.auth.user?.two_factor_enabled
);

watch(twoFactorEnabled, () => {
  if (!twoFactorEnabled.value) {
    confirmationForm.reset();
    confirmationForm.clearErrors();
  }
});

const enableTwoFactorAuthentication = () => {
  enabling.value = true;
  router.post(
    route("two-factor.enable"),
    {},
    {
      preserveScroll: true,
      onSuccess: () => Promise.all([showQrCode(), showSetupKey(), showRecoveryCodes()]),
      onFinish: () => {
        enabling.value = false;
        confirming.value = props.requiresConfirmation;
      },
    }
  );
};

const showQrCode = () =>
  axios.get(route("two-factor.qr-code")).then((res) => {
    qrCode.value = res.data.svg;
  });
const showSetupKey = () =>
  axios.get(route("two-factor.secret-key")).then((res) => {
    setupKey.value = res.data.secretKey;
  });
const showRecoveryCodes = () =>
  axios.get(route("two-factor.recovery-codes")).then((res) => {
    recoveryCodes.value = res.data;
  });

const confirmTwoFactorAuthentication = () => {
  confirmationForm.post(route("two-factor.confirm"), {
    errorBag: "confirmTwoFactorAuthentication",
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      confirming.value = false;
      qrCode.value = null;
      setupKey.value = null;
    },
  });
};

const regenerateRecoveryCodes = () => {
  axios.post(route("two-factor.recovery-codes")).then(() => showRecoveryCodes());
};

const disableTwoFactorAuthentication = () => {
  disabling.value = true;
  router.delete(route("two-factor.disable"), {
    preserveScroll: true,
    onSuccess: () => {
      disabling.value = false;
      confirming.value = false;
    },
  });
};
</script>

<template>
  <ActionSection>
    <template #title>
      <span class="text-slate-900 font-bold text-lg tracking-tight"
        >Two Factor Authentication</span
      >
    </template>
    <template #description>
      <span class="text-slate-500 text-sm leading-relaxed">
        Add additional security layers to protect access to clinical resources.
      </span>
    </template>

    <template #content>
      <h3
        v-if="twoFactorEnabled && !confirming"
        class="text-base font-bold text-emerald-600"
      >
        🛡️ Two factor authentication is fully active.
      </h3>
      <h3
        v-else-if="twoFactorEnabled && confirming"
        class="text-base font-bold text-amber-500"
      >
        ⚠️ Finish configuration variables to finalize setup.
      </h3>
      <h3 v-else class="text-base font-bold text-slate-700">
        You have not enabled two factor authentication.
      </h3>

      <div class="mt-3 max-w-xl text-sm text-slate-500 leading-relaxed">
        <p>
          When two factor authentication is enabled, you will be prompted for a secure,
          random token during authentication. You may retrieve this token from your
          phone's Google Authenticator application.
        </p>
      </div>

      <div v-if="twoFactorEnabled">
        <div v-if="qrCode">
          <div
            class="mt-4 max-w-xl text-sm text-slate-600 bg-slate-50 p-4 rounded-xl border border-slate-100"
          >
            <p v-if="confirming" class="font-semibold text-indigo-600">
              To complete authentication, scan this QR code or provide the manual
              initialization key:
            </p>
            <p v-else class="font-semibold text-slate-700">
              Scan the following QR code using your application:
            </p>
          </div>

          <div
            class="mt-4 p-3 inline-block bg-white border border-slate-100 rounded-2xl shadow-sm"
            v-html="qrCode"
          />

          <div
            v-if="setupKey"
            class="mt-2 max-w-xl text-xs font-mono text-slate-600 bg-slate-50 p-3 rounded-lg border"
          >
            Key String: <span class="font-bold select-all" v-html="setupKey"></span>
          </div>

          <div v-if="confirming" class="mt-4 max-w-sm">
            <InputLabel
              for="code"
              value="Verification Code OTP"
              class="font-semibold text-slate-700"
            />
            <TextInput
              id="code"
              v-model="confirmationForm.code"
              type="text"
              name="code"
              class="block mt-1 w-full !rounded-xl"
              inputmode="numeric"
              autofocus
              autocomplete="one-time-code"
              @keyup.enter="confirmTwoFactorAuthentication"
            />
            <InputError :message="confirmationForm.errors.code" class="mt-2" />
          </div>
        </div>

        <div v-if="recoveryCodes.length > 0 && !confirming">
          <div
            class="mt-4 max-w-xl text-sm text-amber-800 bg-amber-50/60 p-4 rounded-xl border border-amber-100"
          >
            <p class="font-semibold">
              Store backup codes in a secure password manager environment.
            </p>
          </div>
          <div
            class="grid gap-1.5 max-w-xl mt-3 px-4 py-3 font-mono text-xs bg-slate-900 text-slate-200 rounded-xl shadow-inner"
          >
            <div
              v-for="code in recoveryCodes"
              :key="code"
              class="tracking-wider select-all"
            >
              {{ code }}
            </div>
          </div>
        </div>
      </div>

      <div class="mt-6 flex flex-wrap gap-3">
        <div v-if="!twoFactorEnabled">
          <ConfirmsPassword @confirmed="enableTwoFactorAuthentication">
            <PrimaryButton
              type="button"
              :class="{ 'opacity-25': enabling }"
              :disabled="enabling"
              class="!rounded-xl bg-indigo-600 hover:bg-indigo-700 text-sm font-semibold !px-5 !py-2.5 shadow-md"
            >
              Enable 2FA
            </PrimaryButton>
          </ConfirmsPassword>
        </div>

        <div v-else class="flex flex-wrap gap-3">
          <ConfirmsPassword @confirmed="confirmTwoFactorAuthentication">
            <PrimaryButton
              v-if="confirming"
              type="button"
              :class="{ 'opacity-25': enabling || confirmationForm.processing }"
              :disabled="enabling || confirmationForm.processing"
              class="!rounded-xl bg-indigo-600 !px-5 !py-2.5"
            >
              Confirm Code
            </PrimaryButton>
          </ConfirmsPassword>

          <ConfirmsPassword @confirmed="regenerateRecoveryCodes">
            <SecondaryButton
              v-if="recoveryCodes.length > 0 && !confirming"
              class="!rounded-xl !px-4 !py-2"
            >
              Regenerate Codes
            </SecondaryButton>
          </ConfirmsPassword>

          <ConfirmsPassword @confirmed="showRecoveryCodes">
            <SecondaryButton
              v-if="recoveryCodes.length === 0 && !confirming"
              class="!rounded-xl !px-4 !py-2"
            >
              Show Recovery Codes
            </SecondaryButton>
          </ConfirmsPassword>

          <ConfirmsPassword @confirmed="disableTwoFactorAuthentication">
            <SecondaryButton
              v-if="confirming"
              :class="{ 'opacity-25': disabling }"
              :disabled="disabling"
              class="!rounded-xl !px-4 !py-2"
            >
              Cancel
            </SecondaryButton>
          </ConfirmsPassword>

          <ConfirmsPassword @confirmed="disableTwoFactorAuthentication">
            <DangerButton
              v-if="!confirming"
              :class="{ 'opacity-25': disabling }"
              :disabled="disabling"
              class="!rounded-xl !px-5 !py-2.5 shadow-sm"
            >
              Disable 2FA
            </DangerButton>
          </ConfirmsPassword>
        </div>
      </div>
    </template>
  </ActionSection>
</template>
