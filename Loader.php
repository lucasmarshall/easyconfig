<?php // $Id$
namespace easyconfig;
use lib\base\Defaults,
    lib\cache\Base as Cache,
    SplFileInfo,
    FilesystemIterator;

/**
 * Accessor for configuration.
 *
 * @author Lucas Marshall
 */
class Loader
{
    protected static $cache = array();
    static $currentConfDir;

    public $reader_class = 'lib\\config\\YamlReader';

    protected $confdir;
    protected $reader;

    public static function factory($confdir = null)
    {
        if ($confdir === null)
            $confdir = Defaults::$CONFDIR;

        if (empty(self::$cache[$confdir]))
            self::$cache[$confdir] = new static($confdir);

        return self::$cache[$confdir];
    }

    public function __construct($confdir)
    {
        $this->confdir = $confdir;
    }

    public static function getCurrentConfDir()
    {
        if (!self::$currentConfDir)
            return Defaults::$CONFDIR;
        return self::$currentConfDir;
    }

    /**
     * loads the configuration from the specified configuration path
     *
     * @param string|array $path a '/'-seperated string or an array of path parts specifying the config to load
     * @return I\Config
     */
    public function get($path)
    {
        self::$currentConfDir = $this->confdir;

        if (is_string($path))
            $path = explode('/', $path);

        foreach (array('LocalCache', 'Cache', 'Filesystem') as $method)
        {
            $method = 'loadConfigFrom' . $method;
            if ($config = $this->$method($path))
                break;
        }

        self::$currentConfDir = null;
        return $config;
    }
    public static function getConfig($path) { return static::factory()->get($path); }

    public function loadConfigFromFilesystem($path)
    {
        $reader = $this->getReader();
        $path_string = implode('/', $path);
        $full_path   = $this->confdir . $path_string;
        $config_path = new SplFileInfo($full_path);
        if ($config_path->isDir())
            $config = $this->loadConfigFromDirectory($config_path->getPathname());
        else
            $config = $this->loadConfigFromFile($config_path->getPathname() . $reader->getConfigFileExtension(), $path_string);

        // put the loaded config in cache
        $this->putConfigInLocalCache($path, $config);
        $this->putConfigInCache($path, $config);

        return $config;
    }

    /**
     * Gets the key to cache the config at the specified path on
     *
     * @param array $path the path to get the key for
     * @return string the key
     */
    public function getCacheKey($path)
    {
        $prefix = Defaults::uhash();
        $prefix = 'config_' . $prefix . '_' . $this->confdir;

        $path = implode('/', $path);
        return $prefix . $path;
    }

    protected function putConfigInCache($path, $config)
    {
        $cache_key   = $this->getCacheKey($path);
        $cache       = Cache::getByName('Config');
        $cache->set($cache_key, $config);
    }

    protected function putConfigInLocalCache($path, $config)
    {
        $cache_key   = $this->getCacheKey($path);
        $local_cache = Cache::getByName('Local');
        $local_cache->set($cache_key, $config);
    }

    protected function loadConfigFromLocalCache($path)
    {
        $cache_key   = $this->getCacheKey($path);
        $local_cache = Cache::getByName('Local');

        $config = $local_cache->get($cache_key);
        return $config;
    }

    protected function loadConfigFromCache($path)
    {
        $cache_key   = $this->getCacheKey($path);
        $cache       = Cache::getByName('Config');

        $config = $cache->get($cache_key);

        $this->putConfigInLocalCache($path, $config);
        return $config;
    }

    public function getExtension()
    {
        $reader_class = $this->reader_class;
        return $reader_class::FILE_EXTENSION;
    }

    protected function loadConfigFromDirectory($directory)
    {
        $extension      = $this->getExtension();
        $directory_iter = new FilesystemIterator($directory);
        $lazyload_paths = array();

        foreach($directory_iter as $item)
        {
            $filename = $item->getFilename();
            if ($filename == '_object' . $extension)
            {
                $config = $this->loadConfigFromFile($item->getPathname());
                continue;
            }

            list($key, $path)     = $this->getKeyAndPathFromPathname($item->getPathname());
            $lazyload_paths[$key] = $path;
        }

        if (!isset($config))
        {
            $config = new Config($directory);
        }

        foreach($lazyload_paths as $key => $path)
            $config->$key = new LazyLoadConfig($path, null, $this->confdir);

        return $config;
    }

    public function getKeyAndPathFromPathname($path)
    {
        $extension   = $this->getExtension();
        $key         = basename($path, $extension);
        $realconfdir = realpath($this->confdir);
        $path        = $this->stripConfigPath($path);
        $path        = str_replace($realconfdir . '/', '', $path);
        $path        = str_replace($extension, '', $path);
        return array($key, $path);
    }

    /**
     * Strips the config directory path from the given path
     * @param string $path
     * @return string
     */
    public function stripConfigPath($path)
    {
        $path = str_replace($this->confdir, '', $path);
        // Terrible. have to do this because of the fact that we resolve symlinks and the deploy system uses symlinks
        // to switch between old and new configs. Boooooooooo!
        $path = str_replace(rtrim($this->confdir,'/').'1/', '', $path);
        $path = str_replace(rtrim($this->confdir,'/').'2/', '', $path);
        return $path;
    }

    public function getReader()
    {
        if (!$this->reader)
        {
            $class = $this->reader_class;
            $this->reader = new $class;
        }
        return $this->reader;
    }

    protected function loadConfigFromFile($file, $path = '')
    {
        self::$currentConfDir = $this->confdir;
        $reader = $this->getReader();
        try
        {
            $extension = $reader->getConfigFileExtension();

            if (substr($file, -strlen($extension)) === $extension)
                $config = $reader->readObjectFromFile($file);

            if (is_object($config))
                $config->fixPaths($config->path() ? "" : $path);
            else if (!$config)
                $config = new EmptyConfig($reader->getRelativePath($file), $reader->nameFromPath($file));
        }
        catch (e\YamlParseFailure $e)
        {
            $config = new EmptyConfig($reader->getRelativePath($file), $reader->nameFromPath($file));
        }
        catch (\Exception $e)
        {
            self::$currentConfDir = null;
            throw $e;
        }

        self::$currentConfDir = null;
        return $config;
    }

    public function loadConfigFromString($string)
    {
        try
        {
            $config = $this->getReader()->readObjectFromString($string);
        }
        catch (e\YamlParseFailure $e)
        {
            $config = new EmptyConfig('', '');
        }

        return $config;
    }
}

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 smarttab: */
