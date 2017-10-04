# View cache configuration 

> Status: DRAFT
> Author: adam.wojs@ez.no

## Summary

This feature covers ability to configure http cache based on various response criteria e.g status code, related content type.

## Response Cache Configurator

In `ezplatform-http-cache` the `ResponseCacheConfigurator` is responsible for set proper response HTTP cache headers  

```php
/**
 * Configures caching options of an HTTP Response.
 */
 <?php
 
interface ResponseCacheConfigurator
{
    /**
     * Enables cache on a Response.
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return ResponseCacheConfigurator
     */
    public function enableCache(Response $response);

    /**
     * Sets the shared-max-age property of a Response if it is not already set.
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return ResponseCacheConfigurator
     */
    public function setSharedMaxAge(Response $response);

    /**
     * Adds $tags to the response's cache tags header.
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param string|array $tags Single tag, or array of tags
     * @return ResponseCacheConfigurator
     */
    public function addTags(Response $response, $tags);
}
```

### ConfigurableResponseCacheConfigurator

`ConfigurableResponseCacheConfigurator` is basic implementation which applies the cache control directives based on 
configured values.  

### ChainResponseCacheConfigurator

```ChainResponseCacheConfigurator``` is base class for chained response cache configurators: 

```php
<?php 

abstract class ChainResponseCacheConfigurator implements ResponseCacheConfigurator {
    // ... 
    
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
```

Configurators are linked into [chain of responsibility](https://en.wikipedia.org/wiki/Chain-of-responsibility_pattern) using `setSuccessor` method. 

The `supports` method allows for conditional apply of  the cache configuration returned by `getCacheConfiguration` method.

Example services configuration: 

```yaml
ezplatform.view_cache.default_response_configurator:
    class: EzSystems\PlatformHttpCacheBundle\ResponseConfigurator\ConfigurableResponseCacheConfigurator
    arguments:
        - '$content.view_cache$'
        - '$content.ttl_cache$'
        - '$content.default_ttl$'

ezplatform.view_cache.error_response_configurator:
    class: EzSystems\PlatformHttpCacheBundle\ResponseConfigurator\ExpressionResponseCacheConfigurator
    arguments:
        - 'response.getStatusCode() >= 400'
        - '@ezplatform.view_cache.error_configuration'
    calls:
        - ['setSuccessor', ['@ezplatform.view_cache.default_response_configurator']]

ezplatform.view_cache.response_configurator:
    alias: ezplatform.view_cache.error_response_configurator
```

### ExpressionResponseCacheConfigurator

```ExpressionResponseCacheConfigurator``` applies cache control directives only if provided expression is true. 


To evaluate expression the [Expression Language Component](http://symfony.com/doc/current/components/expression_language.html) is used. In scope of expression the are available the fallowing values:
 
| Value    | Type                                       | Description        |
|----------|--------------------------------------------|--------------------|
| response | \Symfony\Component\HttpFoundation\Response | Processed response | 


Example expressions:

* Match only responses with 201 status code: 
  `response.getStatusCode() == 201` 
* Match client and server error responses: 
  `response.isClientError() or response.isServerError()`

## Semantic configuration

Example of semantic configuration (draft): 

```yaml
ezpublish:
    system:
        my_siteaccess:
            content:
                view_cache:
                    rules:
                        sensitive:
                            match: 'contentTypeId in [ "top_secrect" ] and locationId == 56'
                            apply:
                               override: true 
                               cache_control: 
                                   private: true
                                   max_age: 5
                        4XX_error:
                            match: 'response.isClientError()'
                            apply:
                               override: true 
                               cache_control: 
                                   public: true
                                   no_cache: true
                                   max_age: 15
                    default:
                        view_cache: true
                        ttl_cache: true 
                        default_ttl: 60 
```

## Open questions


* Semantic configuration should be exposed in `EzSystemsPlatformHttpCacheBundle` or `EzPublishCoreBundle` ?

