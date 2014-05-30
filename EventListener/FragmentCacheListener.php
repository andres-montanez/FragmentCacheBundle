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

class FragmentCacheListener implements EventSubscriberInterface
{
	protected $dispatcher;
	protected $environment;
	protected $debug;
    protected $cache;
    protected $enalbed;
    protected $masterRequest;

    public function __construct(EventDispatcherInterface $dispatcher, $environment, $debug, $enabled, CacheServiceInterface $cache)
    {
    	$this->dispatcher = $dispatcher;
    	$this->environment = $environment;
    	$this->debug = $debug;
        $this->cache = $cache;
        $this->enabled = $enabled;
        $this->masterRequest = Request::createFromGlobals();
    }

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
    }

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

        // If Key is present, save content for that key
        if (($key = $subRequest->attributes->get('_andres_montanez_fragment_cache_key', false)) !== false) {
            if ($this->enabled) {
                $this->cache->set(
                    $key,
                    $event->getResponse()->getContent(),
                    $configuration->getExpiration()
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
    }

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

    public static function getSubscribedEvents()
    {
    	return array(
			KernelEvents::CONTROLLER => array('onKernelController', -128),
			KernelEvents::RESPONSE => 'onKernelResponse',
    	);
    }
}
