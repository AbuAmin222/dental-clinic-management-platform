<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RegisterEmailCheckController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $isTaken = User::where('email', $request->email)->exists();

        return response()->json([
            'valid' => true,
            'available' => !$isTaken,
            'message' => $isTaken ? 'This email is already registered.' : 'Email is available.'
        ]);
    }
}
