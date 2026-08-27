<?php

declare(strict_types=1);

use App\Http\Controllers\Mcp\McpSseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/**
 * HTTP+SSE transport for the MCP server (app/Mcp/*). `role:admin` because these tools can
 * run arbitrary read-only SQL against the allowlisted schema and execute domain Actions
 * that mutate patient/appointment/user data when MCP_ALLOW_MUTATIONS=true — this is
 * strictly an administrative surface, not something to expose to any authenticated role.
 * See MCP_INTEGRATION_GUIDE.md for the stdio transport (the one desktop MCP clients
 * normally use instead of this).
 */
Route::middleware(['auth:sanctum', 'role:admin', 'throttle:mcp'])->prefix('mcp')->name('mcp.')->group(function (): void {
    Route::get('/sse', [McpSseController::class, 'stream'])->name('sse');
    Route::post('/messages', [McpSseController::class, 'receive'])->name('messages');
});

/*
|--------------------------------------------------------------------------
| Per-Role API Route Files
|--------------------------------------------------------------------------
| هذا الملف يبقى تجميعاً بسيطاً فقط — بنفس مبدأ routes/web.php الذي يُحمِّل مسارات كل
| دور من ملف/Registrar منفصل بدل تعريفها هنا مباشرة. أي دور يحتاج مستقبلاً مسارات API
| خاصة به يحصل على ملف مماثل في routes/api/{role}.php.
*/
Route::middleware(['auth:sanctum', 'role:admin'])
    ->prefix('admin')
    ->name('api.admin.')
    ->group(base_path('routes/api/admin.php'));
