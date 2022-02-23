<?php
class view_User_LoginPage extends view_AbstractPage
{
	protected function renderContent( $content )
	{
		$msg = '';
		if ( array_key_exists( 'loginFailed', $content ) && $content[ 'loginFailed' ] === true )
			$msg = '<span class="text-red">Login and/or password is incorrect.</span><br />';
		return $msg . '<form method="post" action="/index.php?User,login">Login:<input type="text" name="login" /><br />Pass:<input type="password" name="password" /><br /><input type="submit" value="Login" /></form>';
	}
}
?>
