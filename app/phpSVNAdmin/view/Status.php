<?php
class view_Status extends view_AbstractPage
{
	protected function renderContent( $content )
	{
		$loginBox = new view_User_LoginBox( );
		return 'Status: ' . $content[ 'status' ] . '<br />' . $content[ 'user' ] . $loginBox->render( false );
	}
}
?>