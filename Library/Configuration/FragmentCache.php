<?php
/*
* This file is part of the FragmentCache bundle.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace AndresMontanez\FragmentCacheBundle\Library\Configuration;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;

/**
 * @Annotation
 * Annotation for Configuration of the Fragment Cache
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class FragmentCache extends ConfigurationAnnotation
{
	/**
	 * Expiration time in minutes
	 *
	 * @var integer
	 */
	protected $expiration;

	/**
	 * Version of the Fragment
	 * @var itneger
	 */
    protected $version;

    /**
     * Custom Options
     *
     * @var array
     */
    protected $options;

    /**
     * Get the Expiration, returned in minutes
     * @return integer
     */
    public function getExpiration()
    {
    	return (integer) $this->expiration;
    }

    /**
     * Sets the Expiration, in minutes
     * @param string $expiration
     * @return \AndresMontanez\FragmentCacheBundle\Library\Configuration\FragmentCache
     */
    public function setExpiration($expiration)
    {
    	if (!is_numeric($expiration)) {
    		$expiration = 1;
    	}

    	$this->expiration = $expiration;
    	return $this;
    }

    /**
     * Get the Version
     * @return integer
     */
    public function getVersion()
    {
    	return (integer) $this->version;
    }

    /**
     * Sets the Version
     * @param integer $version
     * @return \AndresMontanez\FragmentCacheBundle\Library\Configuration\FragmentCache
     */
    public function setVersion($version)
    {
    	if (!is_numeric($version)) {
    		$version = 1;
    	}

    	$this->version = $version;
    	return $this;
    }

    /**
     * Get the Custom Options
     * @return array
     */
    public function getOptions()
    {
    	return $this->options;
    }

    /**
     * Sets the Custom Options
     * @param array $options
     * @return \AndresMontanez\FragmentCacheBundle\Library\Configuration\FragmentCache
     */
    public function setOptions($options)
    {
    	if (!is_array($options)) {
    		$options = array();
    	}

    	$this->options = $options;
    	return $this;
    }

    /**
     * Get the Alias
     * @return string
     */
    public function getAliasName()
    {
        return 'andres_montanez_fragment_cache';
    }

    /**
     * Indicates if allow arrays
     * @return boolean
     */
    public function allowArray()
    {
        return false;
    }
}
