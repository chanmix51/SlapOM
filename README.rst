==========================================
SlapOM Small Object Model Manager for LDAP
==========================================

**SlapOM** is a simple object model manager for LDAP. It allows you to **generate** and **hydrate** objects from a database using the LDAP protocol. It has been tested with Microsoft Active Directory (tm).

SlapOM works with PHP 5.3 and needs the php5-ldap extension module. It uses LDAP v3.

Project structure
*****************

::

  ├── documentation
  │
  ├── lib
  │   ├── SlapOM
  │   └── Exception
  │
  └── tests
      ├── bootstrap
      ├── config
      ├── fixtures
      └── SlapOM
          ├── Tests
          └── Units

Generation and hydration example
********************************

Here is the scenario: you want to retrieve your "person" objectClass from the LDAP server and make its fields available in a User PHP class to display the user name, email and group informations.

Step 1 - Create the inherited objects
=====================================
The first step is to create the object (aka entity or User) that will represent the "person" objectClass AND his mapper.

Entity class (must extend \\SlapOM\\Entity)
-------------------------------------------
.. code-block:: php

  public class User extends \SlapOM\Entity
  {
  } 

Mapper class (must extend \\SlapOM\\EntityMap and must be named "{Entity class name}Map")
-----------------------------------------------------------------------------------------
The abstract class EntityMap contains an abstract method configure(); you must override this method to set up the required parameters.

.. code-block:: php

  public class UserMap extends \SlapOM\EntityMap
  {

      protected function configure()
      {
          /* Set up the required options */
          $this->base_dn = 'dc=company,dc=com'
          $this->ldap_object_class = 'person';
          $this->common_name_identifier = 'uid';
          $this->entity_class = 'User';

          /* Set up the fields that you want to retrieve in your User class */
          // Standard String fields
          $this->addAttribute('firstname');
          $this->addAttribute('lastname');
          $this->addAttribute('mail');

          // Array field
          $this->addAttribute('objectclass', self::FIELD_MULTIVALUED);

          // Binary field
          $this->addAttribute('image', self::FIELD_BINARY);
      }
  }

Step 2 - Use it !
=================

Initialize the connection:

.. code-block:: php

  $connection = new SlapOM\Connection('localhost', 'cn=root', 'root');

Instantiate the mapper class:

.. code-block:: php

  $userMap = $connection->getMapFor('User');

Query all User entities in a $result array:

.. code-block:: php

  $result = $userMap->find();

Display results:

.. code-block:: php

  <ul> 
  <?php foreach ($result as $user): ?>
    <li>
      <?php printf('%s, %s (%s) is objectClass:', $user->getFirstname(), $user->getLastname(), $user->getMail()) ?>
      <ul>
      <?php foreach ($user->getObjectclass() as $group): ?>
          <li><?php printf('<li>%s</li>', $group) ?></li>
      </ul>
      <?php endforeach ?>
    </li>
  <?php endforeach ?>
  </ul>

::

  * Amar, Aaccf (user.0@maildomain.net) is objectClass:
    - person
    - organizationalperson
    - inetorgperson
    - top
  * Atp, Aaren (user.1@maildomain.net) is objectClass:
    - person
    - organizationalperson
    - inetorgperson
    - top
  * Atpco, Aarika (user.2@maildomain.net) is objectClass:
    - person
    - organizationalperson
    - inetorgperson
    - top

Querying the database
=====================

Of course, most of the time, you are not interested in fetching all entities from the database but only a subset of them. This can be done by setting the first parameter of the ``find()`` method with a normalized LDAP filter string such as:

.. code-block:: php

  $result = $userMap->find('(|(mail=*@maildomain.net)(name=user*))');

Note that the return value of the ``getObjectClassFilter()`` method will be prepended to your search string. The final search string will really be ``(&(objectClass=user)(|(mail=*@maildomain.net)(name=user*)))``. 

To manage more complex queries, you might use the ``BinaryFilter`` class:

.. code-block:: php

    $filter = \SlapOM\BinaryFilter::create("mail=*@maildomain.net")
        ->addOr("name=user*");

    $result = $userMap->find((string) $filter);

In case you have the DN of a record, use the ``fetch()`` method to get the corresponding object:

.. code-block:: php

    $user = $userMap->fetch($dn);

Projection operator
===================

By default queries return collections that pop hydrated objects. These instances are by default fed with the fields declared in their corresponding map class. This behavior can be overloaded using the ``getSearchFields()`` method. Even though it is a good idea to declare the user password as a binary field in the user map class, it would not a good idea to fetch it from the database every time a user is retrieved. This method is the right place to strip (or add) fields from your searches.

Dealing with entities
=====================

SlapOM is an OMM hence entities do not know anything about the LDAP database nor their structure: they are just flexible data containers:

.. code-block:: php

    $user['mail'];         // $user->getMail();
    $user->mail;           // $user->getMail();
    $user->getMail();      // $user->get('mail');
    $user->get('mail');    // $mail, raw data from LDAP

If you override the ``getMail()`` accessor, your calls to ``$user['mail']`` and ``$user->mail`` will reflect your overload. You cannot override the generic ``get('mail')`` as this is the only way to access to raw data extracted from the database.

Modifying the entity's data follows the same principle. To save an entity, just call the ``save()`` function of the mapper class and give it your modified object:

.. code-block:: php

  $user['mail'] = 'newMail@maildomain.net'; // $user->setMail('newMail@maildomain.net');
  $user->isModified(); // true
  $userMap->save($user);
  $user->isModified(); // false
  $user->isPersisted(); // true

Tests
*****
The entire SlapOM library is unit tested with **Atoum** (http://downloads.atoum.org/). You can run the test suite with the command::

  php /{wherever the atoum.phar is}/mageekguy.atoum.phar -d tests/SlapOM/Tests/Units/

Or class by class::

  php tests/SlapOM/Tests/Units/{File name}

Before runnning the unit tests, you will need to load into your LDAP testing server the LDIF fixtures (test/fixtures/ldap_datas.ldif) and edit the tests/config/config.ini file.
