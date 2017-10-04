<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformHttpCacheBundle\EventSubscriber;

use EzSystems\PlatformHttpCacheBundle\ResponseConfigurator\ResponseCacheConfigurator;
use EzSystems\PlatformHttpCacheBundle\ResponseTagger\ResponseTagger;
use eZ\Publish\Core\MVC\Symfony\View\CachableView;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Configures the Response HTTP cache properties.
 */
class HttpCacheResponseSubscriber implements EventSubscriberInterface
{
    /**
     * @var \EzSystems\PlatformHttpCacheBundle\ResponseTagger\ResponseTagger
     */
    private $dispatcherTagger;

    /**
     * @var \EzSystems\PlatformHttpCacheBundle\ResponseConfigurator\ResponseCacheConfigurator
     */
    private $responseConfigurator;

    public function __construct(ResponseCacheConfigurator $responseConfigurator, ResponseTagger $dispatcherTagger)
    {
        $this->responseConfigurator = $responseConfigurator;
        $this->dispatcherTagger = $dispatcherTagger;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::RESPONSE => 'configureCache'];
    }

    public function configureCache(FilterResponseEvent $event)
    {
        $response = $event->getResponse();

        $view = $event->getRequest()->attributes->get('view');
        if ($view instanceof CachableView && !$view->isCacheEnabled()) {
            return ;
        }

        $this->responseConfigurator->enableCache($response);
        $this->responseConfigurator->setSharedMaxAge($response);
        if ($view instanceof CachableView) {
          $this->dispatcherTagger->tag($this->responseConfigurator, $response, $view);
        }
    }
}
