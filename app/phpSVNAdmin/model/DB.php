<?php
/**
 * Classes covering database and query resource interaction
 *
 * Contains DB class covering DB connectivity and interaction and
 * DBI class containing methods easing access to query resource
 *
 * @author Grzegorz Lesniewski
 * @copyright Grzegorz Lesniewski 2009
 */
class model_DB
{
	private static $db;
	private $link;
	private $timer;

	private function __construct( $timer )
	{
		$this->link = mysql_connect( _cfg( 'DB', 'HOST' ), _cfg( 'DB', 'USER' ), _cfg( 'DB', 'PASS' ) );
		$this->timer = $timer;
		mysql_select_db( _cfg( 'DB', 'NAME' ), $this->link );
	}

	public function __destruct( )
	{
		if ( $this->link )
			mysql_close( $this->link );	
	}

	public static function create( $timer = false )
	{
		if ( !model_DB::$db )
			model_DB::$db = new model_DB( $timer );
		return model_DB::$db;
	}

	private function execQuery( $sql )
	{
		return mysql_query( $sql, $this->link );
	}

	public function queryError( )
	{
		return mysql_errno( $this->link ) . ': ' . mysql_error( $this->link );
	}

	// TODO: this should never return false -- hmm, it doesnt seem to...
	public function query( $sql )
	{
		$result = $this->execQuery( $sql );
		_dbg( )->issueDebug( 'database query:', helper_Debug::QUERY, $sql, $result );
		if ( $result )
			return new model_DBI( $result, $sql );
		else
			throw new Exception( $this->queryError( ) );
		return false;
	}

	public function queryRow( $sql )
	{
		if ( $result = $this->execQuery( $sql ) )
			return mysql_fetch_array( $result );
		return false;
	}

	public function queryField( $sql )
	{
		if ( $result = $this->queryRow( $sql ) )
			return $result[ 0 ];
		return false;
	}
}

class model_DBI
{
	private $sql;
	private $query;
	private $row;
	private $row_i;

	public function __construct( $query, $sql = false )
	{
		$this->sql = $sql;
		$this->query = $query;
	}

	public function getSql( )
	{
		return $this->sql;
	}

	public function getRow( )
	{
		$this->row = mysql_fetch_row( $this->query );
		$this->row_i = 0;
		return $this->row;
	}

	public function getField( $nextRow = true )
	{
		if ( $nextRow === true )
			$this->getRow( );
		return $this->row[ $this->row_i++ ];
	}

	public function numRows( )
	{
		return mysql_num_rows( $this->query );
	}

	public function numFields( )
	{
		return mysql_num_fields( $this->query );
	}

	public function getObject( $className = false, $params = false )
	{
		if ( $params !== false )
			return mysql_fetch_object( $this->query, $className, $params );
		else
			return mysql_fetch_object( $this->query, $className );
	}

	public function getAssoc( )
	{
		return mysql_fetch_array( $this->query, MYSQL_ASSOC );
	}

	public function dataSeek( $offset )
	{
		return mysql_dataseek( $this->query, $offset );
	}
}
?>