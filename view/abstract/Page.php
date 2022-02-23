<?php
class view_abstract_Page extends view_abstract_View
{
	// TODO: Fix $content passing, need multiple arguments for initDO( )
	protected function renderHeader( &$ro )
	{
		return ( new view_Header( ) )->render( $ro );
	}

	protected function renderFooter( &$ro )
	{
		return ( new view_Footer( ) )->render( $ro );
	}
}
?>