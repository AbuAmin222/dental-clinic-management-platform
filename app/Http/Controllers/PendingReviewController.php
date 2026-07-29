<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Class PendingReviewController
 *
 * Serves the holding screen for accounts awaiting administrative activation.
 * Reached exclusively via the {@see \App\Http\Middleware\EnsureUserIsActive}
 * redirect gate — never linked to directly from application navigation.
 *
 * @package App\Http\Controllers
 */
class PendingReviewController extends Controller
{
    public function show(): InertiaResponse
    {
        return Inertia::render('Auth/PendingReview');
    }
}
