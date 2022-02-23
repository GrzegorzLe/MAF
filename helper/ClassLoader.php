<?php
require( 'abstract/Helper.php' );

class helper_ClassLoader extends helper_abstract_Helper
{
	private static $fileExtension = '.php';

	public static function register( )
	{
		return spl_autoload_register( array( 'helper_ClassLoader', 'loadClass' ) );
	}

	public static function addIncludePath( $includePath )
	{
		return set_include_path( get_include_path( ) . PATH_SEPARATOR . $includePath );
	}

	public static function loadClass( $class )
	{
		$className = $class;
		$class = strtr( $class, '_', DIRECTORY_SEPARATOR );
		@include( $class . static::$fileExtension );// or die( $dbt[ '1' ][ 'file' ] . ': ' . $dbt[ '1' ][ 'line' ] );
		// @TODO: add a way to report from which file missing include was called
		if ( !class_exists( $className ) )
		{
			$dbt = debug_backtrace( );
			throw new Exception( serialize( $dbt ) );
		}
	}
}
