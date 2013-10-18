<?php
namespace easyconfig\i;

/**
 *
 * @author lucas
 */
interface YamlTagHandler
{
    public static function callback($value, $tag, $flags);
}
