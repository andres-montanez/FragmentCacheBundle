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
 */
class FragmentCache extends ConfigurationAnnotation
{
	protected $expiration;

    protected $version;

    protected $options;

    public function getExpiration()
    {
    	return (integer) $this->expiration * 60;
    }

    public function setExpiration($expiration)
    {
    	if (!is_numeric($expiration)) {
    		$expiration = 1;
    	}

    	$this->expiration = $expiration;
    }

    public function getVersion()
    {
    	return (integer) $this->version;
    }

    public function setVersion($version)
    {
    	if (!is_numeric($version)) {
    		$version = 1;
    	}

    	$this->version = $version;
    }

    public function getOptions()
    {
    	return $this->options;
    }

    public function setOptions($options)
    {
    	if (!is_array($options)) {
    		$options = array();
    	}
    	$this->options = $options;
    }

    public function getAliasName()
    {
        return 'andres_montanez_fragment_cache';
    }

    public function allowArray()
    {
        return false;
    }
}
