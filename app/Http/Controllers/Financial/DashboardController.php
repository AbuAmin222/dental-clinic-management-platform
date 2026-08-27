<?php

declare(strict_types=1);

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DashboardController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        return Inertia::render('Financial/Dashboard', [
            'financial' => $request->user()?->financial,
        ]);
    }
}
