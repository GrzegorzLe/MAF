<?php
class Core
{
	protected static $appName;

	protected $debugger = null;
	protected $timer = null;

	public function __construct( $debugger = null, $timer = null )
	{
		$trim = strrpos( get_class( $this ), '_' );
		if ( $trim )
			$relPath = DIRECTORY_SEPARATOR . strtr( substr( get_class( $this ), 0, $trim ), '_', DIRECTORY_SEPARATOR );
		else
			$relPath = '';
		helper_ClassLoader::addIncludePath( __DIR__ . $relPath );
		$this->debugger = $debugger;
		$this->timer = $timer;
	}

	public function getConfig( $value )
	{
		if ( array_key_exists( $value, $GLOBALS[ '_cfg' ][ static::$appName ] ) )
			return $GLOBALS[ '_cfg' ][ static::$appName ][ $value ];
		return false;
	}

	public function debug( $debug, $level )
	{
		if ( $this->debugger === null )
			return;
		return forward_static_call_array( array( 'helper_Debug', 'issueDebug' ), func_get_args( ) );
	}

	public function warn( $warning, $level, $code = 0 )
	{
		if ( $this->debugger === null )
			return;
		return forward_static_call_array( array( 'helper_Debug', 'issueWarning' ), func_get_args( ) );
	}

	public function error( $error, $level, $code = 0 )
	{
		if ( $this->debugger === null )
			return;
		return forward_static_call_array( array( 'helper_Debug', 'issueError' ), func_get_args( ) );
	}

	public function dump( )
	{
		if ( $this->debugger === null )
			return;
		return forward_static_call_array( array( 'helper_Debug', 'varDump' ), func_get_args( ) );
	}
}
