<?php declare(strict_types=1);

namespace HarmonyIO\HttpClientTest\Unit\Message;

use Amp\Artax\Response as ArtaxResponse;
use HarmonyIO\Cache\CacheableResponse;
use HarmonyIO\HttpClient\Exception\InvalidCachedResponse;
use HarmonyIO\HttpClient\Message\Response;
use HarmonyIO\PHPUnitExtension\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ResponseTest extends TestCase
{
    /** @var Response */
    private $response;

    public function setUp(): void
    {
        /** @var MockObject|ArtaxResponse $artaxResponse */
        $artaxResponse = $this->createMock(ArtaxResponse::class);

        $artaxResponse
            ->method('getProtocolVersion')
            ->willReturn('1.0')
        ;

        $artaxResponse
            ->method('getStatus')
            ->willReturn(200)
        ;

        $artaxResponse
            ->method('getReason')
            ->willReturn('OK')
        ;

        $artaxResponse
            ->method('getHeaders')
            ->willReturn([
                'foo' => [
                    'bar',
                    'baz',
                ],
            ])
        ;

        $this->response = new Response($artaxResponse, 'The body');
    }

    public function testImplementsCacheableInterface(): void
    {
        $this->assertInstanceOf(CacheableResponse::class, $this->response);
    }

    public function testGetProtocolVersion(): void
    {
        $this->assertSame('1.0', $this->response->getProtocolVersion());
    }

    public function testGetNumericalStatusCode(): void
    {
        $this->assertSame(200, $this->response->getNumericalStatusCode());
    }

    public function testGetTextualStatusCode(): void
    {
        $this->assertSame('OK', $this->response->getTextualStatusCode());
    }

    public function testHasHeaderReturnsTrueWhenHeaderExists(): void
    {
        $this->assertTrue($this->response->hasHeader('foo'));
    }

    public function testHasHeaderReturnsFalseWhenHeaderDoesNotExist(): void
    {
        $this->assertFalse($this->response->hasHeader('non-existing'));
    }

    public function testGetHeaderReturnsNullWhenItDoesNotExist(): void
    {
        $this->assertNull($this->response->getHeader('non-existing'));
    }

    public function testGetHeaderReturnsFirstValue(): void
    {
        $this->assertSame('bar', $this->response->getHeader('foo'));
    }

    public function testGetHeaderArrayReturnsEmptyArrayWhenItDoesNotExist(): void
    {
        $this->assertSame([], $this->response->getHeaderArray('non-existing'));
    }

    public function testGetHeaderArrayReturnsAllValues(): void
    {
        $this->assertSame(['bar', 'baz'], $this->response->getHeaderArray('foo'));
    }

    public function testGetBody(): void
    {
        $this->assertSame('The body', $this->response->getBody());
    }

    public function testSerialize(): void
    {
        $this->assertSame(
            require TEST_FIXTURE_DIR . '/Message/serialized-response.php',
            serialize($this->response),
        );
    }

    public function testUnserialize(): void
    {
        $response = unserialize(require TEST_FIXTURE_DIR . '/Message/serialized-response.php');

        $this->assertSame('1.0', $response->getProtocolVersion());
        $this->assertSame(200, $response->getNumericalStatusCode());
        $this->assertSame('OK', $response->getTextualStatusCode());
        $this->assertTrue($response->hasHeader('foo'));
        $this->assertSame(['bar', 'baz'], $response->getHeaderArray('foo'));
        $this->assertSame('The body', $response->getBody());
    }

    public function testUnserializeThrowsOnInvalidResponseFormat(): void
    {
        $this->expectException(InvalidCachedResponse::class);
        $this->expectExceptionMessage('The cached response is in an unexpected format.');

        $this->response->unserialize('foobar');
    }
}
