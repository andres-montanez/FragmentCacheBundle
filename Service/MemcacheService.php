<?php
/*
* This file is part of the FragmentCache bundle.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace AndresMontanez\FragmentCacheBundle\Service;

use AndresMontanez\FragmentCacheBundle\Library\CacheServiceInterface;
use Memcached;

/**
 * Cache Service implementation using Memcached library
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class MemcacheService extends Memcached implements CacheServiceInterface
{
}