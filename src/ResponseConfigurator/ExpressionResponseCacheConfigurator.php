<?php

namespace EzSystems\PlatformHttpCacheBundle\ResponseConfigurator;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\Response;

/**
 * ResponseCacheConfigurator applied only if provided expression is true
 */
class ExpressionResponseCacheConfigurator extends ChainResponseCacheConfigurator
{
    /**
     * @var string
     */
    protected $expression;

    /**
     * @var HttpCacheConfiguration $configuration
     */
    protected $configuration;

    /**
     * RuleResponseCacheConfigurator constructor.
     *
     * @param string $expression
     * @param HttpCacheConfiguration $configuration
     */
    public function __construct($expression, HttpCacheConfiguration $configuration)
    {
        $this->expression = $expression;
        $this->configuration = $configuration;
    }

    public function supports(Response $response)
    {
        $supports = (new ExpressionLanguage())->evaluate($this->expression, [
            'response' => $response
        ]);

        return $supports;
    }

    public function getCacheConfiguration()
    {
        return $this->configuration;
    }
}
