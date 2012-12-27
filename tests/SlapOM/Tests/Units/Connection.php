<?php

namespace SlapOM\Tests\Units;

include __DIR__ . '/../../../bootstrap/autoload.php';

require_once __DIR__ . '/../../../../../mageekguy.atoum.phar';

use \mageekguy\atoum;

class Connection extends atoum\test
{

    public function testGetMapFor()
    {
        $connection = new \SlapOM\Connection(LDAP_HOST, LDAP_BIND_DN, LDAP_PASSWORD);

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
        $connection = new \SlapOM\Connection(LDAP_HOST, LDAP_BIND_DN, LDAP_PASSWORD);
        $result = $connection->search('dc=knplabs,dc=com', '(objectClass=person)', array('cn'));
        $this->assert
                ->array($result)
                ->hasSize(2001);

        $connection = new \SlapOM\Connection('fakeHost', LDAP_BIND_DN, LDAP_PASSWORD);

        $this->assert
                ->exception(function() use ($connection) {
                            $connection->search('dc=knplabs,dc=com', '(objectClass=person)', array('cn'));
                        })
                ->isInstanceOf('\SlapOM\Exception\Ldap')
                ->hasMessage('ERROR Could not bind to LDAP host=\'fakeHost:389\' with login=\'cn=root\'.. LDAP ERROR (-1) -- Can\'t contact LDAP server --. Can\'t contact LDAP server');


        $connection = new \SlapOM\Connection(LDAP_HOST, LDAP_BIND_DN, LDAP_PASSWORD);

        $this->assert
                ->exception(function() use ($connection) {
                            $connection->search('dc=knplabs,dc=com', '(&=test)', array('cn'));
                        })
                ->isInstanceOf('\SlapOM\Exception\Ldap')
                ->hasMessage('ERROR Error while filtering dn \'dc=knplabs,dc=com\' with filter \'(&=test)\'.. LDAP ERROR (-7) -- Bad search filter --. Bad search filter');
    }

    public function testModify()
    {
        $connection = new \SlapOM\Connection(LDAP_HOST, LDAP_BIND_DN, LDAP_PASSWORD);

        $result = $connection->modify('uid=user.1999,ou=People,dc=knplabs,dc=com', array('mail' => 'newMail@plop.com'));

        $this->assert
                ->boolean($result)
                ->isTrue();

        $result = $connection->modify('uid=user.1999,ou=People,dc=knplabs,dc=com', array('mail' => array('newMail1@plop.com', 'newMail2@plop.com', 'newMail3@plop.com')));

        $this->assert
                ->boolean($result)
                ->isTrue();

        $this->assert
                ->exception(function() use ($connection) {
                            $connection->modify('uid=user.1999,ou=People,dc=knplabs,dc=com', array('objectclass' => 'protectedObjectClass'));
                        })
                ->isInstanceOf('\SlapOM\Exception\Ldap')
                ->hasMessage('ERROR Error while modifying dn \'uid=user.1999,ou=People,dc=knplabs,dc=com\'.. LDAP ERROR (65) -- Object class violation --. Object class violation');

        $this->assert
                ->exception(function() use ($connection) {
                            $connection->modify('uid=user.1999,ou=People,dc=knplabs,dc=com', array('l' => null));
                        })
                ->isInstanceOf('\SlapOM\Exception\Ldap')
                ->hasMessage('ERROR Error while modifying dn \'uid=user.1999,ou=People,dc=knplabs,dc=com\'.. LDAP ERROR (21) -- Invalid syntax --. Invalid syntax');                        
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