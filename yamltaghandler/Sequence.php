<?php

namespace easyconfig\yamltaghandler;

/**
 * Handles the !!seq tag
 *
 * @author lucas
 */
class Sequence implements \easyconfig\i\YamlTagHandler
{
    public static function callback($value, $tag, $flags)
    {
        $collection = new \easyconfig\Collection();
        foreach($value as $item)
            $collection[] = $item;

        return $collection;
    }
}