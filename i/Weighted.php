<?php // $Id$
namespace easyconfig\i;

/**
 * for configs that are able to be weighted for randomization
 *
 * @author Lucas Marshall
 */
interface Weighted {
    public function getWeight();
}

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 smarttab: */
