<?php

namespace easyconfig\yamltaghandler;
use easyconfig\YamlParser;

/**
 * Handles the !!map tag
 *
 * @author lucas
 */
class Mapping implements \easyconfig\i\YamlTagHandler
{
    public static function callback($value, $tag, $flags)
    {
        return new \easyconfig\Config(YamlParser::getCurrentFile(), null, $value);
    }
}
