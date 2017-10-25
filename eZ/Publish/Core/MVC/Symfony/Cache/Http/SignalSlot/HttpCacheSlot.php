<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot;

use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger;
use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Slot;

/**
 * A abstract legacy slot covering common functions needed for legacy slots.
 */
abstract class HttpCacheSlot extends Slot
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger
     */
    protected $httpCacheClearer;

    /**
     * @var \eZ\Publish\API\Repository\Repository|\eZ\Publish\Core\Repository\Repository
     */
    protected $repository;

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger $httpCacheClearer
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function __construct(GatewayCachePurger $httpCacheClearer, RepositoryInterface $repository)
    {
        $this->httpCacheClearer = $httpCacheClearer;
        $this->repository = $repository;
    }

    public function receive(Signal $signal)
    {
        if (!$this->supports($signal)) {
            return;
        }

        // Use sudo as cache clearing should happen regardless of user permissions.
        $this->repository->sudo(
            function () use ($signal) {
                $this->purgeHttpCache($signal);
            }
        );
    }

    /**
     * Checks if $signal is supported by this handler.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     *
     * @return bool
     */
    abstract protected function supports(Signal $signal);

    /**
     * Purges the HTTP cache for $signal.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     *
     * @return mixed
     */
    abstract protected function purgeHttpCache(Signal $signal);
}
