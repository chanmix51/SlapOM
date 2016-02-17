<?php

namespace SlapOM\Tests\Units;

use \mageekguy\atoum;

class EntityMap extends atoum\test
{

    public function test__construct()
    {
        $connection = new \SlapOM\Connection(LDAP_HOST, LDAP_BIND_DN, LDAP_PASSWORD);

        $this->assert
                ->exception(function() use ($connection) {
                            $connection->getMapFor('SlapOM\Tests\Units\BadUser1');
                        })
                ->isInstanceOf('\SlapOM\Exception\SlapOM')
                ->hasMessage('Base DN is not set after configured class \'SlapOM\Tests\Units\BadUser1Map\'.');

        $this->assert
                ->exception(function() use ($connection) {
                            $connection->getMapFor('SlapOM\Tests\Units\BadUser2');
                        })
                ->isInstanceOf('\SlapOM\Exception\SlapOM')
                ->hasMessage('LDAP \'objectClass\' is not set after configured class \'SlapOM\Tests\Units\BadUser2Map\'.');

        $this->assert
                ->exception(function() use ($connection) {
                            $connection->getMapFor('SlapOM\Tests\Units\BadUser3');
                        })
                ->isInstanceOf('\SlapOM\Exception\SlapOM')
                ->hasMessage('Entity class is not set after configured class \'SlapOM\Tests\Units\BadUser3Map\'.');

        $this->assert
                ->exception(function() use ($connection) {
                            $connection->getMapFor('SlapOM\Tests\Units\BadUser4');
                        })
                ->isInstanceOf('\SlapOM\Exception\SlapOM')
                ->hasMessage('Attributes list is empty after configured class \'SlapOM\Tests\Units\BadUser4Map\'.');
    }

    public function testFind()
    {
        $connection = new \SlapOM\Connection(LDAP_HOST, LDAP_BIND_DN, LDAP_PASSWORD);
        $map = $connection->getMapFor('SlapOM\Tests\Units\UserForTest3');

        $result = $map->find();

        $this->assert
                ->object($result)
                ->isInstanceOf('ArrayIterator');

        $this->assert
                ->integer(count($result))
                ->isEqualTo(2000);

        $result = $map->find('(l=1)');

        $this->assert
                ->integer(count($result))
                ->isEqualTo(0);

        $result = $map->find(null, 'ou=people');

        $this->assert
                ->integer(count($result))
                ->isEqualTo(2000);

        $result = $map->find(null, null, 10);

        $this->assert
                ->integer(count($result))
                ->isEqualTo(10);

        $result = $map->find('(uid=user.0)');

        $this->assert
                ->integer(count($result))
                ->isEqualTo(1);

        $this->assert
                ->array($result[0]['objectclass'])
                ->hasSize(4);
    }

    public function testGetAttributeNames()
    {
        $connection = new \SlapOM\Connection(LDAP_HOST, LDAP_BIND_DN, LDAP_PASSWORD);
        $map = $connection->getMapFor('SlapOM\Tests\Units\UserForTest3');

        $this->assert
                ->array($map->getAttributeNames())
                ->hasSize(5);
    }

    public function testAddAttribute()
    {
        $connection = new \SlapOM\Connection(LDAP_HOST, LDAP_BIND_DN, LDAP_PASSWORD);
        $map = $connection->getMapFor('SlapOM\Tests\Units\UserForTest3');

        $this->assert
                ->array($map->getAttributeNames())
                ->hasSize(5);

        $map->addAttribute('test');

        $this->assert
                ->array($map->getAttributeNames())
                ->hasSize(6);
    }

    public function testGetAttributeModifiers()
    {
        $connection = new \SlapOM\Connection(LDAP_HOST, LDAP_BIND_DN, LDAP_PASSWORD);
        $map = $connection->getMapFor('SlapOM\Tests\Units\UserForTest3');

        $this->assert
                ->integer($map->getAttributeModifiers('cn'))
                ->isEqualTo(0);

        $this->assert
                ->integer($map->getAttributeModifiers('objectclass'))
                ->isEqualTo(\SlapOM\EntityMap::FIELD_MULTIVALUED);
    }

    public function testSave()
    {
        $connection = new \SlapOM\Connection(LDAP_HOST, LDAP_BIND_DN, LDAP_PASSWORD);
        $map = $connection->getMapFor('SlapOM\Tests\Units\UserForTest3');

        $result = $map->find('(uid=user.1999)');
        $user = $result[0];

        $this->assert
                ->boolean($user->isPersisted())
                ->isFalse();

        $newMail = sprintf("random.mail.%d@plop.org", rand(0, 1000));

        $user->setMail($newMail);     
        $map->save($user);

        $this->assert
                ->boolean($user->isPersisted())
                ->isTrue();

        $result = $map->find('(uid=user.1999)');
        $user = $result[0];

        $this->assert
                ->boolean($user->isPersisted())
                ->isFalse();

        $this->assert
                ->string($user->getMail())
                ->isEqualTo($newMail);

        $this->assert
                ->exception(function() use ($map) {
                            $user = new UserForTest3();
                            $map->save($user);
                        })
                ->isInstanceOf('\SlapOM\Exception\SlapOM')
                ->hasMessage('This fonctionality is not yet implemented.');
    }

}

class UserForTest3 extends \SlapOM\Entity
{
    
}

class BadUser1Map extends \SlapOM\EntityMap
{

    protected function configure()
    {
        
    }

}

class BadUser2Map extends \SlapOM\EntityMap
{

    protected function configure()
    {
        $this->base_dn = 'dc=knplabs,dc=com';
    }

}

class BadUser3Map extends \SlapOM\EntityMap
{

    protected function configure()
    {
        $this->base_dn = 'dc=knplabs,dc=com';
        $this->ldap_object_class = 'person';
    }

}

class BadUser4Map extends \SlapOM\EntityMap
{

    protected function configure()
    {
        $this->base_dn = 'dc=knplabs,dc=com';
        $this->ldap_object_class = 'person';
        $this->entity_class = 'SlapOM\Tests\Units\UserForTest3';
    }

}

class UserForTest3Map extends \SlapOM\EntityMap
{

    protected function configure()
    {
        $this->base_dn = 'dc=knplabs,dc=com';
        $this->ldap_object_class = 'person';
        $this->entity_class = 'SlapOM\Tests\Units\UserForTest3';
        $this->addAttribute('cn');
        $this->addAttribute('l');
        $this->addAttribute('mail');
        $this->addAttribute('objectclass', \SlapOM\EntityMap::FIELD_MULTIVALUED);
    }

}