<?php
/*
* This file is part of the FragmentCache bundle.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace AndresMontanez\FragmentCacheBundle\EventListener;

use AndresMontanez\FragmentCacheBundle\Library\CacheServiceInterface;
use AndresMontanez\FragmentCacheBundle\Library\Configuration\FragmentCache;
use AndresMontanez\FragmentCacheBundle\Event\KeyGenerationEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Event Listener for the Fragment Cache
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class FragmentCacheListener implements EventSubscriberInterface
{
	/**
	 * Event Dispatcher
	 *
	 * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
	 */
	protected $dispatcher;

	/**
	 * Current Environment
	 *
	 * @var string
	 */
	protected $environment;

	/**
	 * Indicates if the environment has debugging enabled or not
	 *
	 * @var boolean
	 */
	protected $debug;

	/**
	 * Cache Service for saving the fragments
	 *
	 * @var \AndresMontanez\FragmentCacheBundle\Library\CacheServiceInterface
	 */
    protected $cache;

    /**
     * Indicates if Fragment Cache is enabled
     *
     * @var boolean
     */
    protected $enalbed;

    /**
     * The Master Request
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $masterRequest;

    /**
     * The Constructor
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     * @param string $environment
     * @param boolean $debug
     * @param boolean $enabled
     * @param \AndresMontanez\FragmentCacheBundle\Library\CacheServiceInterface $cache
     */
    public function __construct(EventDispatcherInterface $dispatcher, $environment, $debug, $enabled, CacheServiceInterface $cache)
    {
    	$this->dispatcher = $dispatcher;
    	$this->environment = $environment;
    	$this->debug = $debug;
        $this->cache = $cache;
        $this->enabled = $enabled;
        $this->masterRequest = Request::createFromGlobals();
    }

    /**
     * Listener for the Controller call
     *
     * @param \Symfony\Component\HttpKernel\Event\FilterControllerEvent $event
     * @return void
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        // Only for SubRequests
        $subRequest = $event->getRequest();
        if ($event->getRequestType() == HttpKernelInterface::MASTER_REQUEST) {
        	return;
        }

        // Check if Annotation is present
        if (!$configuration = $subRequest->attributes->get('_andres_montanez_fragment_cache', false)) {
            return;
        }

        // Calculate Request Key
        $key = $this->getKey($configuration, $subRequest, $this->masterRequest);

        if (!$this->enabled) {
        	return;
        }

        // If content is cached, return it
        $content = $this->cache->get($key);
        if ($content !== false) {
        	$fragment = '';
        	if ($this->debug) {
        		$fragment .= '<!-- HIT - Begin Fragment Cache for KEY: ' . $key .' -->';
        	}

        	$fragment .= $content;

        	if ($this->debug) {
        		$fragment .= '<!-- End Fragment Cache for KEY: ' . $key .' -->';
        	}

    	    $response = new Response($fragment);
	        $event->setController(function() use ($response) {
                return $response;
            });

        } else {
    	    $subRequest->attributes->set('_andres_montanez_fragment_cache_key', $key);
        }

        return;
    }

    /**
     * Listener for the Response call
     *
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
     * @return void
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
    	// Only for Sub Requests
        $subRequest = $event->getRequest();
        if ($event->getRequestType() == HttpKernelInterface::MASTER_REQUEST) {
        	return;
        }

        // Check if Annotation is present
        if (!$configuration = $subRequest->attributes->get('_andres_montanez_fragment_cache', false)) {
        	return;
        }

        // If Key is present, save content for that key, expiration saved in seconds retreived in minutes
        if (($key = $subRequest->attributes->get('_andres_montanez_fragment_cache_key', false)) !== false) {
            if ($this->enabled) {
                $this->cache->set(
                    $key,
                    $event->getResponse()->getContent(),
                    $configuration->getExpiration() * 60
                );

                $fragment = '';
                if ($this->debug) {
                	$fragment .= '<!-- MISS - Begin Fragment Cache for KEY: ' . $key . ' -->';
                }

                $fragment .= $event->getResponse()->getContent();

                if ($this->debug) {
                    $fragment .= '<!-- End Fragment Cache for KEY: ' . $key . ' -->';
                }

                $event->getResponse()->setContent($fragment);

        	} else if (!$this->enabled) {
        		$fragment = '';
        		if ($this->debug) {
        			$fragment .= '<!-- DISABLED - Begin Fragment Cache for KEY: ' . $key . ' -->';
        		}

        		$fragment .= $event->getResponse()->getContent();

        		if ($this->debug) {
        			$fragment .= '<!-- End Fragment Cache for KEY: ' . $key . ' -->';
        		}

                $event->getResponse()->setContent($fragment);
        	}
        }

        return;
    }

    /**
     * Calculates the cache Key of the Fragment
     *
	 * @param \AndresMontanez\FragmentCacheBundle\Library\Configuration\FragmentCache $configuration
	 * @param \Symfony\Component\HttpFoundation\Request $subRequest
	 * @param \Symfony\Component\HttpFoundation\Request $masterRequest
     * @return string
     */
    protected function getKey(FragmentCache $configuration, Request $subRequest, Request $masterRequest)
    {
    	$event = new KeyGenerationEvent($configuration, $subRequest, $masterRequest);

        $keyRequest = sha1($subRequest->getRequestUri())
                    . ':'
                    . sha1($masterRequest->getRequestUri());

        $key = array(
            'AndresMontanezFragmentCache',
    		$this->environment,
            'v' . $configuration->getVersion(),
            $keyRequest
        );

        $this->dispatcher->dispatch(KeyGenerationEvent::NAME, $event);
        if (count($event->getKeys()) > 0) {
        	$key = array_merge($key, $event->getKeys());
        }

        $key = implode(':', $key);
        return $key;
    }

    /**
     * Get the Subscribed Events
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
    	return array(
			KernelEvents::CONTROLLER => array('onKernelController', -128),
			KernelEvents::RESPONSE => 'onKernelResponse',
    	);
    }
}
