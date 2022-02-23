<?php
class view_AbstractPage extends view_Abstract
{
	// TODO: Fix $content passing, need multiple arguments for initDO( )
	protected function renderHeader( $contents )
	{
		$header = new view_Header( );
		return $header->render( dao_HTMLHeader::initDO( $contents ) );
	}

	protected function renderFooter( $contents )
	{
		$footer = new view_Footer( );
		return $footer->render( array( ) );
	}
}
?>