<?php
class view_Abstract_Basic extends view_Abstract_View
{
	protected static $elementProperties = array( 'type', 'value', 'name', 'props' );

	protected function renderElement( $element )
	{
	 	if ( is_string( $element ) && method_exists( $this, 'render' . ucfirst( $element ) ) )
	 		return call_user_method( 'render' . ucfirst( $element ), $this );
	 	elseif ( is_array( $element ) && is_null( $element[ 'type' ] ) && !is_null( $element[ 0 ] ) )
	 		return call_user_method( 'render' . ucfirst( $element[ 0 ] ), array_combine( static::$elementProperties, $element ) );
	 	elseif ( is_array( $element ) && !is_null( $element[ 'type' ] ) )
	 		return call_user_method( 'render' . ucfirst( $element[ 'type' ] ), $element );
	 	else
	 		_dbg( )->issueError( 'Element type not specified!', helper_Debug::DEVEL, helper_debug_EC::ELEMENT_TYPE_NOT_SPECIFIED );
	}

	protected function renderLabel( $element )
	{
		if ( !empty( $element[ 'props' ] ) )
			return '<span' . static::a2hp( $element[ 'props' ] ) . '>' . $element[ 'value' ] . '</span>';
		else
			return $element[ 'value' ]; 
	}

	protected function renderText( $element )
	{
		if ( !empty( $element[ 'props' ] ) )
			return '<span' . static::a2hp( $element[ 'props' ] ) . '>' . $element[ 'value' ] . '</span>';
		else
			return $element[ 'value' ]; 
	}

	protected function renderInclude( $element )
	{
		
	}
}
?>
