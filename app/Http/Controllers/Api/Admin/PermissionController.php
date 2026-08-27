<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StorePermissionRequest;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;

/**
 * كتالوج الصلاحيات المتاحة في النظام — نقطة البداية لأي منح (لدور أو مستخدم). إنشاء
 * صلاحية جديدة لا يمنحها لأحد تلقائياً؛ المنح خطوة منفصلة عبر RolePermissionController
 * أو UserPermissionController أدناه (فصل مسؤوليات: "ما الصلاحيات الموجودة" عن "من يملكها").
 */
class PermissionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Permission::orderBy('group')->orderBy('name')->get(),
        ]);
    }

    public function store(StorePermissionRequest $request): JsonResponse
    {
        $permission = Permission::create($request->validated());

        return response()->json(['data' => $permission], 201);
    }
}
