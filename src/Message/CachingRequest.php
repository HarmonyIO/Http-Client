<?php declare(strict_types=1);

namespace HarmonyIO\HttpClient\Message;

use HarmonyIO\Cache\CacheableRequest;
use HarmonyIO\Cache\Key;
use HarmonyIO\Cache\Ttl;

class CachingRequest extends Request implements CacheableRequest
{
    private const CACHE_TYPE = 'HttpRequest';

    /** @var string */
    private $key;

    /** @var Ttl */
    private $ttl;

    public function __construct(string $key, Ttl $ttl, string $uri, string $method = 'GET')
    {
        $this->key = $key;
        $this->ttl = $ttl;

        parent::__construct($uri, $method);
    }

    public function getCachingKey(): Key
    {
        return new Key(self::CACHE_TYPE, $this->key, md5(serialize($this)));
    }

    public function getTtl(): Ttl
    {
        return $this->ttl;
    }
}
