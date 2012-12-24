<?php

namespace SlapOM\Tests\Units;

include __DIR__ . '/../../../bootstrap/autoload.php';

require_once __DIR__ . '/../../../../../mageekguy.atoum.phar';

use \mageekguy\atoum;

class Connection extends atoum\test
{

    public function testGetMapFor()
    {
        $connection = new \SlapOM\Connection('locahost', 'cn=root', 'root');

        $userMap = $connection->getMapFor('SlapOM\Tests\Units\UserForTest1');

        $this->assert
                ->object($userMap)
                ->isInstanceOf('SlapOM\Tests\Units\UserForTest1Map');
        $this->assert
                ->boolean($userMap->isNew)
                ->isTrue();

        $userMap->isNew = false;

        $userMap = $connection->getMapFor('SlapOM\Tests\Units\UserForTest1');

        $this->assert
                ->object($userMap)
                ->isInstanceOf('SlapOM\Tests\Units\UserForTest1Map');
        $this->assert
                ->boolean($userMap->isNew)
                ->isFalse();

        $userMap = $connection->getMapFor('SlapOM\Tests\Units\UserForTest1', true);

        $this->assert
                ->object($userMap)
                ->isInstanceOf('SlapOM\Tests\Units\UserForTest1Map');
        $this->assert
                ->boolean($userMap->isNew)
                ->isTrue();
    }

    public function testSearch()
    {
        $connection = new \SlapOM\Connection('localhost', 'cn=root', 'root');
        $result = $connection->search('dc=knplabs,dc=com', '(objectClass=person)', array('cn'));
        $this->assert
                ->array($result)
                ->hasSize(2001);

        $connection = new \SlapOM\Connection('fakeHost', 'cn=root', 'root');

        $this->assert
                ->exception(function() use ($connection) {
                            $connection->search('dc=knplabs,dc=com', '(objectClass=person)', array('cn'));
                        })
                ->isInstanceOf('\SlapOM\Exception\Ldap')
                ->hasMessage('ERROR Could not bind to LDAP host=\'fakeHost:389\' with login=\'cn=root\'.. LDAP ERROR (-1) -- Can\'t contact LDAP server --. Can\'t contact LDAP server');


        $connection = new \SlapOM\Connection('localhost', 'cn=root', 'root');

        $this->assert
                ->exception(function() use ($connection) {
                            $connection->search('dc=knplabs,dc=com', '(&=test)', array('cn'));
                        })
                ->isInstanceOf('\SlapOM\Exception\Ldap')
                ->hasMessage('ERROR Error while filtering dn \'dc=knplabs,dc=com\' with filter \'(&=test)\'.. LDAP ERROR (-7) -- Bad search filter --. Bad search filter');
    }

}

class UserForTest1 extends \SlapOM\Entity
{
    
}

class UserForTest1Map extends \SlapOM\EntityMap
{

    public $isNew = true;

    protected function configure()
    {
        $this->base_dn = 'dc=knplabs,dc=com';
        $this->ldap_object_class = 'person';
        $this->entity_class = 'SlapOM\Tests\Units\UserForTest1';
        $this->addAttribute('cn');
    }

}