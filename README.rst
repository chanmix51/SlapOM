==========================================
SlapOM Small Object Model Manager for LDAP
==========================================

**SlapOM** is a simple object model manager for LDAP. It allow you to **generate**, **hydrate** objects from a database using the LDAP protocol. It has been tested with Microsoft Active Directory (tm).

SlapOM works with PHP 5.3 and needs the php5-ldap extension module. It use LDAP v3.

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

Here is the scenario, you want to retrieve your "person" objectClass from the LDAP into a User class to display the user name, email and group informations.

Step 1 - Create the inherited objects
=====================================
So the first step is to create the object (=entity=User) that represent the "person" objectClass AND his mapper.

Entity class (must extend \\SlapOM\\Entity)
-------------------------------------------
::

  public class User extends \SlapOM\Entity
  {
  } 

Mapper class (must extend \\SlapOM\\EntityMap and must be named "{Entity class name}Map")
-----------------------------------------------------------------------------------------
The abstract class EntityMap contains an abstract method configure(), you have to override this method to set up the required parameters.

::

  public class UserMap extends \SlapOM\EntityMap
  {

      protected function configure()
      {
          /* Sets up the required options */
          $this->base_dn = 'dc=company,dc=com'
          $this->ldap_object_class = 'person';
          $this->entity_class = 'User';

          /* Sets up the fields that you want to retrieve in your User class */
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

Initialize the connection::

  $connection = new SlapOM\Connection('localhost', 'cn=root', 'root');

Instantiate the mapper class::

  $userMap = $connection->getMapFor('User');

Query all User entities in a $result array::

  $result = $userMap->find();

Displays result::

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

Of course, most of the time, you are not interested int fetching all entities from the database but a certain set of them. This can be done by setting the first parameter of the ``find()`` method with a normalized LDAP filter string like::

  $result = $userMap->find('(|(mail=*@maildomain.net)(name=user*))');

Note that the returning value of the ``getObjectClassFilter()`` method will be appended to you search string so the final search string will be ``(&(objectClass=user)(|(mail=*@maildomain.net)(name=user*)))``. 

To manage your complex queries, you might use the ``BinaryFilter`` class::

    $filter = \SlapOM\BinaryFilter::create("mail=*@maildomain.net")
        ->addOr("name=user*");

    $result = $userMap->find((string) $filter);

In case you have the DN of a record, use the ``fetch()`` method to get the according object::

    $user = $userMap->fetch($dn);

Projection operator
===================

By default, the queries return collections that pop hydrated objects. These instances are by default fed with the fields declared in their according map class but this behavior can be overloaded using the ``getSearchFields()`` method. Even though it is a good idea to declare the user password as a binary field in the user map class, it is by example not a good idea to fetch it from the database every time a user is retrieved. This method is the right place to strip (or add) fields from your searches.

Dealing with entities
=====================

SlapOM is an OMM hence entities do not know anything about the LDAP database nor their structure: they are just flexible data containers::

    $user['mail'];         // $user->getMail();
    $user->mail;           // $user->getMail();
    $user->getMail();      // $user->get('mail');
    $user->get('mail');    // $mail

If you override the ``getMail()`` accessor, your calls to ``$user['mail']`` and ``$user->mail`` will reflect your overload. You cannot override the generic ``get('mail')`` as this is the only way to access to raw data extracted from the database.

Modifying the entities data follows the same principle. To save an entity, just call the ``save()`` function of the mapper class and give it your modified object::

  $user['mail'] = 'newMail@maildomain.net'; // $user->setMail('newMail@maildomain.net');
  $user->isModified(); // true
  $userMap->save($user);
  $user->isModified(); // false
  $user->isPersisted(); // true

Tests
*******
The entire SlapOM library is unit tested with **Atoum** (http://downloads.atoum.org/). You can run the test suite with the command::

  php /{wherever the atoum.phar is}/mageekguy.atoum.phar -d tests/SlapOM/Tests/Units/

Or class by class::

  php tests/SlapOM/Tests/Units/{File name}

Before run it, make sure you have loaded the LDIF fixtures (test/fixtures/ldap_datas.ldif) in your LDAP testing server and edited the tests/config/config.ini file.
