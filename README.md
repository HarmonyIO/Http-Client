# Http-Client

[![Latest Stable Version](https://poser.pugx.org/harmonyio/http-client/v/stable)](https://packagist.org/packages/harmonyio/http-client)
[![Build Status](https://travis-ci.org/HarmonyIO/Http-Client.svg?branch=master)](https://travis-ci.org/HarmonyIO/Http-Client)
[![Build status](https://ci.appveyor.com/api/projects/status/qe3volxj5pxqaguu/branch/master?svg=true)](https://ci.appveyor.com/project/PeeHaa/http-client/branch/master)
[![Coverage Status](https://coveralls.io/repos/github/HarmonyIO/Http-Client/badge.svg?branch=master)](https://coveralls.io/github/HarmonyIO/Http-Client?branch=master)
[![License](https://poser.pugx.org/harmonyio/http-client/license)](https://packagist.org/packages/harmonyio/http-client)

Async caching aware http client

## Requirements

- PHP 7.3
- Redis (if wanting to use the Redis caching provider)

In addition for non-blocking context one of the following event libraries should be installed:

- [ev](https://pecl.php.net/package/ev)
- [event](https://pecl.php.net/package/event)
- [php-uv](https://github.com/bwoebi/php-uv)

## Installation

```
composer require harmonyio/http-client
```

## Usage

### Caching requests

The following example shows how to make a request and cache the result so consecutive calls will used the cached results instead of making a new external HTTP call.

```php
<?php declare(strict_types=1);

namespace Foo;

use Amp\Artax\DefaultClient;
use Amp\Redis\Client;
use HarmonyIO\Cache\Provider\Redis;
use HarmonyIO\Cache\Ttl;
use HarmonyIO\HttpClient\Client\ArtaxClient;
use HarmonyIO\HttpClient\Message\CachingRequest;
use function Amp\wait;

// create instance of the HTTP client
$httpClient = new ArtaxClient(
    new DefaultClient(),
    new Redis(new Client('tcp://127.0.0.1:6379'))
);

// create a new request to be cached for 10 seconds
$request = new CachingRequest('TestRequestKey', new Ttl(10), 'https://httpbin.org/get');

// make the request and cache it
$result = wait($httpClient->request($request));

// all consecutive requests will now used the cached result instead of calling the external service again
$result = wait($httpClient->request($request));
```

### Non-caching requests

It is also possible to make non-caching requests.

```php
<?php declare(strict_types=1);

namespace Foo;

use Amp\Artax\DefaultClient;
use Amp\Redis\Client;
use HarmonyIO\Cache\Provider\Redis;
use HarmonyIO\HttpClient\Client\ArtaxClient;
use HarmonyIO\HttpClient\Message\Request;
use function Amp\wait;

// create instance of the HTTP client
$httpClient = new ArtaxClient(
    new DefaultClient(),
    new Redis(new Client('tcp://127.0.0.1:6379'))
);

// create a new request to be cached for 10 seconds
$request = new Request('https://httpbin.org/get');

// make the request (the results will NOT be cache)
$result = wait($httpClient->request($request));

// make the same request again
$result = wait($httpClient->request($request));
```

## Client interface

The HTTP client's interface only contains a single method: `Client::request(\HarmonyIO\HttpClient\Message\Request $request)`.

The `$request` parameter can be either a "normal" non-caching request (`HarmonyIO\HttpClient\Message\Request`) or a caching request (`HarmonyIO\HttpClient\Message\CachingRequest`).

### `HarmonyIO\HttpClient\Message\Request`

The constructor of the request class expects at least a URL to make the request to and optionally an HTTP method (defaults to GET).

Optional request parts can be set in setter method of the request class:

```php
<?php declare(strict_types=1);

namespace Foo;

use HarmonyIO\HttpClient\Message\Request;

$request = (new Request('https://httpbin.org/post', 'POST'))
    ->setProtocolVersions('1.1', '2.0')
    ->addHeader('foo', 'bar')
    ->addHeader('baz', 'qux')
    ->setBody('foobar')
;
```

### `HarmonyIO\HttpClient\Message\CachingRequest`

The constructor of the caching request class expects at least a key, a TTL, the URL to make the request to and optionally an HTTP method (defaults to GET).

Optional request parts can be set in setter method of the request class as defined in the previous section.

```php
<?php declare(strict_types=1);

namespace Foo;

use HarmonyIO\Cache\Ttl;
use HarmonyIO\HttpClient\Message\CachingRequest;

$request = (new CachingRequest('UniqueCachingKey', new Ttl(Ttl::ONE_HOUR), 'https://httpbin.org/get'))
    ->setProtocolVersions('1.1', '2.0')
    ->addHeader('foo', 'bar')
    ->addHeader('baz', 'qux')
;
```
