<?php
/**
 * Abstract parent class for HTML classes which use name property
 *
 * Extends HTMLAbstract class: {@inheritdoc } In addition defines name property.
 *
 * @abstract
 * @package MAF
 * @subpackage HTMLHelper
 * @uses HTMLAbstract
 * @author Grzegorz Lesniewski
 * @copyright Grzegorz Lesniewski 2009-2012
 */
abstract class HTMLAbstractNamed extends HTMLAbstract
{
	protected $contents = array( );
	protected $properties = array( 'id' => false, 'name' => false, 'class' => false, 'style' => false, 'title' => false );

	public function __construct( $id = false, $name = false, $class = false, $style = false, $title = false )
	{
		parent::__construct( $id, $class, $style, $title );
		$this->properties[ 'name' ] = $name;
	}

	public function setName( $name )
	{
		$this->setProperty( 'name', $name );
	}
}
?>