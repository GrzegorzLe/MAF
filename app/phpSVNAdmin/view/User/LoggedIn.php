<?php
class view_User_LoggedIn extends view_AbstractPage
{
	protected function renderContent( $content )
	{
		return 'Hello again ' . $content[ 'username' ] . '! You have been successfully logged in!';
	}
}
?>
