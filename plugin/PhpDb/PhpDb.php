<?php
require_once( 'pDB/pDB_SQL_PARSER.php');
require_once( 'pDB/pDB_TABLE_OBJ.php');
require_once( 'pDB/pDB_CORE.php');

class plugin_PhpDb_PhpDb extends plugin_Abstract
{
	protected static $appName = 'PDB';
	protected $core;

	public function __construct( $debugger = null, $timer = null )
	{
		parent::__construct( $debugger, $timer );
		$this->core = new pDB_CORE( );
		$this->core->pDB_init( '', $this->getConfig( 'CONF_DATA' ), $this->getConfig( 'USER_DATA' ) );
		$this->core->pDB_login( 'root', 'pDB' );
	}

	public function query( $sql )
	{
		return $this->core->query( $sql );
	}
}