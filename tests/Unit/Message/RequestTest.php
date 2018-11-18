<?php declare(strict_types=1);

namespace HarmonyIO\HttpClientTest\Unit\Message;

use Amp\Artax\Request as ArtaxRequest;
use Amp\Artax\StringBody;
use HarmonyIO\HttpClient\Exception\InvalidBody;
use HarmonyIO\HttpClient\Message\Request;
use HarmonyIO\PHPUnitExtension\TestCase;

class RequestTest extends TestCase
{
    public function testConstructorSetsTheUri(): void
    {
        $request = new Request('https://example.com');

        $this->assertSame('https://example.com', $request->getArtaxRequest()->getUri());
    }

    public function testConstructorSetsTheMethod(): void
    {
        $request = new Request('https://example.com', 'POST');

        $this->assertSame('POST', $request->getArtaxRequest()->getMethod());
    }

    public function testSetProtocolVersions(): void
    {
        $request = (new Request('https://example.com', 'POST'))->setProtocolVersions('1.0', '1.1');

        $this->assertSame(['1.0', '1.1'], $request->getArtaxRequest()->getProtocolVersions());
    }

    public function testAddHeader(): void
    {
        $request = (new Request('https://example.com', 'POST'))->addHeader('foo', 'bar');

        $this->assertTrue($request->getArtaxRequest()->hasHeader('foo'));
        $this->assertSame('bar', $request->getArtaxRequest()->getHeader('foo'));
    }

    public function testSetBody(): void
    {
        $request = (new Request('https://example.com', 'POST'))->setBody('foo');

        $this->assertInstanceOf(StringBody::class, $request->getArtaxRequest()->getBody());
    }

    public function testSetBodyThrowsOnInvalidInput(): void
    {
        $this->expectException(InvalidBody::class);
        $this->expectExceptionMessage('Expected body to be of type string|Amp\Artax\RequestBody, but got DateTimeImmutable.');

        (new Request('https://example.com', 'POST'))->setBody(new \DateTimeImmutable());
    }

    public function testGetArtaxRequest(): void
    {
        $request = (new Request('https://example.com', 'POST'));

        $this->assertInstanceOf(ArtaxRequest::class, $request->getArtaxRequest());
    }
}
