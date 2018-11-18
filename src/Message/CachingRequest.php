<?php declare(strict_types=1);

namespace HarmonyIO\HttpClient\Message;

use HarmonyIO\Cache\CacheableRequest;
use HarmonyIO\Cache\Key;

class CachingRequest extends Request implements CacheableRequest
{
    private const CACHE_TYPE = 'HttpRequest';

    /** @var string */
    private $key;

    /** @var int */
    private $ttl;

    public function __construct(string $key, int $ttl, string $uri, string $method = 'GET')
    {
        $this->key = $key;
        $this->ttl = $ttl;

        parent::__construct($uri, $method);
    }

    public function getCachingKey(): Key
    {
        return new Key(self::CACHE_TYPE, $this->key, md5(serialize($this)));
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }
}
