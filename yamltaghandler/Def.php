<?php

namespace easyconfig\yamltaghandler;

/**
 * Handles the !def tag
 *
 * @author lucas
 */
class Def implements \easyconfig\i\YamlTagHandler
{
    public static function callback($value, $tag, $flags)
    {
        $value = constant('\lib\common\Def::' . $value);
        return $value;
    }
}
