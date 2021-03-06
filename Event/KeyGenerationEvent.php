<?php
/*
* This file is part of the FragmentCache bundle.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace AndresMontanez\FragmentCacheBundle\Event;

use AndresMontanez\FragmentCacheBundle\Library\Configuration\FragmentCache;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

/**
 * Event sent when the Key for the Cache is being generated
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class KeyGenerationEvent extends Event
{
    /**
     * Name of the event
     * @var string
     */
    const NAME = 'fragment_cache.key_generation';

    /**
     * The Configuration of the Fragment Cache
     * @var \AndresMontanez\FragmentCacheBundle\Library\Configuration\FragmentCache
     */
    protected $configuration;

    /**
     * The Sub Request being cached
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $subRequest;

    /**
     * The Master Request that originated the Sub Request
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $masterRequest;

    /**
     * Collection of Keys generated by the event dispatcher
     * @var array
     */
    protected $keys;

    /**
     * Constructor
     *
     * @param \AndresMontanez\FragmentCacheBundle\Library\Configuration\FragmentCache $configuration
     * @param \Symfony\Component\HttpFoundation\Request $subRequest
     * @param \Symfony\Component\HttpFoundation\Request $masterRequest
     */
    public function __construct(FragmentCache $configuration, Request $subRequest, Request $masterRequest)
    {
        $this->configuration = $configuration;
        $this->subRequest = $subRequest;
        $this->masterRequest = $masterRequest;
        $this->keys = array();
    }

    /**
     * Get the Fragment Configuration
     * @return \AndresMontanez\FragmentCacheBundle\Library\Configuration\FragmentCache
     */
    public function getConfigurarion()
    {
        return $this->configuration;
    }

    /**
     * Get the Sub Request
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getSubRequest()
    {
        return $this->subRequest;
    }

    /**
     * Get the Master Request
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getMasterRequest()
    {
        return $this->masterRequest;
    }

    /**
     * Adds a Key part
     * @param string $key
     * @return \AndresMontanez\FragmentCacheBundle\Event\KeyGenerationEvent
     */
    public function addKey($key)
    {
        $this->keys[] = sha1($key);
        return $this->keys;
    }

    /**
     * Get the Key Parts
     * @return array
     */
    public function getKeys()
    {
        return $this->keys;
    }
}