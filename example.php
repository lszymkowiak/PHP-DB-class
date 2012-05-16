<?php

/**
 * specify the database connection parameters
 * include db.class.php file and initialize db class object
 */
define( DB_DSN, 'mysql:host=localhost;dbname=example' );
define( DB_USER, 'user' );
define( DB_PASSWORD, 'passwd' );

require_once( 'db.class.php' );

$db = db::get_instance();


/*
 * EXAMPLE 1
 * 
 * insert into row into database and retrieving inserted row ID
 */
$db->query( "INSERT INTO users SET name='John Smith', date_of_birth='1990-01-01'" );

echo $db->insert_id;

/*
 * EXAMPLE 2
 *
 * update database and retrieving afected rows
 */
$db->query( "UPDATE users SET date_of_birth='1995-01-01' WHERE id='{$id}'" );

echo $db->affected_rows;


/*
 * EXAMPLE 3
 *
 * select single variable
 */

echo $db->get_var( "SELECT date_of_birth FROM users WHERE name='{$id}'" );


/**
 * EXAMPLE 4
 *
 * select one row and return it as object
 */
$user = $db->get_row( "SELECT name,date_of_birth FROM users WHERE id='{$id}'" );

echo $user->name;
echo $user->date_of_birth;


/**
 * EXAMPLE 5
 *
 * select one row and return it as associative array
 */
$user = $db->get_row( "SELECT name,date_of_birth FROM users WHERE id='{$id}'", 'ARRAY' );

echo $user['name'];
echo $user['date_of_birth'];


/**
 * EXAMPLE 6
 *
 * select one column
 */
$users = $db->get_col( "SELECT name FROM users ORDER BY date_of_birth" );

foreach( $users as $name ) {
	echo $name;
}


/**
 * EXAMPLE 7
 *
 * select multiple rows and return it as an array of objects
 */
$users = $db->get_results( "SELECT name,date_of_birth FROM users ORDER BY date_of_birth" );

foreach( $users as $user ) {
	echo $user->name;
	echo $user->date_of_birth;
}


/**
 * EXAMPLE 8
 *
 * select multiple rows nd return it as an array of associative array
 */
$users = $db->get_results( "SELECT name,date_of_birth FROM users ORDER BY date_of_birth", 'ARRAY' );

foreach( $users as $user ) {
	echo $user['name'];
	echo $user['date_of_birth'];
}


/**
 * EXAMPLE 9
 *
 * select multiple rows and return it as an associative array (with key definde from selected columns) of objects
 */
$users = $db->get_assoc( "SELECT id,name,date_of_birth FROM users ORDER BY date_of_birth", 'date_of_birth' );

echo $user['1995-01-01']->name;
echo $user['1995-01-01']->date_of_birth;


/**
 * EXAMPLE 10
 *
 * select multiple rows and return it as an associative array (with key definde from selected columns) f associative array
 */
$users = $db->get_assoc( "SELECT id,name,date_of_birth FROM users ORDER BY date_of_birth", 'date_of_birth', 'ARRAY' );

echo $user['1995-01-01']['name'];
echo $user['1995-01-01']['date_of_birth'];


/**
 * EXAMPLE 11
 *
 * display debug for the next query
 */
$db->debug_once();

$db->get_results( "SELECT name,date_of_birth FROM users ORDER BY date_of_birth" );

// outpuy
//
// db::get_results( "SELECT name,date_of_birth FROM users ORDER BY date_of_birth" )
// EXECUTION_TIME: 0.0012631416s
// NUM_ROWS: 10
// Array
// (
// 	[0] => stdClass Object
// 		(
// 				[name] => John Smith
// 				[date_of_birth] => 1990-01-01
// 		)
// )
?>