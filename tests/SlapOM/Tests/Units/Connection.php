<?php

namespace SlapOM\Tests\Units;

class Connection extends \atoum
{

    public function testGetMapFor()
    {
        $connection = new \SlapOM\Connection(LDAP_HOST, LDAP_BIND_DN, LDAP_PASSWORD, LDAP_PORT);

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
        $connection = new \SlapOM\Connection(LDAP_HOST, LDAP_BIND_DN, LDAP_PASSWORD, LDAP_PORT);
        $userMap = $connection->getMapFor('SlapOM\Tests\Units\UserForTest1');
        $result = $connection->search($userMap, 'dc=example,dc=com', '(objectClass=person)', array('cn'));
        $this->assert
                ->integer(count($result))
                ->isEqualTo(2000);

        $connection = new \SlapOM\Connection('fakeHost', LDAP_BIND_DN, LDAP_PASSWORD);

        $this->assert
                ->exception(function() use ($connection, $userMap) {
                            $connection->search($userMap, 'dc=example,dc=com', '(objectClass=person)', array('cn'));
                        })
                ->isInstanceOf('\SlapOM\Exception\Ldap')
                ->hasMessage('ERROR Could not bind to LDAP host=\'fakeHost:389\' with login=\'cn=admin,dc=example,dc=com\'.. LDAP ERROR (-1) -- Can\'t contact LDAP server --. Can\'t contact LDAP server');


        $connection = new \SlapOM\Connection(LDAP_HOST, LDAP_BIND_DN, LDAP_PASSWORD, LDAP_PORT);

        $this->assert
                ->exception(function() use ($connection, $userMap) {
                            $connection->search($userMap, 'dc=example,dc=com', '(&=test)', array('cn'));
                        })
                ->isInstanceOf('\SlapOM\Exception\Ldap')
                ->hasMessage('ERROR Error while filtering dn \'dc=example,dc=com\' with filter \'(&=test)\'.. LDAP ERROR (-7) -- Bad search filter --. Bad search filter');
    }

    public function testModify()
    {
        $dn = 'uid=user.1999,ou=People,dc=example,dc=com';

        $connection = new \SlapOM\Connection(LDAP_HOST, LDAP_BIND_DN, LDAP_PASSWORD, LDAP_PORT);

        $result = $connection->modify($dn, array('mail' => 'newMail@plop.com'));

        $this->assert
                ->boolean($result)
                ->isTrue();

        $result = $connection->modify($dn, array('mail' => array('newMail1@plop.com', 'newMail2@plop.com', 'newMail3@plop.com')));

        $this->assert
                ->boolean($result)
                ->isTrue();

        $this->assert
                ->exception(function() use ($connection, $dn) {
                            $connection->modify($dn, array('objectClass' => 'protectedObjectClass'));
                        })
                ->isInstanceOf('\SlapOM\Exception\Ldap');

        $this->assert
                ->exception(function() use ($connection, $dn) {
                            $connection->modify($dn, array('objectClass' => null));
                        })
                ->isInstanceOf('\SlapOM\Exception\Ldap')
                ->hasMessage("ERROR Error while DELETING attributes {objectClass} in dn='$dn'.. LDAP ERROR (65) -- Object class violation --. Object class violation");
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
        $this->base_dn = 'dc=example,dc=com';
        $this->ldap_object_class = 'person';
        $this->entity_class = 'SlapOM\Tests\Units\UserForTest1';
        $this->addAttribute('cn');
    }

}
