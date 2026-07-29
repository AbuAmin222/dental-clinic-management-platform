<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterEmailCheckRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RegisterEmailCheckController
 *
 * Real-time asynchronous boundary verification utility.
 * Delegates database existence lookup to UserService layer and relies on FormRequest for validation.
 *
 * @package App\Http\Controllers\Auth
 */
class RegisterEmailCheckController extends Controller
{
    /**
     * Inject UserService.
     */
    public function __construct(
        protected readonly UserService $userService
    ) {}

    /**
     * Intercept validation stream to verify corporate email availability.
     *
     * @param  \App\Http\Requests\Auth\RegisterEmailCheckRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(RegisterEmailCheckRequest $request): JsonResponse
    {
        $email = (string) $request->validated('email');

        $isAvailable = $this->userService->isEmailAvailable($email);

        return new JsonResponse([
            'valid'     => true,
            'available' => $isAvailable,
            'message'   => $isAvailable
                ? __('Email is available.')
                : __('This email is already registered.')
        ], Response::HTTP_OK);
    }
}
