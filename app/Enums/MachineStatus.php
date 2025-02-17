<?php

namespace App\Enums;

enum MachineStatus: string
{
    case IDLE = 'idle';
    case PROCESSING = 'processing';
}
