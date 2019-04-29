<?php declare(strict_types=1);

namespace HarmonyIO\HttpClientTest\Unit\Message;

use HarmonyIO\Cache\CacheableRequest;
use HarmonyIO\Cache\Key;
use HarmonyIO\Cache\Ttl;
use HarmonyIO\HttpClient\Message\CachingRequest;
use HarmonyIO\PHPUnitExtension\TestCase;

class CachingRequestTest extends TestCase
{
    public function testImplementsCachingInterface(): void
    {
        $this->assertInstanceOf(
            CacheableRequest::class,
            (new CachingRequest('TestKey', new Ttl(10), 'https://example.com')),
        );
    }

    public function testGetCachingKey(): void
    {
        $key = (new CachingRequest('TestKey', new Ttl(10), 'https://example.com'))->getCachingKey();

        $this->assertInstanceOf(Key::class, $key);
    }

    public function testGetTtl(): void
    {
        $this->assertSame(
            10,
            (new CachingRequest('TestKey', new Ttl(10), 'https://example.com'))->getTtl()->getTtlInSeconds(),
        );
    }
}
