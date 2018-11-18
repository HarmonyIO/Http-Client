<?php declare(strict_types=1);

namespace HarmonyIO\HttpClient\Exception;

class InvalidBody extends Exception
{
    private const MESSAGE = 'Expected body to be of type string|Amp\Artax\RequestBody, but got %s.';

    /**
     * @param mixed $body
     */
    public function __construct($body)
    {
        parent::__construct(sprintf(self::MESSAGE, $this->getType($body)));
    }

    /**
     * @param mixed $body
     */
    private function getType($body): string
    {
        $type = gettype($body);

        if ($type !== 'object') {
            return $type;
        }

        return get_class($body);
    }
}
