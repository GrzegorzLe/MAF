<?php
class view_Include extends view_abstract_View
{
	public function renderContent( &$ro )
	{
		$rao = new dao_Request( $ro );
		$include = $rao->getParam( 'include' );
		ob_start( );
		include 'app/GameTVGuide/www/include/' . $include . '.php';
		return ob_get_clean( );
	}

	protected function renderFooter( &$ro )
	{
		return ( new view_abstract_Footer )->render( $ro );
	}
}
