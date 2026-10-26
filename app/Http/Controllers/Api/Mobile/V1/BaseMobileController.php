<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;

/**
 * أساس متحكمات API تطبيق الجوال (للطلاب فقط).
 */
abstract class BaseMobileController extends Controller
{
    use ApiResponse;
}
