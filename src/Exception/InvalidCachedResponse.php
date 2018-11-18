<?php declare(strict_types=1);

namespace HarmonyIO\HttpClient\Exception;

class InvalidCachedResponse extends Exception
{
    public function __construct()
    {
        parent::__construct('The cached response is in an unexpected format.');
    }
}
