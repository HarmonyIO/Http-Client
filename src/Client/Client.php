<?php declare(strict_types=1);

namespace HarmonyIO\HttpClient\Client;

use Amp\Promise;
use HarmonyIO\HttpClient\Message\Request;

interface Client
{
    public function request(Request $request): Promise;
}
