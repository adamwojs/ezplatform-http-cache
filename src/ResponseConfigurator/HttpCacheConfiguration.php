<?php

namespace EzSystems\PlatformHttpCacheBundle\ResponseConfigurator;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Value object representing Http Cache configuration
 */
class HttpCacheConfiguration extends ValueObject
{
    /**
     * @var bool
     */
    public $enableViewCache;

    /**
     * @var bool
     */
    public $enableTtlCache;

    /**
     * @var boolean
     */
    public $override;

    /**
     * @var int
     */
    public $sharedMaxAge;

    /**
     * @var string
     */
    public $etag;

    // TODO: Support for other cache control directives
}
