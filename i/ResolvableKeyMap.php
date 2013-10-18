<?php
namespace easyconfig\i;

interface ResolvableKeyMap
{
    function resolve($path, $map);
}
