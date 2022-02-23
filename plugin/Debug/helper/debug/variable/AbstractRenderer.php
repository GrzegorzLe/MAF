<?php
/**
 * Debug helper variable abstract renderer
 * 
 * @author Grzegorz Lesniewski
 * @copyright Grzegorz Lesniewski 2009-2010

	Copyright 2009-2012 Grzegorz Lesniewski <grzegorz.lesniewski@gmail.com>

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
interface helper_debug_variable_IRenderer
{
	static function renderBoolean( $var );
	static function renderInteger( $var );
	static function renderDouble( $var );
	static function renderString( $var );
	static function renderArray( $var );
	static function renderObject( $var );
	static function renderResource( $var );
	static function renderNULL( $var );
}

abstract class helper_debug_variable_AbstractRenderer implements helper_debug_variable_IRenderer
{
	protected static $supportedTypes = array( 'boolean', 'integer', 'double', 'string', 'array', 'object', 'resource', 'NULL' );
	protected static $simpleTypes = array( 'boolean', 'integer', 'double', 'string', 'NULL' );
	protected static $subtypedTypes = array( 'object' => 'get_class', 'resource' => 'get_resource_type' );

	public static function render( $var )
	{
		$varType = gettype( $var );
		if ( $varType == 'object' )
		{
			$varClass = 'helper_debug_variable_object_' . get_class( $var );
			if ( class_exists( $varClass ) )
				return $varClass::render( $var );
		}
		if ( !in_array( $varType, static::$supportedTypes ) )
			$varType = 'unknown';

		$varType = 'render' . ucfirst( $varType );
		return self::$varType( $var );
	}
}
?>