<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * Class Controller
 *
 * Abstract Base Architecture Controller for Dental Clinic Application (DCA).
 * Establishes global type safety, standard authorization hooks, and request validation traits.
 *
 * @package App\Http\Controllers
 */
abstract class Controller
{
    use AuthorizesRequests, ValidatesRequests;
}
