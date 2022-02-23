<?php
/**
 * File containing debug helper class
 *
 * @author Grzegorz Lesniewski
 * @copyright Grzegorz Lesniewski 2009-2012

	Copyright 2009-2010 Grzegorz Lesniewski <grzegorz.lesniewski@gmail.com>

	Licensed under the Apache License, Version 2.0 (the "License");
	you may not use this file except in compliance with the License.
	You may obtain a copy of the License at

		http://www.apache.org/licenses/LICENSE-2.0

	Unless required by applicable law or agreed to in writing, software
	distributed under the License is distributed on an "AS IS" BASIS,
	WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	See the License for the specific language governing permissions and
	limitations under the License.
 */
/**
 * include helper config and renderer
 */
include 'debug/config.php';
include 'debug/' . _DBG_RENDERER . 'Renderer.php';
/**
 * Debug helper class
 *
 * Helper handling application's error/warning/debug messages and exceptions.
 * Stores all messages in $_REQUEST scope. Renders debug output on demand.
 *
 * @author Grzegorz Lesniewski
 * @copyright Grzegorz Lesniewski 2009-2012
 */
class helper_Debug extends helper_abstract_Helper
{
	/** Level of the event */
	const USER = 1, ADMIN = 2, DEVEL = 3, VERBOSE = 4, QUERY = 5, ALL = 6;
	/** Type of the event */
	const ERROR = 0, WARNING = 1, DEBUG = 2;
	

	private static $debugLevel = self::ALL;
	private static $cssJsIncluded = false;
	private static $rootPath = '';
	private static $backtraceOffset = 1;
	private static $subPath = false;
//	public static $timer;

	function __construct( $rootPath, $backtraceOffset = 1, $subPath = false )
	{
		if ( !array_key_exists( _DBG_REQUEST_KEY, $_REQUEST ) || !is_array( $_REQUEST[ _DBG_REQUEST_KEY ]) )
			$_REQUEST[ _DBG_REQUEST_KEY ] = array( );
		self::$rootPath = $rootPath;
		self::$backtraceOffset = $backtraceOffset;
		self::$subPath = $subPath;
		//		self::$timer = new helper_debug_Timer( );
	}

	/**
	 * Function used to set debug level
	 *
	 * @static
	 * @param integer $level level of debug messages which will be included in debug output
	 * @return boolean
	 */
	public static function setDebugLevel( $level )
	{
		switch ( $level )
		{
			case 'USER':
			case 'user':
			case '1';
			case 1:
				self::$debugLevel = self::USER;
				break;
			case 'ADMIN':
			case 'admin':
			case '2':
			case 2:
				self::$debugLevel = self::ADMIN;
				break;
			case 'DEVEL':
			case 'devel':
			case '3':
			case 3:
				self::$debugLevel = self::DEVEL;
				break;
			case 'VERBOSE':
			case 'verbose':
			case '4':
			case 4:
				self::$debugLevel = self::VERBOSE;
				break;
			case 'QUERY':
			case 'query':
			case '5':
			case 5:
				self::$debugLevel = self::QUERY;
				break;
		}
		return true;
	}

	/**
	 * Function used to report an error in application
	 *
	 * @static
	 * @param string $error error message
	 * @param integer $level severity of error
	 * @param integer $code error code
	 * @return void
	 */
	public static function issueError( $error, $level, $code = 0 )
	{
		$args = func_get_args( );
		$args = array_slice( $args, 3 );
		self::issue( self::ERROR, $error, $level, $code, $args );
	}

	/**
	 * Function used to issue a warning in application
	 *
	 * @static
	 * @param string $warning warning message
	 * @param integer $level severity of warning
	 * @param integer $code warning code
	 * @return void
	 */
	public static function issueWarning( $warning, $level, $code = 0 )
	{
		$args = func_get_args( );
		$args = array_slice( $args, 3 );
		self::issue( self::WARNING, $warning, $level, $code, $args );
	}

