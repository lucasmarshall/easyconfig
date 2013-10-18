<?php // $Id$
namespace easyconfig\i;

/**
 * for configs that have roles
 *
 * @author Lucas Marshall
 */
interface HasRoles {
    public function getRoles();
    public function useRole($string);
}

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 smarttab: */
