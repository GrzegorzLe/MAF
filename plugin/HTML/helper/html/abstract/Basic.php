<?php
/**
 * Abstract basic parent class for HTML classes
 *
 * Defines ArrayAccess functions and toString( ) function.
 *
 * @abstract
 * @package MAF
 * @subpackage HTMLHelper
 * @uses HTMLHelper:a2hp()
 * @author Grzegorz Lesniewski
 * @copyright Grzegorz Lesniewski 2009-2012
 */
abstract class Basic implements ArrayAccess
{
	protected $tagName = "";
	protected $contents = array( );
	protected $attributes = array( );
	/**
	 * ArrayAccess interface functions
	 */
	public function __contruct( $tagName )
	{
		$this->tagName = $tagName;
		return $this;
	}
	public function offsetExists ( mixed $offset )
	{
		return empty( $this->attributes[ $offset ] );
	}
	public function offsetGet ( mixed $offset )
	{
		return $this->attributes[ $offset ];
	}
	public function offsetSet ( mixed $offset , mixed $value )
	{
		$this->attributes[ $offset ] = $value;
	}
	public function offsetUnset ( mixed $offset )
	{
		unset( $this->attributes[ $offset ] );
	}
	public function toString( )
	{
		$returnValue = '<' . $this->tagName;
		$returnValue .= helper_HTML::a2hp( $this->attributes );
		if ( empty( $this->contents ) )
			$returnValue .= ' />' . "\r";
		else
		{
			$returnValue .= '>' . "\r";
			foreach ( $this->contents as $element )
				$returnValue .= $element->toString( );
			$returnValue .= '</' . $this->tagName . '>' . "\r";
		}
		return $returnValue;
	}
}