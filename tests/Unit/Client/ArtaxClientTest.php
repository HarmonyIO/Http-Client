<?php declare(strict_types=1);

namespace HarmonyIO\HttpClientTest\Unit\Client;

use Amp\Artax\Client as ArtaxBaseClient;
use Amp\Artax\Request as ArtaxRequest;
use Amp\Artax\Response as ArtaxResponse;
use Amp\ByteStream\InputStream;
use Amp\ByteStream\Message;
use Amp\Loop;
use Amp\Success;
use HarmonyIO\Cache\Cache;
use HarmonyIO\Cache\Ttl;
use HarmonyIO\HttpClient\Client\ArtaxClient;
use HarmonyIO\HttpClient\Client\Client;
use HarmonyIO\HttpClient\Message\CachingRequest;
use HarmonyIO\HttpClient\Message\Request;
use HarmonyIO\HttpClient\Message\Response;
use HarmonyIO\PHPUnitExtension\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ArtaxClientTest extends TestCase
{
    /** @var MockObject|ArtaxBaseClient */
    private $artaxBaseClient;

    /** @var MockObject|Cache */
    private $cache;

    public function setUp(): void
    {
        $this->artaxBaseClient = $this->createMock(ArtaxBaseClient::class);
        $this->cache           = $this->createMock(Cache::class);
    }

    public function testImplementsClientInterface(): void
    {
        $artaxClient = new ArtaxClient($this->artaxBaseClient, $this->cache);

        $this->assertInstanceOf(Client::class, $artaxClient);
    }

    public function testRequestDoesNotCache(): void
    {
        $this->cache
            ->expects($this->never())
            ->method('exists')
        ;

        $this->cache
            ->expects($this->never())
            ->method('store')
        ;

        $this->cache
            ->expects($this->never())
            ->method('get')
        ;

        $this->artaxBaseClient
            ->method('request')
            //phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
            ->willReturnCallback(function (ArtaxRequest $request) {
                /** @var MockObject|InputStream $inputStream */
                $inputStream = $this->createMock(InputStream::class);
                $inputStream
                    ->method('read')
                    ->willReturnOnConsecutiveCalls(new Success('foobar'), new Success(null))
                ;
                $message = new Message($inputStream);
                $response = $this->createMock(Response::class);
                $response
                    ->method('getBody')
                    ->willReturn($message)
                ;
                return new Success($response);
            })
        ;

        $artaxClient = new ArtaxClient($this->artaxBaseClient, $this->cache);

        $artaxClient->request(new Request('https://example.com'));
    }

    public function testRequestCachesCachingRequest(): void
    {
        $this->cache
            ->method('exists')
            ->willReturnOnConsecutiveCalls(new Success(false))
        ;

        $this->cache
            ->expects($this->once())
            ->method('store')
            ->willReturn(new Success())
        ;

        $this->cache
            ->method('get')
            ->willReturn(new Success(require TEST_FIXTURE_DIR . '/Message/serialized-response.php'))
        ;

        $this->artaxBaseClient
            ->method('request')
            //phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
            ->willReturnCallback(function (ArtaxRequest $request) {
                /** @var MockObject|InputStream $inputStream */
                $inputStream = $this->createMock(InputStream::class);

                $inputStream
                    ->method('read')
                    ->willReturnOnConsecutiveCalls(new Success('foobar'), new Success(null))
                ;

                $message = new Message($inputStream);

                $response = $this->createMock(ArtaxResponse::class);

                $response
                    ->method('getBody')
                    ->willReturn($message)
                ;

                $response
                    ->method('getStatus')
                    ->willReturn(200)
                ;

                return new Success($response);
            })
        ;

        $artaxClient = new ArtaxClient($this->artaxBaseClient, $this->cache);

        Loop::run(static function () use ($artaxClient) {
            yield $artaxClient->request(new CachingRequest('Key', new Ttl(10), 'https://example.com'));
        });
    }

    public function testRequestDoesNotCacheErroringCachingRequest(): void
    {
        $this->cache
            ->expects($this->exactly(2))
            ->method('exists')
            ->willReturnOnConsecutiveCalls(new Success(false), new Success(false))
        ;

        $this->cache
            ->expects($this->never())
            ->method('store')
            ->willReturn(new Success())
        ;

        $this->cache
            ->expects($this->never())
            ->method('get')
            ->willReturn(new Success(require TEST_FIXTURE_DIR . '/Message/serialized-response.php'))
        ;

        $this->artaxBaseClient
            ->method('request')
            //phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
            ->willReturnCallback(function (ArtaxRequest $request) {
                /** @var MockObject|InputStream $inputStream */
                $inputStream = $this->createMock(InputStream::class);

                $inputStream
                    ->method('read')
                    ->willReturnOnConsecutiveCalls(new Success('foobar'), new Success(null))
                ;

                $message = new Message($inputStream);

                $response = $this->createMock(ArtaxResponse::class);

                $response
                    ->method('getBody')
                    ->willReturn($message)
                ;

                $response
                    ->method('getStatus')
                    ->willReturn(404)
                ;

                return new Success($response);
            })
        ;

        $artaxClient = new ArtaxClient($this->artaxBaseClient, $this->cache);

        Loop::run(static function () use ($artaxClient) {
            yield $artaxClient->request(new CachingRequest('Key', new Ttl(10), 'https://example.com'));
            yield $artaxClient->request(new CachingRequest('Key', new Ttl(10), 'https://example.com'));
        });
    }

    public function testRequestUsesCacheForConsecutiveCachingRequests(): void
    {
        $this->cache
            ->expects($this->exactly(2))
            ->method('exists')
            ->willReturnOnConsecutiveCalls(new Success(false), new Success(true))
        ;

        $this->cache
            ->expects($this->once())
            ->method('store')
            ->willReturn(new Success())
        ;

        $this->cache
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturn(new Success(require TEST_FIXTURE_DIR . '/Message/serialized-response.php'))
        ;

        $this->artaxBaseClient
            ->method('request')
            //phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
            ->willReturnCallback(function (ArtaxRequest $request) {
                /** @var MockObject|InputStream $inputStream */
                $inputStream = $this->createMock(InputStream::class);

                $inputStream
                    ->method('read')
                    ->willReturnOnConsecutiveCalls(new Success('foobar'), new Success(null))
                ;

                $message = new Message($inputStream);

                $response = $this->createMock(ArtaxResponse::class);

                $response
                    ->method('getBody')
                    ->willReturn($message)
                ;

                $response
                    ->method('getStatus')
                    ->willReturn(200)
                ;

                return new Success($response);
            })
        ;

        $artaxClient = new ArtaxClient($this->artaxBaseClient, $this->cache);

        Loop::run(static function () use ($artaxClient) {
            yield $artaxClient->request(new CachingRequest('Key', new Ttl(10), 'https://example.com'));
            yield $artaxClient->request(new CachingRequest('Key', new Ttl(10), 'https://example.com'));
        });
    }
}
