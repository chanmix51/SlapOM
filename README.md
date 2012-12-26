==========================================
SlapOM Small Object Model Manager for LDAP
==========================================

**SlapOM** is a simple object model manager for LDAP. It allow you to **generate**, **hydrate** (and very soon "**manage**") objects from your LDAP objectClass.

SlapOM works with PHP 5.3 and needs the php5-ldap extention module. It use the LDAP protocol version 3.

Project structure :
*******************

├── documentation

├── lib
  ├── SlapOM

  └── Exception

└── tests
    ├── bootstrap

    ├── config

    ├── fixtures

    └── SlapOM
       ├── Tests

       └── Units

Generation and hydratation example :
************************************

Here is the scenario, you want to retrive your "person" objectClass from the LDAP into a User class to display the user name, email and group informations.

Step 1 - Create the inherited objects
=====================================
So the first step is to create the object (=entity=User) that represent the "person" objectClass AND his mapper.

Entity class (must extends \\SlapOM\\Entity) : 
----------------------------------------------
::

  public class User extends \SlapOM\Entity
  {
  } 

Mapper class (must extends \\SlapOM\\EntityMap and must be named "{Entity class name}Map") : 
--------------------------------------------------------------------------------------------
The abstract class EntityMap contains an abstract method configure(), you have to override this method to set up parameters.
::

  public class UserMap extends \SlapOM\EntityMap
  {

      protected function configure()
      {
          /* Sets up the required options */
          $this->base_dn = 'dc=knplabs,dc=com'
          $this->ldap_object_class = 'person';
          $this->entity_class = 'User';

          /* Sets up the fields that you want to retrieve in your User class */
          // Standard String fields
          $this->addAttribute('firstname');
          $this->addAttribute('lastname');
          $this->addAttribute('mail');
          // Array field
          $this->addAttribute('objectclass', self::FIELD_MULTIVALUED);
      }

  } 

Step 2 - Use it !
=================
Code :

::

  /* Initialises the connection */
  $connection = new SlapOM\Connection('localhost', 'cn=root', 'root');
  
  /* Instantiates the mapper class  */
  $userMap = $connection->getMapFor('User');

  /* Retrieves all User in $result array */
  $result = $userMap->find();

  /* Displays result */
  echo '<ul>';

  foreach ($result as $user)
  {
    echo '<li>';
    echo sprintf('%s, %s (%s) is member of:', $user->getFirstname(), $user->getLastname(), $user->getMail());
    echo '<ul>';
    foreach ($user->getObjectclass() as $group)
    {
        echo sprintf('<li>%s</li>', $group);
    }
    echo '</ul>';
    echo '</li>';
  }

  echo '</ul>';

Result : 

::

  * Amar, Aaccf (user.0@maildomain.net) is member of:
    - person
    - organizationalperson
    - inetorgperson
    - top
  * Atp, Aaren (user.1@maildomain.net) is member of:
    - person
    - organizationalperson
    - inetorgperson
    - top
  * Atpco, Aarika (user.2@maildomain.net) is member of:
    - person
    - organizationalperson
    - inetorgperson
    - top

You can also specifie a filter. This can be done by setting the first parameter of the ``find()`` method with a normalized LDAP filter string like :

::

  $result = $userMap->find('(first=Amar)');

Tests :
*******
The entire SlapOM library is unit tested with **Atoum** (http://downloads.atoum.org/). You can run the test suite with the command :

::

  php /{wherever the atoum.phar is}/mageekguy.atoum.phar -d tests/SlapOM/Tests/Units/

Or class by class :

::

  php tests/SlapOM/Tests/Units/{File name}

Before run it, make sure you have loaded the LDIF fixtures (test/fixtures/ldap_datas.ldif) in your LDAP testing server and edited the tests/config/config.ini file.