<?php
/**
 * PHP-DB-class
 * provide an easy way to work with databases with PHP using PDO extension
 *
 * method names and some code are based on ezSQL by Justin Vincent
 *
 * @version		2.0
 * @author		Łukasz Szymkowiak
 * @link			http://www.lszymkowiak.pl/db
 * @license		This work is licensed under a Creative Commons Attribution 3.0 Unported License. 
 *						To view a copy of this license, visit http://creativecommons.org/licenses/by/3.0/
 */
class db {
	
	private static $db_instance;
	
	private $pdo = null;
	static $db_dsn = null;
	static $db_user = null;
	static $db_password = null;
	private $error = true;
	private $debug = false;
	private $debug_once = false;
	private $method = null;
	private $query = null;
	private $result = null;
	public $insert_id = null;
	public $affected_rows = null;
	public $num_rows = null;
	private $start_time = null;
	private $exec_time = null;
	
	private $mode = array ( 'OBJECT' => PDO::FETCH_OBJ, 'ARRAY' => PDO::FETCH_ASSOC );
	
	
	/**
	 * configure database connection properities
	 *
	 * @access	public
	 * @param		string	$setting
	 * @param		string	$value
	 */
	public static function configure( $setting, $value=null ) {
		if ( property_exists( __CLASS__, $setting ) ) {
			self::$$setting = $value;
		}
	}
	
	
	/**
	 * singelton instance
	 *
	 * @access  public
	 */
	public static function get_instance() {
		if ( isset( self::$db_instance ) == false ) {
			self::$db_instance = new db();
		}
		return self::$db_instance;
	}
	
	
	/**
	 * creates a connection to a database
	 *
	 * @access  private
	 */
	private function __construct() {
		try {
			$this->pdo = new PDO( self::$db_dsn, self::$db_user, self::$db_password );
		} catch( PDOException $e ) {
			$this->_show_error( $e->getMessage() );
		}
	}
	
	
	/**
	 * execute query and return the number of affected rows
	 *
	 * @access	public
	 * @param 	string	$query
	 * @return	int
	 */
	public function query( $query ) {
		$this->method = __METHOD__;
		$this->query = trim( $query );
		if ( $statement = $this->_query() ) {
			$this->result = $statement->rowCount();
		}
		$this->debug || $this->debug_once ? $this->_show_debug() : null;
		$this->debug_once = false;
		return $this->result;
	}
	
	
	/**
	 * executes query and returns single variable
	 *
	 * @access	public
	 * @param 	string	$query		query to execute
	 * @return	string
	 */
	public function get_var( $query ) {
		$this->method = __METHOD__;
		$this->query = trim( $query );
		if ( $statement = $this->_query() ) {
			$this->result = $statement->fetchColumn();
 			$this->num_rows = ( $this->num_rows > 0 ? 1 : 0 );
		}
		$this->debug || $this->debug_once ? $this->_show_debug() : null;
		$this->debug_once = false;
		return $this->result;
	}
	
	
	/**
	 * executes query and returns single row in associative array or object (defined by $output)
	 *
	 * @access	public
	 * @param 	string	$query
	 * @param 	string	$output
	 * @return	object|array
	 */
	public function get_row( $query, $output='OBJECT' ) {
		$this->method = __METHOD__;
		$this->query = trim( $query );
 		if ( $statement = $this->_query() ) {
			$output = array_key_exists( $this->output, $this->mode ) ? $output : key( $this->mode );
			$this->result = $statement->fetch( $this->mode[$output] );
 			$this->num_rows = ( $this->num_rows > 0 ? 1 : 0 );
 		}
		$this->debug || $this->debug_once ? $this->_show_debug() : null;
 		$this->debug_once = false;
 		return $this->result;
	}
	
	
	/**
	 * executes query and returns single column in indexed array
	 *
	 * @access	public
	 * @param 	string	$query
	 * @return	array
	 */
	public function get_col( $query ) {
		$this->method = __METHOD__;
		$this->query = trim( $query );
		if ( $statement = $this->_query() ) {
			while ( $row = $statement->fetchColumn() ) {
				$this->result[] = $row;
			}
		}
		$this->debug || $this->debug_once ? $this->_show_debug() : null;
 		$this->debug_once = false;
		return $this->result;
 	}
	
	
	/**
	 * executes query and return all rows in indexed array
	 * rows can be returned as associative array or object (defined by $output)
	 *
	 * @access	public
	 * @param 	string	$query
	 * @param 	string	$output
	 * @return	array
	 */
	public function get_results( $query, $output='OBJECT' ) {
		$this->method = __METHOD__;
		$this->query = trim( $query );
 		if ( $statement = $this->_query() ) {
			$output = array_key_exists( $output, $this->mode ) ? $output : key( $this->mode );
			$this->result = $statement->fetchAll( $this->mode[$output] );
 		}
		$this->debug || $this->debug_once ? $this->_show_debug() : null;
		$this->debug_once = false;
		return $this->result;
	}
	
	
	/**
	 * executes query and return all rows in associative array (key defined by $col)
	 * rows can be returned as associative array or object (defined by $output)
	 *
	 * @access	public
	 * @param 	string	$query
	 * @param 	string	$output
	 * @return	mixed
	 */
	public function get_assoc( $query, $col, $output='OBJECT' ) {
		$this->method = __METHOD__;
		$this->query = trim( $query );
 		if ( $statement = $this->_query() ) {
			$output = array_key_exists( $output, $this->mode ) ? $output : key( $this->mode );
			while ( $row = $statement->fetch( $this->mode[$output] ) ) {
 				if ( $output == 'OBJECT' ) {
 					$this->result[$row->$col] = $row;
 				} else {
 					$this->result[$row[$col]] = $row;
 				}
 			}
 		}
		$this->debug || $this->debug_once ? $this->_show_debug() : null;
 		$this->debug_once = false;
		return $this->result;
	}
	
	
	/**
	 * returns escaped string for safe query
	 *
	 * @access	public
	 * @param		string $string
	 * @return	string
	 */
	public function escape( $string ) {
		return addslashes( stripslashes( $string ) );
	}
	
	
	/**
	 * turns on or off errors displaying
	 *
	 * @access	public
	 * @param		bool		$state
	 */
	public function error( $state=true ) {
		$this->error = $state;
	}
	
	
	/**
	 * turns on or off debug displaying
	 *
	 * @access	public
	 * @param		bool		$state
	 */
	public function debug( $state=true ) {
		$this->debug = $state;
	}
	
	
	/**
	 * turns on or off errors displaying for for a single query
	 *
	 * @access	public
	 */
	public function debug_once() {
		$this->debug_once = true;
	}
	
	
	/**
	 * prepares query to execute
	 *
	 * @access	private
	 * @param		string		$query
	 * @return	object
	 */
	private function _query() {
		$this->_reset();
		if ( $this->pdo ) {
			$statement = $this->pdo->query( $this->query );
			$error = $this->pdo->errorInfo();
 			if ( $error['1'] ) {
				$this->_show_error( $error['2'] );
			} else {
				if ( preg_match( "/^\s*(insert|update|replace|delete)\s+/i", strtolower( $this->query ) ) ) {
 					$this->affected_rows = $statement->rowCount();
 				}
				if ( preg_match( "/^\s*(insert|replace)\s+/i", strtolower( $this->query ) ) ) {
 					$this->insert_id = $this->pdo->lastInsertId();	
 				}
				if ( preg_match( "/^\s*(select)\s+/i", strtolower( $this->query ) ) ) {
					$this->num_rows = $statement->rowCount();
				}
 				return $statement;
			}
		}
	}
	
	
	/**
	 * resets variables specific for each query
	 *
	 * @access	private
	 */
	private function _reset() {
		$this->start_time = microtime(true);
		$this->insert_id = null;
		$this->affected_rows = null;
		$this->num_rows = null;
		switch( $this->method ) { 
			case __CLASS__.'::query';
				$this->result = 0;
				break;
			case __CLASS__.'::get_var':
				$this->result = false;
				break;
			case __CLASS__.'::get_row':
				$this->result = ( $this->output == 'ARRAY' ? array() : null );
				break;
			default:
				$this->result = array();
				break;
		}
	}
		
	
	/**
	 * displays query debug (method name, query string, execution time, number of rows in result, affected rows, insert ID, query result)
	 *
	 * @access	private
	 */
	private function _show_debug() {
		$this->exec_time = sprintf( "%01.10f", microtime(true) - $this->start_time );
		echo '<span style="color:#999;"><b>' . $this->method . '</b>( "' . $this->query . '" )</span>';
		echo '<br /><b>EXECUTION_TIME:</b> ' . $this->exec_time . 's';
		echo $this->num_rows !== null ? '<br /><b>NUM_ROWS:</b> ' . $this->num_rows : '';
		echo $this->affected_rows !== null ? '<br /><b>AFFECTED_ROWS:</b> ' . $this->affected_rows : '';
		echo $this->insert_id !== null ? '<br /><b>INSERT_ID:</b> ' . $this->insert_id : '';
		echo '<br /><b>DB_RESULT:</b>';
		echo '<pre>';
		print_r( $this->result );
		echo '</pre>';
		echo '<hr />';
	}
	
	
	/**
	 * displays error
	 *
	 * @access	private
	 * @param		string		$txt
	 * @param		string		$query
	 * @return	null
	 */
	private function _show_error( $txt ) {
		if ( $this->error === true ) {
			echo '<b>DB_ERROR:</b> ' . $txt . '<br />' . ( empty( $this->query ) == false ? '<b>QUERY:</b> ' . $this->query . '<br />' : '' );
		}
	}
	
}
?>