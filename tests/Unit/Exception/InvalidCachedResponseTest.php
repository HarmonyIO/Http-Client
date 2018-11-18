<?php declare(strict_types=1);

namespace HarmonyIO\HttpClientTest\Unit\Exception;

use HarmonyIO\PHPUnitExtension\TestCase;

class InvalidCachedResponseTest extends TestCase
{
    public function testMessage(): void
    {
        $this->expectException(InvalidCachedResponse::class);
        $this->expectExceptionMessage('The cached response is in an unexpected format.');

        throw new InvalidBody(1);
    }
}
