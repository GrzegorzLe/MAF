<?php
class view_abstract_View
{
	private $defaultController = "controller_Abstract";

	public function __construct( )
	{
		// get all of constructor arguments
		$args = func_get_args( );
		// get all of object instance properties
		// TODO: make sure that order in array corresponds to declaration order
		$vars = get_object_vars( $this );
		// loop over both arrays and assign constructor arguments to object properties
		$i = 0;
		foreach( $vars as $var => $val )
		{
			if ( $i >= count( $args ) )
				break;
			$this->$var = $args[ $i++ ];
		}
		return $this;
	}
	
	public function preRender( &$ro )
	{
		return $ro;
	}

	public function render( &$ro )
	{
		$this->preRender( $ro );

		$retPage = $this->renderHeader( $ro );
		$retPage .= $this->renderContent( $ro );
		$retPage .= $this->renderFooter( $ro );

		return $retPage;
	}

	protected function renderHeader( &$ro )
	{
		return '';
	}

	protected function renderContent( &$ro )
	{
		return '';
	}

	protected function renderFooter( &$ro )
	{
		return '';
	}

	public static function a2hp( $array )
	{
		$propsString = '';
		if ( isset( $array ) && !empty( $array ) )
			foreach ( $array as $i => $v )
				$propsString .= " $i='$v'";
		return $propsString;
	}
}
?>