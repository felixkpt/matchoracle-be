<?php

namespace App\Enums;

enum ResultStatus: int
{
    case PENDING = 0;
    case FETCHED = 1;
    case MISSING = 2;
}

enum OddStatus: int
{
    case PENDING = 0;
    case FETCHED = 1;
    case MISSING = 2;
}
