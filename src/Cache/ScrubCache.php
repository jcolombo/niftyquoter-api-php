<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Cache;

class ScrubCache
{
    /**
     * Invalidate cache entries after a mutation (create/update/delete).
     *
     * Clears the entire cache — simplest approach for reliable invalidation.
     * With only 10 resources and file-based cache, the performance impact is
     * negligible compared to the risk of stale data from incomplete pattern matching.
     *
     * If custom backends need more granular invalidation, users can implement
     * their own registerCacheMethods() that handle key-specific clearing.
     */
    public static function invalidate(string $resourceUrl): void
    {
        if (!Cache::isEnabled()) {
            return;
        }

        // Clear all cache — simplest approach for reliable invalidation.
        // A more granular approach would pattern-match, but given the SDK's
        // scale (10 resources), clearing all is acceptable and avoids edge cases.
        Cache::clear();
    }

    /**
     * Extract resource type from URL.
     * E.g., 'proposals/42/comments' → 'comments', 'clients/5' → 'clients'.
     * Takes the last path segment that isn't a numeric ID.
     */
    private static function buildPattern(string $resourceUrl): string
    {
        $segments = explode('/', trim($resourceUrl, '/'));
        $segments = array_reverse($segments);
        foreach ($segments as $segment) {
            if ($segment !== '' && !ctype_digit($segment)) {
                return $segment;
            }
        }
        return $resourceUrl;
    }
}
