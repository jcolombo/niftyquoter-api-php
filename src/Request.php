<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp;

use Jcolombo\NiftyquoterApiPhp\Entity\EntityMap;
use Jcolombo\NiftyquoterApiPhp\Utility\Converter;
use Jcolombo\NiftyquoterApiPhp\Utility\HttpMethod;
use Jcolombo\NiftyquoterApiPhp\Utility\RequestAbstraction;
use Jcolombo\NiftyquoterApiPhp\Utility\RequestCondition;
use Jcolombo\NiftyquoterApiPhp\Utility\RequestResponse;
use Jcolombo\NiftyquoterApiPhp\Utility\Error;
use Jcolombo\NiftyquoterApiPhp\Utility\ErrorSeverity;

/**
 * Static CRUD builder — constructs RequestAbstraction objects and delegates execution to the connection.
 *
 * @override OVERRIDE-001 WHERE compilation uses individual query parameters
 */
class Request
{
    /**
     * GET single resource by ID.
     */
    public static function fetch(
        ?NiftyQuoter $connection,
        string $objectKey,
        int|string $id,
        array $fields = [],
        array $include = []
    ): RequestResponse {
        $url = self::buildUrl($objectKey) . '/' . $id;

        $request = new RequestAbstraction(
            method: HttpMethod::GET,
            resourceUrl: $url,
            include: $include,
        );

        return $connection->execute($request);
    }

    /**
     * GET list with pagination and filters.
     */
    public static function list(
        ?NiftyQuoter $connection,
        string $objectKey,
        array $fields = [],
        array $where = [],
        array $include = [],
        ?int $page = null,
        ?int $pageSize = null,
        ?string $parentPath = null
    ): RequestResponse {
        $url = self::buildUrl($objectKey, $parentPath);

        $whereParams = self::compileWhereParameters($where);

        $request = new RequestAbstraction(
            method: HttpMethod::GET,
            resourceUrl: $url,
            include: $include,
            where: $whereParams,
            page: $page,
            pageSize: $pageSize,
        );

        return $connection->execute($request);
    }

    /**
     * POST new resource.
     */
    public static function create(
        ?NiftyQuoter $connection,
        string $objectKey,
        array $data,
        ?string $parentPath = null
    ): RequestResponse {
        $url = self::buildUrl($objectKey, $parentPath);

        $request = new RequestAbstraction(
            method: HttpMethod::POST,
            resourceUrl: $url,
            data: $data,
        );

        return $connection->execute($request);
    }

    /**
     * PUT update resource.
     */
    public static function update(
        ?NiftyQuoter $connection,
        string $objectKey,
        int|string $id,
        array $data
    ): RequestResponse {
        $url = self::buildUrl($objectKey) . '/' . $id;

        $request = new RequestAbstraction(
            method: HttpMethod::PUT,
            resourceUrl: $url,
            data: $data,
        );

        return $connection->execute($request);
    }

    /**
     * DELETE resource.
     */
    public static function delete(
        ?NiftyQuoter $connection,
        string $objectKey,
        int|string $id
    ): RequestResponse {
        $url = self::buildUrl($objectKey) . '/' . $id;

        $request = new RequestAbstraction(
            method: HttpMethod::DELETE,
            resourceUrl: $url,
        );

        return $connection->execute($request);
    }

    /**
     * Execute a custom request (for send_email, clone, etc.).
     */
    public static function custom(
        ?NiftyQuoter $connection,
        HttpMethod $method,
        string $url,
        ?array $data = null
    ): RequestResponse {
        $request = new RequestAbstraction(
            method: $method,
            resourceUrl: $url,
            data: $data,
        );

        return $connection->execute($request);
    }

    /**
     * Build URL path from objectKey and optional parent path.
     */
    private static function buildUrl(string $objectKey, ?string $parentPath = null): string
    {
        // objectKey format: 'clients' or 'blocks:block'
        $parts = explode(':', $objectKey, 2);
        $path = $parts[0];

        if ($parentPath !== null) {
            return $parentPath . '/' . $path;
        }
        return $path;
    }

    /**
     * Map RequestCondition[] to individual query parameters.
     *
     * @override OVERRIDE-001 Uses individual named query params
     *
     * @param RequestCondition[] $conditions
     * @return array<string, string>
     */
    private static function compileWhereParameters(array $conditions): array
    {
        $params = [];
        foreach ($conditions as $condition) {
            if ($condition->type !== 'where') {
                continue;
            }
            $value = $condition->dataType !== null
                ? Converter::convertForFilter($condition->value, $condition->dataType)
                : (string) $condition->value;
            $params[$condition->prop] = $value;
        }
        return $params;
    }

    /**
     * Extract data from response body using wrapper key.
     */
    private static function extractResponseData(array $body, string $key, ?string $alternateKey = null): mixed
    {
        if (isset($body[$key])) {
            return $body[$key];
        }
        if ($alternateKey !== null && isset($body[$alternateKey])) {
            return $body[$alternateKey];
        }

        Error::handle(
            ErrorSeverity::WARN,
            "Response wrapper key '{$key}' not found in response body"
        );
        return $body;
    }
}
