<?php // $Id$
namespace easyconfig;

/**
 * This class handles parsing yaml strings and files
 *
 * @author Lucas Marshall
 */
class YamlParser
{
    protected static $tag_callbacks = array(
        '!import'       => '\easyconfig\yamltaghandler\Import::callback',
        '!const'        => '\easyconfig\yamltaghandler\Constant::callback',
        '!host'         => '\easyconfig\yamltaghandler\Host::callback',
        '!def'          => '\easyconfig\yamltaghandler\Def::callback',
        YAML_MAP_TAG    => '\easyconfig\yamltaghandler\Mapping::callback',
        YAML_SEQ_TAG    => '\easyconfig\yamltaghandler\Sequence::callback',
    );

    protected static $file_stack = array();

    /**
     * parses the specified file
     *
     * @param string $yaml_file the path to the yaml file to parse
     * @return array
     */
    public static function parseFile($yaml_file)
    {
        array_push(self::$file_stack, $yaml_file);
        $data = null;
        $numdocs = null;

        $is_valid = file_exists($yaml_file);

        if ($is_valid)
        {
            $is_valid = false;
            $file_data = file($yaml_file);
            foreach ($file_data as $line)
                if ($line !== "" && $line[0] !== '#')
                    $is_valid = true;
        }

        if ($is_valid)
            $data = yaml_parse(implode("", $file_data), 0, $numdocs, static::$tag_callbacks);

        if ($data == null)
        {
            array_pop(self::$file_stack);
            throw new e\YamlParseFailure('Unable to parse file ' . $yaml_file);
        }
        array_pop(self::$file_stack);
        return $data;
    }

    /**
     * parses the string passed in
     *
     * @param string $yaml_string the yaml string to parse
     * @return array
     */
    public static function parseString($yaml_string)
    {
        $numdocs = null;
        $data = yaml_parse($yaml_string, 0, $numdocs, static::$tag_callbacks);
        if ($data == null)
            throw new e\YamlParseFailure('Unable to parse string.');
        return $data;
    }

    public static function getCurrentFile()
    {
        return end(self::$file_stack);
    }
}

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 smarttab: */
