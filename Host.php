<?php // $Id$
namespace easyconfig;

/**
 * configuration for a host
 *
 * @author Lucas Marshall
 */
class Host extends Config implements i\Switchable, i\Weighted, i\HasRoles
{
    protected $use_role;

    static private $validation_mapping = array(
        'host'   => 'validateHost',
        'port'   => 'validateInteger',
        'state'  => 'validateBoolean',
        'weight' => 'validateInteger'
    );

    public function __get($key)
    {
        if ($key == 'ip')
            return parent::__get('host');

        return parent::__get($key);
    }

    public function __set($key, $value)
    {
        if (isset(self::$validation_mapping[$key]))
        {
            $method = self::$validation_mapping[$key];
            $value  = Validation::$method($value);
        }

        parent::__set($key, $value);
    }

    public function getHostPort()
    {
        return "$this->host:$this->port";
    }

    public function getState()
    {
        // default to true if the state isn't set
        if (!isset($this->state))
            return true;
        return $this->state;
    }

    public function getWeight()
    {
        $roles = $this->getRoles();
        if (isset($this->use_role) && isset($roles->{$this->use_role}))
            $roles->{$this->use_role};

        // default to 1 if the weight isn't set
        if (!isset($this->weight))
            return 1;
        return $this->weight;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function useRole($role)
    {
        $this->use_role = $role;
        return $this;
    }
}

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 smarttab: */
