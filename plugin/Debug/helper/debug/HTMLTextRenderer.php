<?php
/**
 * Debug helper output renderer, uses text format with HTML formatting
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
include 'variable/HTMLTextRenderer.php';

class helper_debug_HTMLTextRenderer extends helper_debug_AbstractRenderer
{
	protected static $varRenderer = 'helper_debug_variable_HTMLTextRenderer';

	public static function render( $debug, $level )
	{
		$retDebug = "<br /><br /><br /><br />Rendered debug:<br />\r";

		foreach ( $debug as $msg )
			if ( $msg[ 4 ] <= $level )
			{
				if ( $retDebug[ 0 ][ 1 ][ 'file' ] != $lastfile )
				{
					if ( isset( $msg[ 0 ][ 1 ][ 'file' ] ) )
						$retDebug .= $msg[ 0 ][ 1 ][ 'file' ];
					if ( isset( $msg[ 0 ][ 1 ][ 'class' ] ) )
						$retDebug .= ':' . $msg[ 0 ][ 1 ][ 'class' ];
					if ( isset( $msg[ 0 ][ 1 ][ 'function' ] ) )
						$retDebug .= '::' . $msg[ 0 ][ 1 ][ 'function' ];
					$retDebug .= ": <br />\r";
					$lastfile = $msg[ 0 ][ 1 ][ 'file' ];
				}
				if ( $msg[ 3 ] === helper_Debug::ERROR )
					$retDebug .= '	ERROR: ';
				elseif ( $msg[ 3 ] == helper_Debug::WARNING )
					$retDebug .= '	 WARN: ';
				else
					$retDebug .= '	DEBUG: ';
				$debug .= $msg[ 1 ];
				if ( $msg[ 2 ] !== false )
					$retDebug .= ' (' . $msg[ 2 ] . ')';
				$retDebug .= '<br />' . "\r";
//				var_dump( $msg );
				if ( count( $msg[ 5 ] ) > 0 )
					foreach ( $msg[ 5 ] as $var )
						$retDebug .= self::varDump( $var );
			}
		return $debug;
	}
}
?>