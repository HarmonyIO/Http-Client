<?php declare(strict_types=1);

namespace HarmonyIO\HttpClient\Message;

use Amp\Artax\Request as ArtaxRequest;
use Amp\Artax\RequestBody;
use HarmonyIO\HttpClient\Exception\InvalidBody;

class Request
{
    /** @var ArtaxRequest */
    private $artaxRequest;

    public function __construct(string $uri, string $method = 'GET')
    {
        $this->artaxRequest = new ArtaxRequest($uri, $method);
    }

    public function setProtocolVersions(string ...$versions): Request
    {
        $this->artaxRequest = $this->artaxRequest->withProtocolVersions($versions);

        return $this;
    }

    public function addHeader(string $key, string $value): Request
    {
        $this->artaxRequest = $this->artaxRequest->withHeader($key, $value);

        return $this;
    }

    /**
     * @param string|RequestBody $body
     * @throws InvalidBody
     */
    public function setBody($body): Request
    {
        if (!is_string($body) && !($body instanceof RequestBody)) {
            throw new InvalidBody($body);
        }

        $this->artaxRequest = $this->artaxRequest->withBody($body);

        return $this;
    }

    public function getArtaxRequest(): ArtaxRequest
    {
        return $this->artaxRequest;
    }
}
