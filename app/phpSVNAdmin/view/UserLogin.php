<?php
class view_UserLogin extends view_AbstractPage
{
	protected function renderContent( $content )
	{
		if ( $content[ 'loginFailed' ] === true )
			$msg = '<span class="text-red">Login and/or password is incorrect.</span><br />';
		return $msg . '<form method="post" action="/phpsvnadmin/User/login">Login:<input type="text" name="login" /><br />Pass:<input type="password" name="password" /><br /><input type="submit" value="Login" /></form>';
	}
}
?>
