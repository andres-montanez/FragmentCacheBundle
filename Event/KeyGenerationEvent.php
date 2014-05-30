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

class KeyGenerationEvent extends Event
{
	const NAME = 'fragment_cache.key_generation';

	protected $configuration;
	protected $subRequest;
	protected $masterRequest;
	protected $keys;

	public function __construct(FragmentCache $configuration, Request $subRequest, Request $masterRequest)
	{
		$this->configuration = $configuration;
		$this->subRequest = $subRequest;
		$this->masterRequest = $masterRequest;
		$this->keys = [];
	}

	public function getConfigurarion()
	{
		return $this->configuration;
	}

	public function getSubRequest()
	{
		return $this->subRequest;
	}

	public function getMasterRequest()
	{
		return $this->masterRequest;
	}

	public function addKey($key)
	{
		$this->keys[] = sha1($key);
	}

	public function getKeys()
	{
		return $this->keys;
	}
}