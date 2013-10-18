<?php

namespace easyconfig\yamltaghandler;

use \easyconfig\YamlParser,
    \easyconfig\LazyLoadConfig,
    \easyconfig\Loader,
    \easyconfig\i\YamlTagHandler,
    \easyconfig\e\Shenanigans,
    \lib\base\Defaults;

/**
 * Handles the !import tag
 *
 * @author lucas
 */
class Import implements YamlTagHandler
{
    public static function callback($value, $tag, $flags)
    {
        if (strpos($value, ':') !== false)
        {
            list($path, $internal_path) = explode(':', $value, 2);
        }
        else
        {
            $path          = $value;
            $internal_path = null;
        }

        if ($internal_path)
            $internal_path = explode('.', $internal_path);

        // check to see if we have an absolute path
        if (strpos($path, '/') === 0)
        {
            $filename = $path;
        }
        else
        {
            $dir = dirname(YamlParser::getCurrentFile());
            $filename = realpath($dir.DIRECTORY_SEPARATOR.$path);
        }

        if (empty($filename))
        {
            $msg = "External file " . $value . " specified in " . YamlParser::getCurrentFile() . " can't be found";
            if (Defaults::$ENV == 'dev')
            {
                throw new Shenanigans($msg);
            }
            else
            {
                log_msg($msg, 'error', __CLASS__);
                return null;
            }
        }
        if (pathinfo($path, PATHINFO_EXTENSION) == 'yaml')
        {
            $confdir = Loader::getCurrentConfDir();

            // Handle unresolved absolute paths by peeking at the CONFDIR
            if ($path[0] != '/' && strpos($filename, $confdir) !== 0)
                $confdir = dirname($filename).'/';

            $filename = str_replace($confdir, "", $filename);

            $value = new LazyLoadConfig($filename, $internal_path, $confdir);
        }
        else
        {
            $value = file_get_contents($filename);
        }

        return $value;
    }
}
