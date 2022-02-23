<?php
/**
 * Debug helper variable renderer, returns HTML Table format
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
class helper_debug_variable_HTMLTableRenderer extends helper_debug_variable_AbstractRenderer
{
	public static function render( $var, $key = false, $oneLiner = false )
	{
		$varType = gettype( $var );
		$retVal = '';
		if ( !in_array( $varType, static::$supportedTypes ) )
			$varType = 'unknown';

		$varRenderer = 'render' . ucfirst( $varType );

		if ( array_key_exists( $varType, static::$subtypedTypes ) )
		{
			$varSubtype = static::$subtypedTypes[ $varType ];
			$varSubtype = $varSubtype( $var );
		}
		else
			$varSubtype = '';
		
		if ( $key === false )
			$retVal .= static::renderTableStart( $varType, $oneLiner && in_array( $varType, static::$simpleTypes ), $varSubtype );
		elseif ( !in_array( $varType, static::$simpleTypes ) )
		{
			$varRenderer = 'render';
			$varSubtype = false;
		}

		if ( $key !== false )
			$retVal .= '<tr><td class="_dbg_var_key _dbg_var_' . $varType . '_key" onclick="_dbg_toggleRow( this )" title="click to collapse">' . $key . '</td><td>';
		elseif ( $oneLiner && in_array( $varType, static::$simpleTypes ) )
			$retVal .= '<td title="' . $varType . '">';
		elseif ( in_array( $varType, static::$simpleTypes ) )
			$retVal .= '<tr><td title="' . $varType . '">';

		$retVal .= static::$varRenderer( $var, $varSubtype );

		if ( $key !== false || in_array( $varType, static::$simpleTypes ) )
			$retVal .= '</td></tr>' . "\r";

		if ( $key === false )
			$retVal .= static::renderTableEnd( $varType );

		return $retVal;
	}

	public static function renderBoolean( $var, $subtype = '' )
	{
		return $var ? 'true' : 'false';
	}

	public static function renderInteger( $var, $subtype = '' )
	{
		return $var;
	}

	public static function renderDouble( $var, $subtype = '' )
	{
		return $var;
	}

	public static function renderString( $var, $subtype = '' )
	{
		return $var;
	}

	public static function renderArray( $var, $subtype = '' )
	{
		$retVal = '';
		foreach ( $var as $k => $v )
			$retVal .= static::render( $v, $k );
		return $retVal;
	}

	public static function renderObject( $var, $subtype = '' )
	{
		$retVal = '';
		foreach ( $var as $k => $v )
			$retVal .= static::render( $v, $k );
		return $retVal;
	}

	public static function renderResource( $var, $subtype = '' )
	{
		$subRenderer = 'helper_debug_variable_resource_HTMLTable' . str_replace( ' ', '', ucwords( $subtype ) );
		if ( $subtype != '' and class_exists( $subRenderer, true ) )
			return $subRenderer::render( $var );
//		return 'Resource(' . get_resource_type( $var ) . ')';
	}

	public static function renderNULL( $var, $subtype = '' )
	{
		return 'null';
	}

	public static function renderUnknown( $var, $subtype = '' )
	{
		return 'Unknown(' . var_export( $var, true ) . ')';
	}

	private static function renderTableStart( $type, $oneLiner = false, $subtype = '' )
	{
		$toggle = $oneLiner ? 'Row' : 'Table';
		$subtype = $subtype === '' ? '' : ' (' . $subtype . ')';
		$table = <<<TABLE
<table class="_dbg_var _dbg_var_$type">
<tr><th class="_dbg_var_header _dbg_var_{$type}_header" onclick="_dbg_toggle$toggle( this )" title="click to collapse" colspan="99">$type$subtype</th>
TABLE;
		if ( !$oneLiner )
			$table .= '</tr>' . "\r";
		return $table;
	}

	private static function renderTableEnd( $type )
	{
		return '</table>' . "\r";
	}
}
?>