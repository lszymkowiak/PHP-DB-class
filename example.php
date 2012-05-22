<?php

/**
 * define the database connection parameters
 * these defines are passed as parameters to PDO::__construct() ( string $dsn [, string $username [, string $password ]] )
 * DB_DSN 			$dsn 			required to connect database
 * DB_USER 			$username optional if not specified null is passed
 * DB_PASSWORD	$password optional if not specified null is passed
*/

// MySQL example:
define( DB_DSN, 'mysql:host=localhost;dbname=example' );
define( DB_USER, 'user' );
define( DB_PASSWORD, 'passwd' );

// PostgreSQL example
// define( DB_DSN, 'pgsql:host=localhost;port=5432;dbname=example;user=user;password=passwd' );


/**
 * include db.class.php file and initialize db class object
 */
require_once( 'db.class.php' );
$db = db::get_instance();


/**
 * Example 1
 * insert row into database and retrieve inserted row ID
 */
$db->query( "INSERT INTO users SET name='John Smith', date_of_birth='1990-01-01'" );
echo $db->insert_id;


/**
 * Example 2
 * update database and retrieve number affected rows
 */
$db->query( "UPDATE users SET date_of_birth='1995-01-01' WHERE id='{$id}'" );
echo $db->affected_rows;


/**
 * Example 3
 * select single variable
 */
echo $db->get_var( "SELECT date_of_birth FROM users WHERE name='{$id}'" );


/**
 * Example 4
 * select one row and return it as object
 */
$user = $db->get_row( "SELECT name,date_of_birth FROM users WHERE id='{$id}'" );
echo $user->name;
echo $user->date_of_birth;


/**
 * Example 5
 * select one row and return it as associative array
 */
$user = $db->get_row( "SELECT name,date_of_birth FROM users WHERE id='{$id}'", 'ARRAY' );
echo $user['name'];
echo $user['date_of_birth'];

/**
 * Example 6
 * select one column
 */
$users = $db->get_col( "SELECT name FROM users ORDER BY date_of_birth" );
foreach( $users as $name ) {
	echo $name;
}


/**
 * Example 7
 * select multiple rows and return it as an array of objects
 */
$users = $db->get_results( "SELECT name,date_of_birth FROM users ORDER BY date_of_birth" );
foreach( $users as $user ) {
	echo $user->name;
	echo $user->date_of_birth;
}


/**
 * Example 8
 * select multiple rows nd return it as an array of associative array
 */
$users = $db->get_results( "SELECT name,date_of_birth FROM users ORDER BY date_of_birth", 'ARRAY' );
foreach( $users as $user ) {
	echo $user['name'];
	echo $user['date_of_birth'];
}


/**
 * Example 9
 * select multiple rows and return it as an associative array (with key defined as one of selected columns) of objects
 */
$users = $db->get_assoc( "SELECT id,name,date_of_birth FROM users ORDER BY date_of_birth", 'date_of_birth' );
echo $user['1995-01-01']->name;
echo $user['1995-01-01']->date_of_birth;

/**
 * Example 10
 * select multiple rows and return it as an associative array (with key defined as one of selected columns) of associative array
 */
$users = $db->get_assoc( "SELECT id,name,date_of_birth FROM users ORDER BY date_of_birth", 'date_of_birth', 'ARRAY' );
echo $user['1995-01-01']['name'];
echo $user['1995-01-01']['date_of_birth'];

/**
 * Example 11
 * display debug for the next query
 */
$db->debug_once();
$db->get_results( "SELECT name,date_of_birth FROM users ORDER BY date_of_birth" );

?>