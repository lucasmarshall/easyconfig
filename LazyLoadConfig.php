<?php // $Id$
namespace easyconfig;

use lib\base\Defaults,
    IteratorAggregate,
    ArrayIterator;

/**
 * A marker object for a lazy-loaded config
 *
 * @author lucas
 */
class LazyLoadConfig implements IteratorAggregate
{
    protected $path;
    protected $internal_path = null;
    protected $config_dir;

    /**
     * Constructor for LazyLoadConfig
     * Takes a path to a config file
     *
     * @param string $path the path to the config file to be lazy-loaded
     * @param array $internal_path the path, internal to the loaded config file, to be lazy-loaded
     */
    public function __construct($path, $internal_path = null, $confdir = null)
    {
        $loader = Loader::factory();

        $path                = str_replace($loader->getExtension(), '', $path);
        $path                = $loader->stripConfigPath($path);
        $this->path          = $path;
        $this->internal_path = $internal_path;
        $this->config_dir    = $confdir ? $confdir : Defaults::$CONFDIR;
    }

    /**
     * Get the path to the config file for this LazyLoadConfig
     *
     * @return string the path to the config file
     */
    public function path()
    {
        return $this->path;
    }

    public function load()
    {
        $value = config($this->path(), $this->config_dir);

        if ($this->internal_path)
            $value = $value->resolve($this->internal_path);
        return $value;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->load()->getObjectArray(true));
    }
}

?>
