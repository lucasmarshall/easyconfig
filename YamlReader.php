<?php // $Id$
namespace easyconfig;
use lib\log\Logger, lib\base\Defaults;

/**
 * Reads objects that are generated from yaml and turns them into config objects where necessary
 *
 * @author Lucas Marshall
 */
class YamlReader
{
    const FILE_EXTENSION = '.yaml';

    /**
     * returns the file extension expected for config files
     *
     * @return string the file extension (dot included)
     */
    public function getConfigFileExtension()
    {
        return self::FILE_EXTENSION;
    }

    /**
     * reads an object from the specified yaml file
     *
     * @param string $file_path path to the yaml file
     * @return i\Config the object read
     */
    public function readObjectFromFile($file_path, $default_class = 'easyconfig\Config')
    {
        $data = YamlParser::parseFile($file_path);
        return $data;
    }

    /**
     * converts a yaml string into an object
     *
     * @param string $string the string to convert
     * @return i\Config
     */
    public function readObjectFromString($string, $default_class = 'easyconfig\Config')
    {
        $data = YamlParser::parseString($string);
        return $data;
    }

    /**
     * Get the name of the object from the path
     *
     * @param string $path A unix file path
     * @return string
     */
    public function nameFromPath($path)
    {
        $name_parts = explode('/', $this->getObjectPath($path));
        $name       = array_pop($name_parts);
        if ($name == '_object')
            $name = array_pop($name_parts);
        return $name;
    }

    /**
     * Get the relative path of the object from a full path.
     *
     * @param string $path A unix file path
     * @return string
     */
    public function getRelativePath($path)
    {
        $name_parts = explode('/', $this->getObjectPath($path));
        if ($name_parts[count($name_parts)-1] == '_object')
            $name = array_pop($name_parts);
        $rel_path = implode('/', $name_parts);
        $rel_path = Loader::factory()->stripConfigPath($rel_path);
        return $rel_path;
    }

    /**
     * Get the name of the object path from the path.  The object
     * path is the path without the filename extension if there is one.
     *
     * @param string $path A unix file path
     * @return string
     */
    public function getObjectPath($path)
    {
        return str_replace($this->getConfigFileExtension(), '', $path);
    }
}

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 smarttab: */
