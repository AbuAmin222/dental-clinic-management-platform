<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class LoginResponse
 *
 * Intercepts post-authentication lifecycles to negotiate appropriate multi-role redirects.
 * Tailored for high performance, returning fast JSON metadata or zero-cost redirection headers.
 *
 * @package App\Http\Responses
 */
class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * Dynamically evaluates active security context roles to compute target dashboard routing coordinates.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request): Response
    {
        $user = Auth::user();

        // Compute localized dashboard path dynamically
        $role = $user ? strtolower((string) $user->role) : 'default';

        $redirectPath = match ($role) {
            'doctor'       => route('doctor.dashboard'),
            'patient'      => route('patient.dashboard'),
            'receptionist' => route('receptionist.dashboard'),
            'admin'        => route('admin.dashboard'),
            default        => route('dashboard'),
        };

        return $request->wantsJson()
            ? new JsonResponse(['two_factor' => false, 'redirect' => $redirectPath], Response::HTTP_OK)
            : redirect()->intended($redirectPath);
    }
}
