<?php // $Id$
namespace easyconfig\i;

/**
 * for configs that are able to be switched on and off via state
 *
 * @author Lucas Marshall
 */
interface Switchable {
    public function getState();
}

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 smarttab: */
