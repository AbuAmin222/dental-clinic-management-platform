<?php

declare(strict_types=1);

/**
 * مسارات API الخاصة حصراً بدور Admin — لإدارة الأدوار والصلاحيات ديناميكياً. نُقلت هنا
 * من routes/api.php (2026-08-15) لتطابق نفس مبدأ فصل مسارات كل دور المُطبَّق فعلياً على
 * الأدوار الخمسة الأخرى عبر app/Routing/Registrars/{Role}RouteRegistrar.php — routes/api.php
 * نفسه يبقى ملف "تجميع/تحميل" بسيط فقط، مطابقاً لنفس دور routes/web.php الذي لا يحتوي
 * منطق مسارات أي دور مباشرة، بل يُحمِّلها من routes/roles/*.php والـ Registrars.
 *
 * يُطبِّق القرار المؤكد: "المسؤول نفسه يعطي الصلاحية التي يريدها لأي موظف أو دور" عبر
 * مسارين مستقلين:
 *   - roles/{role}/permissions  → منح لكامل الدور (كل من يحمله)
 *   - users/{user}/permissions  → منح لمستخدم واحد بعينه، بمعزل عن دوره
 */

use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Admin\RolePermissionController;
use App\Http\Controllers\Api\Admin\UserPermissionController;
use App\Http\Controllers\Api\Admin\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::apiResource('permissions', PermissionController::class)->only(['index', 'store']);

Route::get('roles/{role}/permissions', [RolePermissionController::class, 'index'])->name('roles.permissions.index');
Route::post('roles/{role}/permissions', [RolePermissionController::class, 'store'])->name('roles.permissions.store');
Route::delete('roles/{role}/permissions/{permission}', [RolePermissionController::class, 'destroy'])->name('roles.permissions.destroy');

Route::get('users/{user}/permissions', [UserPermissionController::class, 'index'])->name('users.permissions.index');
Route::post('users/{user}/permissions', [UserPermissionController::class, 'store'])->name('users.permissions.store');
Route::delete('users/{user}/permissions/{permission}', [UserPermissionController::class, 'destroy'])->name('users.permissions.destroy');

Route::get('users/{user}/roles', [UserRoleController::class, 'index'])->name('users.roles.index');
Route::post('users/{user}/roles', [UserRoleController::class, 'store'])->name('users.roles.store');
Route::delete('users/{user}/roles/{role}', [UserRoleController::class, 'destroy'])->name('users.roles.destroy');
