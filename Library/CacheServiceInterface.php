<?php
/*
* This file is part of the FragmentCache bundle.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace AndresMontanez\FragmentCacheBundle\Library;

/**
 * Interface for a Generic Caching Service
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
interface CacheServiceInterface
{
    /**
     * Gets a Cached Value by it's Key
     *
     * @param string $key
     * @return mixed
     */
    public function get($key);

    /**
     * Stores a Value on the Cache with a Key
     *
     * @param string $key
     * @param mixed $value
     * @param integer $expiration
     * @return boolean
     */
    public function set($key, $value, $expiration = 0);

}