<?php
namespace \SlapOM;

use SlapOM\Exception\SlapOM as SlapOMException;
use SlapOM\Exception\Ldap as LdapException;

class Connection
{
    protected $handler;
    protected $bind_dn;
    protected $host;
    protected $port;
    protected $password;
    protected $maps = array();
    protected $error;

    public function __construct($bind_dn, $host, $port = 389, $password = null)
    {
        $this->bind_dn = $bind_dn;
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
    }

    public function __destruct()
    {
        if ($this->isOpen())
        {
            ldap_unbind($this->handler);
        }
    }

    public function getMapFor($class, $renew = false)
    {
        if (!isset($this->maps[$class]) or $renew === true)
        {
            $this->maps[$class] = new $class($this);
        }

        return $this->maps[$class];
    }

    public function search($dn, $filter, $attributes, $limit = 0)
    {
        $ret = ldap_search($this->getHandler(), $dn, $filter, $attributes, 0, $limit);

        if ($ret === false)
        {
            throw new LdapException(sprintf("Error while filtering dn '%s' with filter '%s'.", $dn, $filter), $this->handler, $this->error);
        }

        return ldap_get_entries($this->handler, $ret);
    }

    protected function isOpen()
    {
        return !(is_null($this->handler) or $this->handler !== false);
    }

    protected function connect()
    {
        $this->handler = ldap_connect($this->host, $this->port);

        if (!$this->isOpen()) 
        {
            throw new SlapOMException(sprintf("Could not open LDAP connection on host='%s' (port='%d').", $this->host, $this->port));
        }

        ldap_get_option($this->handler,LDAP_OPT_ERROR_STRING,$this->error);
        ldap_set_option($this->handler, LDAP_OPT_PROTOCOL_VERSION, 3);

        if (!@ldap_bind($this->handler, $this->bind_dn, $this->password))
        {
            throw new LdapException(sprintf("Could not bind to DN='%s'.", $this->bind_dn, $this->handler, $this->error));
        }
    }

    protected function getHandler()
    {
        if (!$this->isOpen())
        {
            $this->connect();
        }

        return $this->handler();
    }
}
