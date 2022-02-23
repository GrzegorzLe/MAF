<?php
class view_Offer extends view_Page
{
	public function renderContent( &$ro )
	{
		$rao = new dao_Request( $ro );
		$dsp = $rao->getParam( 'display' );
		$includes = $rao->getParam( 'include' );
		ob_start( );
		include 'app/AutoSulej/www/include/' . $includes . '.php';
		return ob_get_clean( );
	}
}