	/**
	 * Function used to issue a debug message in application
	 *
	 * @static
	 * @param string $debug debug message
	 * @param integer $level severity of debug
	 * @return void
	 */
	public static function issueDebug( $debug, $level )
	{
		$args = func_get_args( );
		$args = array_slice( $args, 2 );
		self::issue( self::DEBUG, $debug, $level, false, $args );
	}

	private static function issue( $what, $debug, $level, $code, $args )
	{
		_tmr( )->start( 'debug', 'issuing' );
		$dbt = self::parseBackTrace( debug_backtrace( ) );
		if ( defined( 'helper_Debug::' . $level ) )
			$level = constant( 'helper_Debug::'.  $level );
		else
			$level = helper_Debug::ALL;
		array_push( $_REQUEST[ _DBG_REQUEST_KEY ], array( $dbt, $debug, $code, $what, $level, $args ) );
		_tmr( )->end( 'debug' );
	}

	/**
	 * Function used to handle exceptions issued in the application
	 *
	 * @static
	 * @param Exception $exception issued exception
	 * @return void
	 */
	public static function handleException( $exception )
	{
		
	}

	public static function varDump( )
	{
		$retDump = '';
		if ( !static::$cssJsIncluded )
		{
			$retDump = static::getCssJs( );
			static::$cssJsIncluded = true;
		}

		$renderer = 'helper_debug_variable_' . _DBG_RENDERER . 'Renderer';
		$vars = func_get_args( );
		foreach ( $vars as $var )
			$retDump .= $renderer::render( $var );
		return $retDump;
	}

	/**
	 * Function used to render debug messages registered during last page generation
	 *
	 * @static
	 * @return string HTML with debug messages
	 */
	public static function renderDebug( )
	{
		$debug = '';
		if ( !static::$cssJsIncluded )
		{
			$debug = static::getCssJs( );
			static::$cssJsIncluded = true;
		}

		$renderer = 'helper_debug_' . _DBG_RENDERER . 'Renderer';
		$debug .= "<div class='_debug'>";
		$debug .= $renderer::render( $_REQUEST[ _DBG_REQUEST_KEY ], self::$debugLevel );
		$debug .= "</div>\r";
		return $debug;
	}

	private static function parseBackTrace( $dbt )
	{
		$pos = false;
		$bto = self::$backtraceOffset;
		if ( !is_array( $dbt ) || count( $dbt ) <= $bto )
			return false;
		$retDBT = array( );
		if ( self::$subPath )
			$pos = strpos( $dbt[ $bto ][ 'file' ], self::$subPath );
		if ( $pos )
			$pos += strlen( self::$subPath );
		if ( !$pos )
		{
			$pos = strpos( $dbt[ $bto ][ 'file' ], self::$rootPath );
			if ( $pos )
				$pos += strlen( self::$rootPath );
		}
		if ( $pos )
			$file = substr( $dbt[ $bto ][ 'file' ], $pos );
		else
			$file = $dbt[ $bto ][ 'file' ];
		$retDBT[ 'file' ] = $file;
		$retDBT[ 'line' ] = $dbt[ $bto ][ 'line' ];
		$retDBT[ 'function' ] = '';
		$bto++;
		if ( count( $dbt ) > $bto )
		{
			if ( isset( $dbt[ $bto ][ 'class' ] ) )
				$retDBT[ 'function' ] .= $dbt[ $bto ][ 'class' ];
			if ( isset( $dbt[ $bto ][ 'type' ] ) )
				$retDBT[ 'function' ] .= $dbt[ $bto ][ 'type' ];
			if ( isset( $dbt[ $bto ][ 'function' ] ) )
				$retDBT[ 'function' ] .= $dbt[ $bto ][ 'function' ] . '()';
		}
		return $retDBT;
	}

	private static function getCssJs( )
	{
		$retCss = '<style>' . "\r";
		ob_start( );
		include 'debug/debug.css';
		$retCss .= ob_get_clean( );
		$retCss .= '</style>' . "\r";

		$retJs = '<script type="text/javascript">' . "\r";
		ob_start( );
		include 'debug/debug.js';
		$retJs .= ob_get_clean( );
		$retJs .= '</script>' . "\r";

		return $retCss . $retJs;
	}
}
?>