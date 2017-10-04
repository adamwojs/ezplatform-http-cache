<?php

namespace EzSystems\PlatformHttpCacheBundle\ResponseConfigurator;

use Symfony\Component\HttpFoundation\Response;

abstract class ChainResponseCacheConfigurator implements ResponseCacheConfigurator
{
    /**
     * @var \EzSystems\PlatformHttpCacheBundle\ResponseConfigurator\ResponseCacheConfigurator
     */
    private $successor = null;

    public function enableCache(Response $response)
    {
        if ($this->supports($response)) {
            if ($this->getCacheConfiguration()->enableViewCache) {
                $response->setPublic();
            }
        } else if ($this->successor) {
            return $this->successor->enableCache($response);
        }

        return $this;
    }

    public function setSharedMaxAge(Response $response)
    {
        if ($this->supports($response)) {
            if ($this->getCacheConfiguration()->enableTtlCache) {
                $response->setSharedMaxAge($this->getCacheConfiguration()->sharedMaxAge);
            }
        }
        else if ($this->successor) {
            return $this->successor->setSharedMaxAge($response);
        }

        return $this;
    }

    public function addTags(Response $response, $tags)
    {
        if ($this->supports($response)) {
            if ($this->getCacheConfiguration()->enableViewCache) {
                $response->headers->set('xkey', $tags, false);
            }
        }
        else if ($this->successor) {
            return $this->successor->addTags($response, $tags);
        }

        return $this;
    }

    /**
     * Get next configured link in chain
     *
     * @param ResponseCacheConfigurator $successor
     * @return void
     */
    public function setSuccessor(ResponseCacheConfigurator $successor)
    {
        $this->successor = $successor;
    }

    /**
     * Returns the Http Cache configuration.
     *
     * @return HttpCacheConfiguration
     */
    protected abstract function getCacheConfiguration();

    /**
     * Checks if the configurator supports the given response.
     *
     * @param Response $response
     * @return true if this Configurator can process the class
     */
    protected abstract function supports(Response $response);
}
