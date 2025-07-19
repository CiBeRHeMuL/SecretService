<?php

namespace App\Presentation\Api\Enum;

enum ErrorCodeEnum: int
{
    case INTERNAL_ERROR = 500;
    case NOT_FOUND = 404;
}
