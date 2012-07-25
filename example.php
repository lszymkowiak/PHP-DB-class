<?php

/**
 * include db class
 */
require_once( 'db.class.php' );

/**
 * configure database connection parameters
 *
 * db_dsn				required to connect database
 * db_user			optional if not specified null is passed
 * db_password	optional if not specified null is passed
*/
db::configure( 'db_dsn', 'mysql:host=localhost;dbname=php_db_class' );
db::configure( 'db_user', 'user' );
db::configure( 'db_password', 'passwd' );

// PostgreSQL example
// db::configure( 'db_dsn', 'pgsql:host=localhost;port=5432;dbname=users;user=user;password=passwd' );


/**
 * initialize db class object
 */
$db = db::get_instance();


/**
 * method: query( $query )
 *
 * Execute query and return the number of affected rows.
 * Designed for queries like insert,update,replace,delete, etc which doesn't return any results.
 */
$db->query( "INSERT INTO users SET name='John Smith', date_of_birth='1990-01-01'" );
$db->query( "UPDATE users SET date_of_birth='1995-01-01' WHERE id='1'" );


/**
 * method: get_var( $query )
 * 
 * Select single variable from database.
 */
$date_of_birth = $db->get_var( "SELECT date_of_birth FROM users WHERE name='1'" );


/**
 * method: get_row( $query, $output='OBJECT' )
 * 
 * Select single row and return it as object or associative array.
 * Type of returned variable can be defined by second parameter: 'OBJECT' (default) or 'ARRAY'.
 */

// object example
$user = $db->get_row( "SELECT name,date_of_birth FROM users WHERE id='1'" );
echo $user->name;
echo $user->date_of_birth;

// array example
$user = $db->get_row( "SELECT name,date_of_birth FROM users WHERE id='1'", 'ARRAY' );
echo $user['name'];
echo $user['date_of_birth'];


/**
 * method: get_col( $query )
 * 
 * Select select column and return it as indexed array.
 */
$users = $db->get_col( "SELECT name FROM users ORDER BY date_of_birth" );
foreach( $users as $name ) {
	echo $name;
}


/**
 * method: get_results( $query, $output='OBJECT' )
 * 
 * Select multiple rows.
 * Type of returned variable can be array of objects or array of associative array.
 * It can be defined by second parameter: 'OBJECT' (default) or 'ARRAY'.
 */

// object example
$users = $db->get_results( "SELECT name,date_of_birth FROM users ORDER BY date_of_birth" );
	foreach( $users as $user ) {
		echo $user->name;
		echo $user->date_of_birth;
}

// array example
$users = $db->get_results( "SELECT name,date_of_birth FROM users ORDER BY date_of_birth", 'ARRAY' );
foreach( $users as $user ) {
	echo $user['name'];
	echo $user['date_of_birth'];
}


/**
 * method: get_assoc( $query, $col, $output='OBJECT' )
 * 
 * Select multiple rows and return it as an associative array (key defined as one of selected columns) of object or associative array.
 */

// object example
$id_user = 1;
$users = $db->get_assoc( "SELECT id,name,date_of_birth FROM users ORDER BY date_of_birth", 'id' );
echo $user[$id_user]->name;
echo $user[$id_user]->date_of_birth;

// array example
$id_user = 1;
$users = $db->get_assoc( "SELECT id,name,date_of_birth FROM users ORDER BY date_of_birth", 'id', 'ARRAY' );
echo $user[$id_user]['name'];
echo $user[$id_user]['date_of_birth'];


/**
 * method: debug( $state )
 * 
 * Turn on or off debug display.
 * Debug is turned off by default.
 */
$db->debug( true );
$db->get_results( "SELECT name,date_of_birth FROM users ORDER BY date_of_birth" );
$db->debug( false );


/**
 * method: debug_once()
 * 
 * Turn on debug display only for the next query
 */
$db->debug_once();
$db->get_results( "SELECT name,date_of_birth FROM users ORDER BY date_of_birth" );


/**
 * method: error( $state )
 * 
 * Turn on or off query error display.
 * Error is turned on by default.
 */
$db->error( true );
$db->get_results( "SELEC name,date_of_birth FROM users ORDER BY date_of_birth" );
$db->error( false );


/**
 * method: escape( $tring )
 * 
 * Return escaped string for safe query
 */
$name = "Patrick O'Brian";
$db->query( "INSERT INTO users name='".$db->escape($name)."', date_of_birth='1914-12-12'" );


/**
 * propertie: insert_id
 *
 * Insert ID from last query.
 */
$db->query( "INSERT INTO users SET name='John Smith', date_of_birth='1990-01-01'" );
echo $db->insert_id;


/**
 * propertie: affected_rows
 *
 * Number of rows affected by query() method.
 */
$db->query( "UPDATE users SET date_of_birth='1990-01-01'" );
echo $db->affected_rows;


/**
 * propertie: num_rows
 * 
 * Number of rows returned by select query.
 */
$db->get_results( "SELECT name,date_of_birth FROM users WHERE date_of_birth>='1990-01-01' ORDER BY date_of_birth" );
echo $db->num_rows;

?>