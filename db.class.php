<?php
/**
 * PHP PDO class
 *  
 * @version 1.0 beta 1.2
 * @author	Łukasz Szymkowiak
 * @link		http://www.lszymkowiak.pl
 * @license	This work is licensed under a Creative Commons Attribution 3.0 Unported License. To view a copy of this license, visit http://creativecommons.org/licenses/by/3.0/
 */
class db {
	
	private static $db_instance;
	
	private $pdo = null;
	
	private $db_dsn = null;
	private $db_user = null;
	private $db_password = null;
	private $db_options = null;
	
	private $start_time = null;
	private $exec_time = null;
	
	private $error = true;
	private $debug = false;
	private $debug_once = false;
	private $method = null;
	private $query = null;
	private $result = false;
	
	public $insert_id = null;
	public $affected_rows = null;
	public $num_rows = null;
	
	private $mode = array ( 'OBJECT' => PDO::FETCH_OBJ, 'ARRAY' => PDO::FETCH_ASSOC );
	
	
	/**
	 * singelton instance
	 *
	 * @access  public
	 */
	public static function getInstance( $dsn=false, $username=false, $password=false, $options=array() ) {
		if ( isset( self::$db_instance ) == false ) {
			self::$db_instance = new db( $dsn, $username, $password, $driver_options );
		}
		return self::$db_instance;
	}
	
	
	/**
	 * creates a connection to a database
	 *
	 * @access  private
	 */
	private function __construct( $dsn, $username, $password, $options ) {
		$this->db_dsn = $dsn;
		$this->db_user = $username;
		$this->db_password = $password;
		$this->db_options = $options;
		try {
			$this->pdo = new PDO( $this->db_dsn, $this->db_user, $this->db_password, $this->db_options );
		} catch( PDOException $e ) {
			$this->_show_error( 'Failed to connect database' );
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
		if ( $statement = $this->_query( $query ) ) {
			$this->result = $statement->rowCount();
			$this->_show_debug();
		}
		$this->debug_once = false;
		return $this->result;
	}
	
	
	/**
	 * executes query and returns single value
	 *
	 * @access	public
	 * @param 	string	$query
	 * @return	string
	 */
	public function get_var( $query ) {
		$this->method = __METHOD__;
		if ( $statement = $this->_query( $query ) ) {
			$this->result = $statement->fetchColumn();
 			$this->num_rows = ( $this->num_rows > 0 ? 1 : 0 );
			$this->_show_debug();
		}
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
 		if ( $statement = $this->_query( $query ) ) {
			$output = array_key_exists( $output, $this->mode ) ? $output : key( $this->mode );
			$this->result = $statement->fetch( $this->mode[$output] );
 			$this->num_rows = ( $this->num_rows > 0 ? 1 : 0 );
			$this->_show_debug();
 		}
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
		if ( $statement = $this->_query( $query ) ) {
			$this->result = array();
			while ( $row = $statement->fetchColumn() ) {
				array_push( $this->result, $row );
			}
			$this->_show_debug();
		}
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
 		if ( $statement = $this->_query( $query ) ) {
			$output = array_key_exists( $output, $this->mode ) ? $output : key( $this->mode );
			$this->result = $statement->fetchAll( $this->mode[$output] );
			$this->_show_debug();
 		}
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
	 * @return	array
	 */
	public function get_assoc( $query, $col, $output='OBJECT' ) {
 		if ( $statement = $this->_query( $query ) ) {
			$this->result = array();
			$output = array_key_exists( $output, $this->mode ) ? $output : key( $this->mode );
			while ( $row = $statement->fetch( $this->mode[$output] ) ) {
 				if ( $output == 'OBJECT' ) {
 					$this->result[$row->$col] = $row;
 				} else {
 					$this->result[$row[$col]] = $row;
 				}
 			}
			$this->_show_debug();
 		}
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
		return mysql_escape_string( stripslashes( $str ) );
	}
	
	
	/**
	 * turns on or off errors displaying
	 *
	 * @access	public
	 * @param		bool		$bool
	 */
	public function error( $bool=true ) {
		$this->error = $bool;
	}
	
	
	/**
	 * turns on or off debug displaying
	 *
	 * @access	public
	 * @param		bool		$bool
	 */
	public function debug( $bool=true ) {
		$this->debug = $bool;
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
	private function _query( $query ) {
		$this->_reset();
		$this->query = trim( $query );
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
		$this->query = null;
		$this->insert_id = null;
		$this->affected_rows = null;
		$this->num_rows = null;
		$this->result = false;
	}
		
	
	/**
	 * displays debuga
	 *
	 * @access	private
	 */
	private function _show_debug() {
		if ( $this->debug === true || $this->debug_once === true ) {
			$this->exec_time = sprintf( "%01.10f", microtime(true) - $this->start_time );
			echo '<span style="color:#999;"><b>' . $this->method . '</b>( "' . $this->query . '" )</span>';
			echo '<br /><b>EXECUTION_TIME:</b> ' . $this->exec_time . 's';
			echo $this->num_rows !== null ? '<br /><b>NUM_ROWS:</b> ' . $this->num_rows : '';
			echo $this->affected_rows !== null ? '<br /><b>AFFECTED_ROWS:</b> ' . $this->affected_rows : '';
			echo $this->insert_id !== null ? '<br /><b>INSERT_ID:</b> ' . $this->insert_id : '';
			if ( empty( $this->result ) == false ) {
				echo '<pre>';
				print_r( $this->result );
				echo '</pre>';
			}
			echo '<hr />';
		}
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