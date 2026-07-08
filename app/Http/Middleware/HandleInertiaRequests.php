<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            // 🌟 مشاركة بيانات المصادقة والدور عالمياً في الواجهة الأمامية
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'first_name' => $request->user()->first_name,
                    'middle_name' => $request->user()->middle_name,
                    'last_name' => $request->user()->last_name,

                    'username' => $request->user()->username,
                    'email' => $request->user()->email,

                    'phone' => $request->user()->phone,
                    'gender' => $request->user()->gender,
                    'date_of_birth' => $request->user()->date_of_birth,
                    'address' => $request->user()->address,

                    'role' => $request->user()->role,

                    'profile_photo_url' => $request->user()->profile_photo_url,
                ] : null,
            ],
            'roleData' => $request->user() ? match ($request->user()->role) {
                'patient' => $request->user()->patient,
                'doctor' => $request->user()->doctor,
                'receptionist' => $request->user()->receptionist,
                default => null,
            } : null,
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error'   => fn() => $request->session()->get('error'),
            ],
        ]);
    }
}
