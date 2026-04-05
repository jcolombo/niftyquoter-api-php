<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp;

use Jcolombo\NiftyquoterApiPhp\Utility\RequestAbstraction;
use Jcolombo\NiftyquoterApiPhp\Utility\RequestResponse;
use Jcolombo\NiftyquoterApiPhp\Utility\HttpMethod;
use Jcolombo\NiftyquoterApiPhp\Utility\RateLimiter;
use Jcolombo\NiftyquoterApiPhp\Utility\Error;
use Jcolombo\NiftyquoterApiPhp\Utility\ErrorSeverity;
use Jcolombo\NiftyquoterApiPhp\Utility\Log;

class NiftyQuoter
{
    /** @var array<string, self> */
    private static array $connections = [];

    private string $email;

    private string $rawApiKey;

    /** Connection key: "email::apiKey" */
    private string $apiKey;

    private string $connectionUrl;

    private string $connectionName;

    private bool $useCache;

    private bool $useLogging;

    private ?\GuzzleHttp\Client $client = null;

    private function __construct() {}

    /**
     * Singleton factory. Returns existing instance for same credentials or creates new.
     *
     * @override OVERRIDE-002: Two explicit params, not Paymo's polymorphic string|array.
     */
    public static function connect(string $email, string $apiKey, ?string $url = null): self
    {
        $key = "{$email}::{$apiKey}";
        if (isset(self::$connections[$key])) {
            return self::$connections[$key];
        }

        $instance = new self();
        $instance->email = $email;
        $instance->rawApiKey = $apiKey;
        $instance->apiKey = $key;
        $instance->connectionUrl = $url ?? Configuration::get('connection.url');
        $last5 = substr($apiKey, -5);
        $rand6 = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $instance->connectionName = "NqApi-***{$last5}-#{$rand6}";
        $instance->useCache = defined('NQAPI_REQUEST_CACHE_PATH') && Configuration::get('enabled.cache');
        $instance->useLogging = Configuration::get('enabled.logging');

        Log::onlyIf(Configuration::get('log.connections', false))
            ->log("Connection created: {$instance->connectionName}", ['url' => $instance->connectionUrl]);

        self::$connections[$key] = $instance;
        return $instance;
    }

    public static function disconnect(?string $email = null, ?string $apiKey = null): void
    {
        if ($email === null && $apiKey === null) {
            self::$connections = [];
            return;
        }
        if ($email !== null && $apiKey !== null) {
            $key = "{$email}::{$apiKey}";
            unset(self::$connections[$key]);
        }
    }

    public function execute(RequestAbstraction $request): RequestResponse
    {
        $startTime = microtime(true);

        // 1. Cache check (GET only, if cache enabled)
        if ($request->method === HttpMethod::GET && $this->useCache) {
            $cacheKey = $request->makeCacheKey();
            $cached = \Jcolombo\NiftyquoterApiPhp\Cache\Cache::fetch($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        // 2. Rate limit wait
        RateLimiter::waitIfNeeded($this->apiKey);

        // 3. Execute Guzzle request
        try {
            $client = $this->getClient();
            $options = [];
            if ($request->data !== null) {
                $options['json'] = $request->data;
            }

            // Build query params from where conditions and pagination
            $query = $request->where;
            if ($request->page !== null) {
                $query['page'] = $request->page;
            }
            if (!empty($request->include)) {
                $query['include'] = implode(',', $request->include);
            }
            if (!empty($query)) {
                $options['query'] = $query;
            }

            $guzzleResponse = $client->request(
                $request->method->value,
                $request->resourceUrl,
                $options
            );

            $responseCode = $guzzleResponse->getStatusCode();
            $responseReason = $guzzleResponse->getReasonPhrase();
            $headers = $guzzleResponse->getHeaders();
            $rawBody = $guzzleResponse->getBody()->getContents();
            $body = !empty($rawBody) ? json_decode($rawBody, true) : null;
            $elapsed = microtime(true) - $startTime;

            // 4. Record successful request
            RateLimiter::recordRequest($this->apiKey);
            RateLimiter::resetRetry($this->apiKey);
            RateLimiter::updateFromHeaders($this->apiKey, $headers);

            $response = new RequestResponse(
                success: $responseCode >= 200 && $responseCode < 300,
                body: $body,
                headers: $headers,
                responseCode: $responseCode,
                responseReason: $responseReason,
                responseTime: $elapsed,
                request: $request,
            );

            // 5. Log request
            Log::onlyIf($this->useLogging && Configuration::get('log.requests', true))
                ->log("{$request->method->value} {$request->resourceUrl}", [
                    'status' => $responseCode,
                    'time' => round($elapsed, 3),
                ]);

            // 6. Cache store (GET only, if successful)
            if ($request->method === HttpMethod::GET && $this->useCache && $response->success) {
                \Jcolombo\NiftyquoterApiPhp\Cache\Cache::store($request->makeCacheKey(), $response);
            }

            // 7. Cache invalidation (mutations)
            if ($request->method !== HttpMethod::GET && $this->useCache) {
                \Jcolombo\NiftyquoterApiPhp\Cache\ScrubCache::invalidate($request->resourceUrl);
            }

            return $response;

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $guzzleResponse = $e->getResponse();
            $responseCode = $guzzleResponse->getStatusCode();
            $elapsed = microtime(true) - $startTime;

            // Handle 429 with retry
            if ($responseCode === 429 && RateLimiter::shouldRetry($this->apiKey)) {
                RateLimiter::waitForRetry($this->apiKey);
                return $this->execute($request);
            }

            $rawBody = $guzzleResponse->getBody()->getContents();
            $body = !empty($rawBody) ? json_decode($rawBody, true) : null;

            $response = new RequestResponse(
                success: false,
                body: $body,
                headers: $guzzleResponse->getHeaders(),
                responseCode: $responseCode,
                responseReason: $guzzleResponse->getReasonPhrase(),
                responseTime: $elapsed,
                request: $request,
            );

            Error::handleApiError($response);
            return $response;

        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $guzzleResponse = $e->getResponse();
            $elapsed = microtime(true) - $startTime;
            $rawBody = $guzzleResponse->getBody()->getContents();
            $body = !empty($rawBody) ? json_decode($rawBody, true) : null;

            $response = new RequestResponse(
                success: false,
                body: $body,
                headers: $guzzleResponse->getHeaders(),
                responseCode: $guzzleResponse->getStatusCode(),
                responseReason: $guzzleResponse->getReasonPhrase(),
                responseTime: $elapsed,
                request: $request,
            );

            Error::handleApiError($response);
            return $response;

        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            $elapsed = microtime(true) - $startTime;

            $response = new RequestResponse(
                success: false,
                body: null,
                headers: [],
                responseCode: 0,
                responseReason: $e->getMessage(),
                responseTime: $elapsed,
                request: $request,
            );

            Error::handle(ErrorSeverity::FATAL, 'Connection failed: ' . $e->getMessage());
            return $response;
        }
    }

    public function getName(): string
    {
        return $this->connectionName;
    }

    public function getUrl(): string
    {
        return $this->connectionUrl;
    }

    private function getClient(): \GuzzleHttp\Client
    {
        if ($this->client === null) {
            $this->client = new \GuzzleHttp\Client([
                'base_uri' => $this->connectionUrl,
                'timeout' => Configuration::get('connection.timeout', 30),
                'verify' => Configuration::get('connection.verify', true),
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode("{$this->email}:{$this->rawApiKey}"),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'http_errors' => true,
            ]);
        }
        return $this->client;
    }
}
