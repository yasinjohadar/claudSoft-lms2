<?php

namespace App\Enums;

enum WapiMessageType: string
{
    case Message = 'message';
    case Template = 'template';
    case Campaign = 'campaign';
}
