<?php

declare(strict_types=1);

namespace App\Factories\Model;

use RuntimeException;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ProfileModelFactory
 *
 * المصنع المسؤول عن تحديد الكلاس الخاص بنموذج البروفايل (Profile Model FQCN)
 * المقابل لدور المستخدم بطريقة مرنة ومحميّة.
 *
 * المبادئ والأنماط المطبقة:
 * - Open-Closed Principle (OCP): قابل للتوسيع دون تعديل السورس كود.
 * - Single Responsibility Principle (SRP): مسئول حصرياً عن دقة جلب نموذج البروفايل.
 * - High Cohesion & Low Coupling: فصل تام لمنطق الربط عن نموذج المستخدم الرئيسي User.
 * - Robust Type Validation: التأكد الصارم من تبعية الكلاس لـ Eloquent Model.
 *
 * @package App\Factories\Model
 */
final class ProfileModelFactory
{
    /**
     * خريطة ربط الأدوار الصريحة بأسماء النماذج الكاملة (FQCN).
     *
     * @var array<string, class-string<Model>>
     */
    private static array $mappings = [
        'doctor'       => \App\Models\Doctor::class,
        'patient'      => \App\Models\Patient::class,
        'receptionist' => \App\Models\Receptionist::class,
    ];

    /**
     * دالة حل مخصصة اختيارية (Custom Resolver Hook).
     *
     * @var (callable(string): ?string)|null
     */
    private static $customResolver = null;

    /**
     * منع إنشاء كائن من هذا الكلاس (Static Utility Factory).
     */
    private function __construct() {}

    /**
     * تحديد الكلاس الخاص بنموذج البروفايل استناداً إلى اسم الدور.
     *
     * @param string $role
     * @return class-string<Model>
     * @throws RuntimeException في حال عدم إمكانية العثور على النموذج أو عدم صحة نوعه.
     */
    public static function resolveClass(string $role): string
    {
        $normalizedRole = Str::lower(trim($role));

        // 1. البحث في خريطة التعيين الصريحة المبرمجة
        if (isset(self::$mappings[$normalizedRole])) {
            $class = self::$mappings[$normalizedRole];
            self::ensureValidModelClass($class, $role);
            return $class;
        }

        // 2. فحص محدد المظاهر المخصص (Custom Resolver Hook) إن وجد
        if (self::$customResolver !== null) {
            $resolved = (self::$customResolver)($role);
            if ($resolved !== null && class_exists($resolved)) {
                self::ensureValidModelClass($resolved, $role);
                return $resolved;
            }
        }

        // 3. الآلية الاحتياطية: الاعتماد على نمط التسمية القياسي (StudlyCase Convention)
        $formattedRole = Str::studly($role);
        $modelClass = "App\\Models\\{$formattedRole}";

        if (!class_exists($modelClass)) {
            throw new RuntimeException(
                "Architectural Integrity Violation: Concrete profile model [{$modelClass}] for role [{$role}] is missing or not registered in ProfileModelFactory."
            );
        }

        self::ensureValidModelClass($modelClass, $role);

        return $modelClass;
    }

    /**
     * تسجيل أو تعديل تعيين دور بنموذج معين ديناميكياً (نقطة التوسعة التابعة لـ OCP).
     *
     * @param string $role
     * @param class-string<Model> $modelClass
     * @return void
     */
    public static function registerMapping(string $role, string $modelClass): void
    {
        $normalizedRole = Str::lower(trim($role));
        self::ensureValidModelClass($modelClass, $role);
        self::$mappings[$normalizedRole] = $modelClass;
    }

    /**
     * تسجيل مصفوفة تعيينات متعددة دفعة واحدة.
     *
     * @param array<string, class-string<Model>> $mappings
     * @return void
     */
    public static function registerMappings(array $mappings): void
    {
        foreach ($mappings as $role => $modelClass) {
            self::registerMapping((string) $role, $modelClass);
        }
    }

    /**
     * تعيين محدد مخصص للتسميات الديناميكية.
     *
     * @param callable(string): ?string $resolver
     * @return void
     */
    public static function setCustomResolver(callable $resolver): void
    {
        self::$customResolver = $resolver;
    }

    /**
     * إعادة الخريطة والمحدد للحالة الافتراضية (مفيد جداً لاختبارات الوحدات isolated unit tests).
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$mappings = [
            'doctor'       => \App\Models\Doctor::class,
            'patient'      => \App\Models\Patient::class,
            'receptionist' => \App\Models\Receptionist::class,
        ];
        self::$customResolver = null;
    }

    /**
     * التحقق من سلامة كلاس النموذج (موجود بالفعل ويمتد من Eloquent Model).
     *
     * @param string $class
     * @param string $role
     * @throws RuntimeException
     */
    private static function ensureValidModelClass(string $class, string $role): void
    {
        if (!class_exists($class)) {
            throw new RuntimeException(
                "Architectural Integrity Violation: Mapped class [{$class}] for role [{$role}] does not exist."
            );
        }

        if (!is_subclass_of($class, Model::class)) {
            throw new RuntimeException(
                "Architectural Integrity Violation: Resolved profile class [{$class}] for role [{$role}] must extend [" . Model::class . "]."
            );
        }
    }
}
