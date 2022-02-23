<?php
class plugin_Debug_Debug extends plugin_Abstract
{
	protected static $appName = 'DBG';

	public function __construct( $timer = null )
	{
		parent::__construct( null, $timer );
		$this->debugger = new helper_Debug( $this->getConfig( 'ROOTPATH' ), 3, $this->getConfig( 'SUBPATH' ) );
		if ( $this->getConfig( 'LEVEL' ) )
			$this->debugger->setDebugLevel( $this->getConfig( 'LEVEL' ) );
		return $this;
	}
	
	public function run( )
	{
		if ( $this->getConfig( 'ENABLED' ) )
			echo $this->debugger->renderDebug( );
	}

	public static function issueError( $error, $level, $code = 0 )
	{
		return forward_static_call_array( array( 'helper_Debug', 'issueError' ), func_get_args( ) );
	}

	public static function issueWarning( $warning, $level, $code = 0 )
	{
		return forward_static_call_array( array( 'helper_Debug', 'issueWarning' ), func_get_args( ) );
	}

	public static function issueDebug( $debug, $level )
	{
		return forward_static_call_array( array( 'helper_Debug', 'issueDebug' ), func_get_args( ) );
	}

	public static function varDump( )
	{
		return forward_static_call_array( array( 'helper_Debug', 'varDump' ), func_get_args( ) );
	}
}