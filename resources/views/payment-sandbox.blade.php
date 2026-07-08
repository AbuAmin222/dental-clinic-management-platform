<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure External Payment Hub (Sandbox Simulation)</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-900 text-gray-100 flex items-center justify-center min-h-screen font-sans">

    <div class="max-w-md w-full bg-gray-800 p-8 rounded-2xl shadow-2xl border border-gray-700 text-center">
        <div class="text-5xl mb-4">🔒</div>

        <span
            class="text-xs font-bold uppercase bg-indigo-900/60 text-indigo-400 px-3 py-1 rounded-md tracking-wider border border-indigo-700">
            PMA Environment Secured
        </span>

        <h2 class="text-xl font-extrabold text-white mt-4 mb-1">{{ $gatewayName }}</h2>
        <p class="text-xs text-gray-400">Transaction Isolation ID: <span
                class="font-mono text-gray-300 font-bold">{{ $tx }}</span></p>

        <div class="my-6 bg-gray-900/60 p-4 rounded-xl border border-gray-700">
            <p class="text-xs text-gray-500 font-semibold uppercase">Amount Authorized</p>
            <p class="text-3xl font-black text-indigo-400 mt-1">{{ number_format($amount, 2) }} <span
                    class="text-sm font-bold text-gray-400">ILS</span></p>
        </div>

        <p class="text-xs text-gray-400 leading-relaxed mb-6">
            You have been redirected from the **Dental Clinic Application** to confirm this remittance. Choose an action
            to simulate the remote server feedback loop.
        </p>

        <div class="flex flex-col gap-3">
            <a href="{{ route('patient.payment.callback', ['gateway' => $gateway, 'tx' => $tx]) }}?status=success"
                class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition text-sm block shadow-md shadow-emerald-900/20">
                Simulate Successful Payment (Success Response)
            </a>

            <a href="{{ route('patient.payment.callback', ['gateway' => $gateway, 'tx' => $tx]) }}?status=failed"
                class="w-full py-3 bg-red-600/20 hover:bg-red-600/40 text-red-400 border border-red-900/60 font-medium rounded-xl transition text-sm block">
                Simulate Declined Card (Failure Response)
            </a>
        </div>

        <div class="mt-6 pt-4 border-t border-gray-700 flex justify-between items-center text-[10px] text-gray-500">
            <span>Encryption: AES-256</span>
            <span>Regulated Sandbox Hub v2.0</span>
        </div>
    </div>

</body>

</html>
