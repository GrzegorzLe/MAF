<?php
/**
 * Debug helper output renderer, returns HTML Table format
 * 
 * @author Grzegorz Lesniewski
 * @copyright Grzegorz Lesniewski 2009-2010

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
include 'variable/HTMLTableRenderer.php';

class helper_debug_HTMLTableRenderer extends helper_debug_AbstractRenderer
{
	protected static $varRenderer = 'helper_debug_variable_HTMLTableRenderer';

	public static function render( $debug, $debugLevel )
	{
		$render[ helper_Debug::DEBUG ] = 'renderDebug';
		$render[ helper_Debug::WARNING ] = 'renderWarning';
		$render[ helper_Debug::ERROR ] = 'renderError';

		$retDebug = static::renderTableStart( 'debug' );

		foreach ( $debug as $msg )
			if ( $msg[ 4 ] <= $debugLevel )
				$retDebug .= static::$render[ $msg[ 3 ] ]( $msg );
		$retDebug .= static::renderTableEnd( 'debug' );
		return $retDebug;
	}

	public static function renderDebug( $debug )
	{
		return self::renderTableRow( 'debug', $debug );
	}

	public static function renderWarning( $warning )
	{
		return self::renderTableRow( 'warning', $warning );
	}

	public static function renderError( $error )
	{
		return self::renderTableRow( 'error', $error );
	}

	private static function renderTableStart( $type )
	{
		$table = <<<TABLE
<table class="_dbg_output">
<tr><th onclick="_dbg_toggleTable( this )" title="click to collapse" colspan="2">$type</th></tr>
TABLE;
		return $table;
	}

	public static function renderTableRow( $type, $debug )
	{
		$tmp = static::$varRenderer;
		$retVal = '<td>' . $debug[ 1 ] . ( $debug[ 2 ] ? '(' . $debug[ 2 ] . ')' : '' );
		if ( count( $debug[ 5 ] ) )
			foreach ( $debug[ 5 ] as $i => $dbg )
				$retVal .= $tmp::render( $dbg, false, true );
		$retVal .= '</td></tr>';
		$retVal = '<tr><td class="_dbg_' . $type . '" onclick="_dbg_toggleRow( this )">' . $debug[ 0 ][ 'file' ] . '(' . $debug[ 0 ][ 'line' ] . '): ' . $debug[ 0 ][ 'function' ] . '</td>' . $retVal;
		return $retVal;
	}

	private static function renderTableEnd( $type )
	{
		return '</table>' . "\r";
	}
}
?>