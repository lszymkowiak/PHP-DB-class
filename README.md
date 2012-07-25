[PHP-DB-class](http://lszymkowiak.pl/db)
=============

provide an easy way to work with databases with [PHP](http://www.php.net/) using [PDO](http://www.php.net/manual/en/book.pdo.php) extension

Initialization
--------------

### 1. Include db class ###

    require_once( 'db.class.php' );

### 2. Define the database connection parameters ###

**db\_dsn** - Data Source Name, required to connect database

**db\_user** - database user name, optional if not specified null is passed

**db\_password**  - database password, optional if not specified null is passed

More information about connecting to databesa using PDO can be found in [PHP Manual](http://www.php.net/manual/en/pdo.construct.php)

*MySQL example:*

    db::configure( 'db_dsn', 'mysql:host=localhost;dbname=php_db_class' );
    db::configure( 'db_user', 'user' );
    db::configure( 'db_password', 'passwd' );

*PostgreSQL example:*

    db::configure( 'db_dsn', 'pgsql:host=localhost;port=5432;dbname=php_db_class;user=user;password=passwd' );


### 3. Initialize db class object ###

    $db = db::get_instance();

Public methods
--------------

### query( $query ) ###

Execute query and return the number of affected rows.
Designed for queries like insert,update,replace,delete, etc which doesn't return any results.

    $db->query( "INSERT INTO users SET name='John Smith', date_of_birth='1990-01-01'" );
    $db->query( "UPDATE users SET date_of_birth='1995-01-01' WHERE id='1'" );

### get_var( $query ) ###

Select single variable from database.

    echo $db->get_var( "SELECT date_of_birth FROM users WHERE name='1'" );

### get_row( $query, $output='OBJECT' ) ###

Select single row and return it as object or associative array.
Type of returned variable can be defined by second parameter: 'OBJECT' (default) or 'ARRAY'.

*object example*

    $user = $db->get_row( "SELECT name,date_of_birth FROM users WHERE id='1'" );
	  echo $user->name;
	  echo $user->date_of_birth;

*array example*

    $user = $db->get_row( "SELECT name,date_of_birth FROM users WHERE id='1'", 'ARRAY' );
	  echo $user['name'];
	  echo $user['date_of_birth'];

### get_col( $query ) ###

Select select column and return it as indexed array.

    $users = $db->get_col( "SELECT name FROM users ORDER BY date_of_birth" );
    foreach( $users as $name ) {
        echo $name;
    }

### get_results( $query, $output='OBJECT' ) ###

Select multiple rows.
Type of returned variable can be array of objects or array of associative array.
It can be defined by second parameter: 'OBJECT' (default) or 'ARRAY'.

*object example*:

    $users = $db->get_results( "SELECT name,date_of_birth FROM users ORDER BY date_of_birth" );
    foreach( $users as $user ) {
        echo $user->name;
        echo $user->date_of_birth;
    }

*array example*:


    $users = $db->get_results( "SELECT name,date_of_birth FROM users ORDER BY date_of_birth", 'ARRAY' );
    foreach( $users as $user ) {
        echo $user['name'];
        echo $user['date_of_birth'];
    }

### get_assoc( $query, $col, $output='OBJECT' ) ###

Select multiple rows and return it as an associative array (key defined as one of selected columns) of object or associative array.

*object example*:

    $id_user = 1;
		$users = $db->get_assoc( "SELECT id,name,date_of_birth FROM users ORDER BY date_of_birth", 'id' );
    echo $user[$id_user]->name;
    echo $user[$id_user]->date_of_birth;

*array example*:

    $id_user
		$users = $db->get_assoc( "SELECT id,name,date_of_birth FROM users ORDER BY date_of_birth", 'id', 'ARRAY' );
    echo $user[$id_user]['name'];
    echo $user[$id_user]['date_of_birth'];


### debug( $state ) ###

Turn on or off debug display.
Debug is turned off by default.

    $db->debug( true );
    $db->get_results( "SELECT name,date_of_birth FROM users ORDER BY date_of_birth" );
    $db->debug( false );

### debug_once() ###

Turn on debug display only for the next query

    $db->debug_once();
    $db->get_results( "SELECT name,date_of_birth FROM users ORDER BY date_of_birth" );

### error( $state ) ###

Turn on or off query error display.
Error is turned on by default.

    $db->error( true );
    $db->get_results( "SELEC name,date_of_birth FROM users ORDER BY date_of_birth" );
    $db->error( false );

### escape( $tring ) ###

Return escaped string for safe query

    $name = "Patrick O'Brian";
    $db->query( "INSERT INTO users name='".$db->escape($name)."', date_of_birth='1914-12-12'" );

		
Public properties
-----------------

### insert_id ###

Insert ID from last query.

    $db->query( "INSERT INTO users SET name='John Smith', date_of_birth='1990-01-01'" );
    echo $db->insert_id

### affected_rows ###

Number of rows affected by query() method.

    $db->query( "UPDATE users SET date_of_birth='1990-01-01'" );
    echo $db->affected_rows;

### num_rows ###

Number of rows returned by select query.

    $db->get_results( "SELECT name,date_of_birth FROM users WHERE date_of_birth>='1990-01-01' ORDER BY date_of_birth" );
    echo $db->num_rows;