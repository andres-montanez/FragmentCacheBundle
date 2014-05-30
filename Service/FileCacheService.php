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

class FileCacheService implements CacheServiceInterface
{
	protected $cacheDir;

	public function __construct($cacheDir)
	{
		$this->cacheDir = $cacheDir . '/fragment_cache/';
		if (!file_exists($this->cacheDir)) {
			mkdir($this->cacheDir);
		}
	}

	public function get($key)
	{
        $file = $this->cacheDir . $this->getDir($key) . $this->getFile($key);
        if (file_exists($file) && is_readable($file)) {
        	if (filemtime($file) > time()) {
        		return file_get_contents($file);
        	}
        }

        return false;
	}

	public function set($key, $value, $expiration = 0)
	{
		$file = $this->cacheDir . $this->getDir($key) . $this->getFile($key);

		if (!file_exists(dirname($file))) {
			mkdir(dirname($file), 0755, true);
		}

		$return = file_put_contents($file, $value);
		touch($file, time() + $expiration);
		return $return;
	}

	protected function getFile($key)
	{
		$file = sha1($key) . '.html';

		return $file;
	}

	protected function getDir($key)
	{
		$dir = sha1($key);
		$dir = substr($dir, 0, 3) . '/'
	         . substr($dir, 3, 3) . '/';

		return $dir;
	}
}