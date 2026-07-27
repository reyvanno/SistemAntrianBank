<?php

namespace App\Enums;

enum QueueStatus: string
{
    case WAITING = 'WAITING';
    case CALLED = 'CALLED';
    case PROCESSING = 'PROCESSING';
    case DONE = 'DONE';
    case SKIPPED = 'SKIPPED';
    case CANCELLED = 'CANCELLED';
}
