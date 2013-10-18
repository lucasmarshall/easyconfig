<?php // $Id$
namespace easyconfig;

class Validation
{
    static function validateString($string) {
        if (!is_string($string)) {
            throw new e\Exception('expected a string, not a ' . gettype($string));
        }
        return $string;
    }

    static function validateInteger($integer)
    {
        if (!is_integer($integer)) {
            throw new e\Exception('expected a integer, not a ' . gettype($integer));
        }
        return $integer;
    }

    static function validateHost($host)
    {
        self::validateString($host);
        // @todo add IP checker
        return $host;
    }

    static function validateBoolean($value)
    {
        if ($value === 1 || $value === true || $value === 'true' || $value === 'yes' || $value === 'on')
                $value = true;
        elseif ($value === 0 || $value === null || $value === false || $value === 'false' || $value === 'no' || $value === 'off')
            $value = false;
        else
            throw new e\Exception('expected a boolean analogue, not ' . print_r($value, 1));
        return $value;
    }
}


/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 smarttab: */
