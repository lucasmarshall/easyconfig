<?php

namespace easyconfig\yamltaghandler;

/**
 * Handles the !host tag
 *
 * @author lucas
 */
class Host implements \easyconfig\i\YamlTagHandler
{
    public static function callback($value, $tag, $flags)
    {
        return new \easyconfig\Host(null, null, $value);
    }
}
