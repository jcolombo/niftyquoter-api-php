<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Utility;

enum HttpMethod: string
{
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
}
