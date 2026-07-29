<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DashboardsController
 *
 * Central Gateway Controller mediating initial access traffic post-login.
 * Adheres strictly to the Open-Closed Principle (OCP) by dynamically resolving
 * role-based dashboard routes without modifying control logic when new roles are introduced.
 *
 * @package App\Http\Controllers
 */
class DashboardsController extends Controller
{
    /**
     * Dynamically resolve and route authenticated user to their respective dashboard interface.
     *
     * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Http\RedirectResponse
     */
    public function index(): InertiaResponse|RedirectResponse|Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $role = strtolower((string) $user->role);
        $targetRoute = "{$role}.dashboard";

        if ($role !== '' && Route::has($targetRoute)) {
            return redirect()->route($targetRoute);
        }

        return Inertia::render('Dashboard');
    }
}
