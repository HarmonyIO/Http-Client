<?php declare(strict_types=1);

namespace HarmonyIO\HttpClientTest\Unit\Exception;

use HarmonyIO\HttpClient\Exception\InvalidBody;
use HarmonyIO\PHPUnitExtension\TestCase;

class InvalidBodyTest extends TestCase
{
    public function testMessageWhenBodyTypeIsInteger(): void
    {
        $this->expectException(InvalidBody::class);
        $this->expectExceptionMessage('Expected body to be of type string|Amp\Artax\RequestBody, but got integer.');

        throw new InvalidBody(1);
    }

    public function testMessageWhenBodyTypeIsAnObject(): void
    {
        $this->expectException(InvalidBody::class);
        $this->expectExceptionMessage(
            'Expected body to be of type string|Amp\Artax\RequestBody, but got DateTimeImmutable.'
        );

        throw new InvalidBody(new \DateTimeImmutable());
    }
}
