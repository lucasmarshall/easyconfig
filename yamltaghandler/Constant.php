<?php

namespace easyconfig\yamltaghandler;

/**
 * Handles the !const tag
 *
 * @author lucas
 */
class Constant implements \easyconfig\i\YamlTagHandler
{
    public static function callback($value, $tag, $flags)
    {
        $value = constant($value);
        return $value;
    }
}
