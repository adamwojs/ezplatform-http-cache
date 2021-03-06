<?php

/**
 * File containing the FOSPurgeClientTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformHttpCacheBundle\Tests\PurgeClient;

use EzSystems\PlatformHttpCacheBundle\PurgeClient\VarnishPurgeClient;
use FOS\HttpCache\ProxyClient\ProxyClientInterface;
use FOS\HttpCacheBundle\CacheManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class VarnishPurgeClientTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheManager;

    /**
     * @var VarnishPurgeClient
     */
    private $purgeClient;

    protected function setUp()
    {
        parent::setUp();
        $this->cacheManager = $this->getMockBuilder(CacheManager::class)
            ->setConstructorArgs(
                array(
                    $this->createMock(ProxyClientInterface::class),
                    $this->createMock(
                        UrlGeneratorInterface::class
                    ),
                )
            )
            ->getMock();
        $this->purgeClient = new VarnishPurgeClient($this->cacheManager);
    }

    public function testPurgeNoLocationIds()
    {
        $this->cacheManager
            ->expects($this->never())
            ->method('invalidate');
        $this->purgeClient->purge(array());
    }

    public function testPurgeOneLocationId()
    {
        $locationId = 123;
        $this->cacheManager
            ->expects($this->once())
            ->method('invalidatePath')
            ->with('/', ['key' => "location-$locationId", 'Host' => 'localhost']);

        $this->purgeClient->purge($locationId);
    }

    /**
     * @dataProvider purgeTestProvider
     */
    public function testPurge(array $locationIds)
    {
        foreach ($locationIds as $key => $locationId) {
            $this->cacheManager
                ->expects($this->at($key))
                ->method('invalidatePath')
                ->with('/', ['key' => "location-$locationId", 'Host' => 'localhost']);
        }

        $this->purgeClient->purge($locationIds);
    }

    public function purgeTestProvider()
    {
        return array(
            array(array(123)),
            array(array(123, 456)),
            array(array(123, 456, 789)),
        );
    }

    public function testPurgeAll()
    {
        $this->cacheManager
            ->expects($this->once())
            ->method('invalidate')
            ->with(array('key' => 'ez-all'));

        $this->purgeClient->purgeAll();
    }
}
