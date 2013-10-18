<?php // $Id$
namespace easyconfig;
use lib\base\Defaults,
    IteratorAggregate,
    ArrayIterator;

/**
 * A generic configuration class.
 *
 * @author Lucas Marshall
 */
class Config implements i\Config, IteratorAggregate
{
    protected $___path   = null;
    protected $___name   = null;
    protected $___rows   = array();
    protected $___expand = true;

    public function __construct($path = null, $name = null, $values=array())
    {
        $extension = Loader::factory()->getExtension();
        if ($name === null)
            $name = basename($path, $extension);

        // Strip off BASEDIR, /'s and .yaml
        $path = str_replace(Defaults::$CONFDIR, '', $path);
        $path = str_replace($extension, '', $path);
        if ($name === '_object')
        {
            $path = substr($path, 0, -8);
            $name = substr($path, strrpos($path, '/')+1);
        }

        $this->___path = $path;
        $this->___name = $name;

        foreach($values as $key => $value)
        {
            $this->$key = $value;
        }
    }

    public function expand($expand = true)
    {
        $this->___expand = $expand;
        return $this;
    }

    public function setName($name)
    {
        return $this->___name = $name;
    }

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

    public function __get($key)
    {
        if (isset($this->___rows[$key]))
        {
            if ($this->___rows[$key] instanceof LazyLoadConfig && $this->___expand)
                $this->___rows[$key] = $this->___rows[$key]->load();

            return $this->___rows[$key];
        }
    }

    public function __set($key, $value)
    {
        $this->___rows[$key] = $value;
    }

    public function __isset($key)
    {
        return isset($this->___rows[$key]);
    }

    public function hasKey($key)
    {
        return array_key_exists($key, $this->___rows);
    }

    public function getIterator($ignoreExpansion = false)
    {
        if ($this->___expand && !$ignoreExpansion)
            $this->notSoLazy();

        return new ArrayIterator($this->___rows);
    }

    public function notSoLazy()
    {
        // @fixme Hack to get around lazy loading
        foreach($this->___rows as $key => $value)
            if ($value instanceof LazyLoadConfig)
                $this->___rows[$key] = $value->load();
        return $this;
    }

    /**
     * returns an array representation of this object and any descendants
     *
     * @return array the array representation of the object
     */
    public function getObjectArray($recursive = false)
    {
        $this->notSoLazy();
        if ($recursive)
        {
            $arr  = array();
            foreach ($this->___rows as $key => $val) {
                $val       = (is_object($val)) ? $val->getObjectArray(true) : $val;
                $arr[$key] = $val;
            }
        }
        else
        {
            $arr = $this->___rows;
        }

        return $arr;
    }

    /**
     * Get a next config by an array of keys
     * i.e. $keys => ['a', 'b', 'c'] maps to:  $config->a->b->c;
     * This method attempts to either find an app or global config for the property array
     *
     * @param array $keys An config name
     * @return Collection
     */
    public function resolve(array $map = null)
    {
        $miss  = null;
        $value = $this;
        if ($map) foreach ($map as $key)
        {
            if (!isset($value->$key))
            {
                $value = $miss;
                break;
            }

            $value = $value->$key;
        }

        if ($value instanceof i\Config && $map)
        {
            $value->___path = $this->___path . '/' . implode('/', $map);
            $value->___name = end($map);
        }

        return $value;
    }

    public function fixPaths($context = '', $name = '', $override_path = '')
    {
        static::fixConfigPaths($this, $context, $name, $override_path);
    }

    public static function fixConfigPaths($config, $context = '', $name = '', $override_path = '')
    {
        $config_path = $config->path();
        $config_name = $config->name();

        if ($context && (!$config_path || $context[0] === '/' || $config_path === '/') && $override_path)
            $config_path = $config->setPath($override_path);
        else if ($config instanceof Collection)
            $config_path = $config->setPath($context);
        else
            $config_path = $config->appendPath($context);

        if ($context)
            $config_name = $config->setName($name);
        if (!$config_name)
        {
            $parts = explode('/', $context);
            $config_name = $config->setName(array_pop($parts));
        }

        foreach ($config->getIterator(true) as $key => $sub_config)
        {
            // Fix for reference bug in yaml doodler
            if (is_array($sub_config))
            {
                $type = __NAMESPACE__ . '\\' . ((key($sub_config) === 0) ? 'Collection' : 'Config');
                $sub_config = new $type('', $key, $sub_config);
                $config->$key = $sub_config;
            }

            if ($sub_config instanceof i\Config)
                $sub_config->fixPaths($context.'/'.$key, $key, $config_path.'/'.$key);
        }
    }

    /**
     * Merges another config into this one.
     *
     * Beware, this is a very naive copy, which doesn't actually merge, but
     * replaces original data with data at the same key from the supplied config.
     *
     * @param Config $config
     * @return Config
     */
    public function merge(Config $config)
    {
        $this->___rows = array_merge($this->___rows, $config->___rows);
        return $this;
    }

    public function toArray()
    {
        return $this->getObjectArray();
    }

    public function toArrayRecursive()
    {
        $this->getObjectArray(true);
    }

    public function keys()
    {
        return array_keys($this->___rows);
    }

    public function __unset($key)
    {
        unset($this->___rows[$key]);
    }
}

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 smarttab: */
