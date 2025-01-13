<?php

namespace App\Config;

enum MessageStatus: string
{
    case SENT = 'sent';
    case READ = 'read';
}
