<?php
/**
 * Abstract parent class for HTML classes
 *
 * Defines basic HTML properties (id, class, style, title), getters and setters for them, toString( ) function.
 *
 * @abstract
 * @package MAF
 * @subpackage HTMLHelper
 * @uses HTMLHelper:a2hp()
 * @author Grzegorz Lesniewski
 * @copyright Grzegorz Lesniewski 2009-2012
 */
abstract class HTMLAbstract
{
	protected $contents = array( );
	protected $properties = array( 'id' => false, 'class' => false, 'style' => false, 'title' => false );

	public function __construct( $id = false, $class = false, $style = false, $title = false )
	{
		$this->properties[ 'id' ] = $id;
		$this->properties[ 'class' ] = $class;
		$this->properties[ 'style' ] = $style;
		$this->properties[ 'title' ] = $title;
	}

	public function setProperty( $property, $value )
	{
		$this->properties[ $property ] = $value;
	}

	public function setId( $id )
	{
		$this->setProperty( 'id', $id );
	}

	public function setClass( $class )
	{
		$this->setProperty( 'class', $class );
	}

	public function setStyle( $style )
	{
		$this->setProperty( 'style', $style );
	}

	public function toString( )
	{
		$returnValue = '<' . $this->tagName;
		$returnValue .= helper_HTML::a2hp( $this->properties );
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
?>