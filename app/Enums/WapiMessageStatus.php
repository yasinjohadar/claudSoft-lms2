<?php

namespace App\Enums;

enum WapiMessageStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';
    case SentPendingConfirmation = 'sent_pending_confirmation';
}
