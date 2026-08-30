<?php

namespace Ridho\JustSubs\Exceptions;

use Exception;

class AlreadySubscribedException extends Exception
{
    public static function forSubscriber($subscriber)
    {
        return new self('Subscriber already has an active subscription.');
    }
}
