<?php declare(strict_types=1);

namespace HarmonyIO\HttpClient\Client;

use Amp\Artax\Client as ArtaxBaseClient;
use Amp\Artax\Response as ArtaxResponse;
use Amp\Promise;
use HarmonyIO\Cache\Cache;
use HarmonyIO\Cache\Item;
use HarmonyIO\HttpClient\Message\CachingRequest;
use HarmonyIO\HttpClient\Message\Request;
use HarmonyIO\HttpClient\Message\Response;
use function Amp\call;

class ArtaxClient implements Client
{
    /** @var ArtaxBaseClient */
    private $artaxClient;

    /** @var Cache */
    private $cache;

    public function __construct(ArtaxBaseClient $artaxClient, Cache $cache)
    {
        $this->artaxClient = $artaxClient;
        $this->cache       = $cache;
    }

    public function request(Request $request): Promise
    {
        if ($request instanceof CachingRequest) {
            return $this->makeCachingRequest($request);
        }

        return $this->makeRequest($request);
    }

    private function makeCachingRequest(CachingRequest $request): Promise
    {
        return call(function () use ($request) {
            if (!yield $this->cache->exists($request->getCachingKey())) {
                /** @var Response $response */
                $response = yield $this->makeRequest($request);

                if ($response->getNumericalStatusCode() >= 400) {
                    return $response;
                }

                yield $this->cache->store(new Item(
                    $request->getCachingKey(),
                    serialize($response),
                    $request->getTtl()
                ));
            }

            return unserialize(yield $this->cache->get($request->getCachingKey()));
        });
    }

    private function makeRequest(Request $request): Promise
    {
        return call(function () use ($request) {
            /** @var ArtaxResponse $response */
            $response = yield $this->artaxClient->request($request->getArtaxRequest());
            $body     = yield $response->getBody();

            return new Response($response, $body);
        });
    }
}
