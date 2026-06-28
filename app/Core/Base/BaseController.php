<?php

namespace App\Core\Base;

use App\Core\Traits\ApiResponses;
use Illuminate\Routing\Controller as LaravelController;

abstract class BaseController extends LaravelController
{
    use ApiResponses;
}
