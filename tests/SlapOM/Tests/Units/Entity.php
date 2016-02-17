<?php

namespace SlapOM\Tests\Units;

include __DIR__ . '/../../../bootstrap/autoload.php';

use \mageekguy\atoum;

class Entity extends atoum\test
{

    public function test_getState()
    {
        $user = new UserForTest2();

        $this->assert
                ->integer($user->_getState())
                ->isEqualTo(\SlapOM\Entity::ENTITY_NEW);

        $user->_setState(\SlapOM\Entity::ENTITY_MODIFIED);

        $this->assert
                ->integer($user->_getState())
                ->isEqualTo(\SlapOM\Entity::ENTITY_MODIFIED);
    }

    public function testModify()
    {
        $user = new UserForTest2();

        $user->modify();

        $this->assert
                ->integer($user->_getState())
                ->isEqualTo(\SlapOM\Entity::ENTITY_MODIFIED);
    }

    public function testIsModified()
    {
        $user = new UserForTest2();

        $this->assert
                ->boolean($user->isModified())
                ->isFalse();

        $user->modify();

        $this->assert
                ->boolean($user->isModified())
                ->isTrue();

        $user->persist();

        $this->assert
                ->boolean($user->isModified())
                ->isTrue();

        $user = new UserForTest2();
        $user->persist();

        $this->assert
                ->boolean($user->isModified())
                ->isFalse();

        $user->modify();

        $this->assert
                ->boolean($user->isModified())
                ->isTrue();
        
        $user = new UserForTest2();
        
        $this->assert
                ->boolean($user->isModified())
                ->isFalse();
        
        $user->setWhatever(true);
        
        $this->assert
                ->boolean($user->isModified())
                ->isTrue();
    }

    public function testPersist()
    {
        $user = new UserForTest2();

        $user->persist();

        $this->assert
                ->integer($user->_getState())
                ->isEqualTo(\SlapOM\Entity::ENTITY_PERSISTED);
    }

    public function testIsPersisted()
    {
        $user = new UserForTest2();

        $this->assert
                ->boolean($user->isPersisted())
                ->isFalse();

        $user->persist();

        $this->assert
                ->boolean($user->isPersisted())
                ->isTrue();

        $user->modify();

        $this->assert
                ->boolean($user->isPersisted())
                ->isTrue();

        $user = new UserForTest2();
        $user->modify();

        $this->assert
                ->boolean($user->isPersisted())
                ->isFalse();

        $user->persist();

        $this->assert
                ->boolean($user->isPersisted())
                ->isTrue();
    }

    public function testIsNew()
    {
        $user = new UserForTest2();

        $this->assert
                ->boolean($user->isNew())
                ->isTrue();

        $user->modify();

        $this->assert
                ->boolean($user->isNew())
                ->isFalse();

        $user->persist();

        $this->assert
                ->boolean($user->isNew())
                ->isFalse();
    }

    public function test_call()
    {
        $user = new UserForTest2();

        $this->assert
                ->boolean($user->hasTest())
                ->isFalse();

        $user->setTest('test');

        $this->assert
                ->boolean($user->hasTest())
                ->isTrue();

        $this->assert
                ->string($user->getTest())
                ->isEqualTo('test');

        $user->clearTest();

        $this->assert
                ->boolean($user->hasTest())
                ->isFalse();

        $this->assert
                ->exception(function() use ($user) {
                            $user->fakeCall();
                        })
                ->isInstanceOf('\SlapOM\Exception\SlapOM')
                ->hasMessage('No such method "SlapOM\Tests\Units\UserForTest2:fakeCall()"');
    }

}

class UserForTest2 extends \SlapOM\Entity
{
    
}