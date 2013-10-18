<?php // $Id$
namespace easyconfig;

use lib\common\Collection as CommonCollection;

/**
 * A collection of configs
 *
 * @author lucas
 */
class Collection extends CommonCollection implements i\Config
{
    protected $___path;
    protected $___name;
    protected $___cache = array();

    const CACHE_KEY_WEIGHTED        = 'weighted';
    const CACHE_KEY_UNWEIGHTED      = 'unweighted';
    const CACHE_KEY_ROLE_WEIGHTED   = 'role_weighted';
    const CACHE_KEY_ROLE_UNWEIGHTED = 'role_unweighted';
    const CACHE_KEY_FILTERED        = 'filtered';
    const CACHE_KEY_ROLE            = 'role';


    public function setName($name)
    {
        return $this->___name = $name;
    }

    // Collection methods
    public function name()
    {
        return $this->___name;
    }

    public function setPath($path)
    {
        return $this->___path = $path;
    }

    public function appendPath($path)
    {
        return $this->setPath($this->___path . $path);
    }

    public function path()
    {
        return $this->___path;
    }

    /**
     * Gets a random config from the collection
     *
     * @param boolean $weighted take config weights into account when doing the randomization
     * @return Config|null a config if there is one available, else null
     */
    public function getRandomConfig($weighted = false)
    {
        $key = $weighted ? self::CACHE_KEY_WEIGHTED : self::CACHE_KEY_UNWEIGHTED;
        if (empty($this->___cache[$key]))
        {
            $config_list = $this->getFilteredConfigs();
            if ($weighted)
                $config_list = $this->buildWeightedConfigList($config_list);

            $this->___cache[$key] = $config_list;
        }

        return $this->getRandomConfigFromList($this->___cache[$key]);
    }

    /**
     * Gets a random configuration that has the specified role
     *
     * @param $role string the role which the config to get must have
     * @param boolean $weighted take config weights into account when doing the randomization
     * @return Config|null a config if there is one with the specified role available, else null
     */
    public function getRandomConfigWithRole($role, $weighted = false)
    {
        $key = ( $weighted ? self::CACHE_KEY_ROLE_WEIGHTED : self::CACHE_KEY_ROLE_UNWEIGHTED ) . $role;
        if (empty($this->___cache[$key]))
        {
            $config_list = $this->getConfigsWithRole($role);
            if ($weighted)
                $config_list = $this->buildWeightedConfigList($config_list);

            $this->___cache[$key] = $config_list;
        }

        $config = $this->getRandomConfigFromList($this->___cache[$key]);

        if ($config !== null)
            return $config->useRole($role);
    }

    /**
     * Gets an array of configs that have the specified role
     *
     * @param $role string the role which the configs to get must have
     * @return array an array of configs that have the specified role
     */
    public function getConfigsWithRole($role)
    {
        $key = self::CACHE_KEY_ROLE.$role;
        if (empty($this->___cache[$key]))
        {
            $configs = $this->getFilteredConfigs();

            $role_configs = array();

            foreach ($configs as $config)
            {
                if ($config instanceof i\HasRoles && isset($config->getRoles()->$role) && $config->getRoles()->$role !== 0)
                    $role_configs[] = $config->useRole($role);
            }

            $this->___cache[$key] = $role_configs;
        }

        return $this->___cache[$key];
    }

    /**
     * Removes a config from the collection - NOTE: rekeys the collection
     *
     * @param Config|integer $item the config object or index of the config to remove
     */
    public function removeConfig($item)
    {
        if (is_object($item))
        {
            foreach($this->_rows as $key=>$value)
                if ($value === $item)
                    unset($this->_rows[$key]);
        }
        else
        {
            unset($this->_rows[$item]);
        }

        $this->___cache = array();

        $this->rekey();
    }

    /**
     * Magic setter method
     */
    public function __set($key, $value)
    {
        if (is_int($key))
            $this->_rows[$key] = $value;
        else if ($key == '_rows')
            if (is_array($value))
                $this->_rows = $value;
            else
                throw new e\Exception('_rows must be an array');
    }

    /**
     * Magic method for cloning a collection object
     */
    public function __clone()
    {
        foreach($this->_rows as $key=>$val)
            if (is_object($val))
                $this->_rows[$key] = clone $val;
            else
                $this->_rows[$key] = $val;
    }

    /**
     * Converts this colleciton in to an array, optionally recursive
     *
     * @param boolean $recursive if true, recurse through all the collections and configs in this collection
     */
    public function getObjectArray($recursive=false)
    {
        if ($recursive)
        {
            $_arr = $this->toArray();
            $arr  = array();
            foreach ($_arr as $key => $val) {
                    $val       = is_object($val) ? $val->getObjectArray(true) : $val;
                    $arr[$key] = $val;
            }
            return $arr;
        }
        else
            return $this->toArray();
    }

    protected function getRandomConfigFromList($config_list, $weighted = false)
    {
        $count = count($config_list);
        if ($count == 0)
            return null;
        if ($count == 1)
            return $config_list[0];

        if ($weighted)
            $config_list = $this->buildWeightedConfigList($config_list);

        return $config_list[rand(0, $count - 1)];
    }

    public function getFilteredConfigs()
    {
        $key = self::CACHE_KEY_FILTERED;
        if (empty($this->___cache[$key]))
        {
            $configs = array();

            foreach ($this->_rows as $config)
            {
                if ($config instanceof i\Switchable && !$config->getState())
                    continue;

                $configs[] = $config;
            }

            $this->___cache[$key] = $configs;
        }

        return $this->___cache[$key];
    }

    public function rekey()
    {
        $new = array();

        foreach ($this->_rows as $key=>$val)
        {
            $new[] = $val;
        }
        $this->_rows = $new;
    }

    protected function buildWeightedConfigList($configs)
    {
        $config_list = array();
        foreach($configs as $config)
        {
            if ($config instanceof i\Weighted)
                $weight = $config->getWeight();
            else
                continue;

            if ($weight == 0)
                continue;

            for($i = 1; $i <= $weight; $i++)
                $config_list[] = $config;
        }

        return $config_list;
    }

    /**
     * Used by the config system for fix the paths on the Collection object
     * DO NOT USE UNLESS YOU REALLY KNOW WHAT YOU ARE DOING!!!
     */
    public function fixPaths($context = '', $name = '', $override_path = '')
    {
        Config::fixConfigPaths($this, $context, $name, $override_path);
    }
}

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 smarttab: */
