<?php
class controller_User extends controller_Abstract
{
	protected $name = 'User';

	protected function indexAction( )
	{
		return $this->loginUser( false );
	}
	/**
	 * Checks if user has rights to add users and displays new user view
	 * If recieved $_POST data adds new entry to svnusers file and stores data in the DB
	 */
	protected function newUser( $args )
	{

	}

	/**
	 * Checks if user has rights to remove users and displays delete user view
	 * If recieved $_POST data removes entry from svnusers file and marks user as deleted
	 */
	protected function deleteUser( )
	{

	}

	protected function editUser( )
	{

	}

	protected function loginUser( $args )
	{
		if ( empty( $args ) )
		{
			_dbg( )->issueDebug( 'setting view to:', helper_Debug::VERBOSE, 'UserLogin' );
			$this->setView( 'User_LoginPage' );
		}
		else
		{
			_dbg( )->issueDebug( 'logging in user with args:', helper_Debug::VERBOSE, $args );
//			$login = new dao_Login( dao_Login::initDO( null, null, $args[ 'login' ] ) );
//			$login = new dao_Login( );
//			$login->setLoginName( $args[ 'login' ] );
//			_dbg( )->issueDebug( 'login prequery:', helper_Debug::DEVEL, $login->getDO( ) );
//			$login->select( );
//			$login->selectWhere( array( 'loginname' => $args[ 'login' ] ) );
			$login = new dao_Login( array( 'loginname' => $args[ 'login' ] ) );
			_dbg( )->issueDebug( 'login query results:', helper_Debug::DEVEL, $login );
			$this->setView( 'User_LoginPage' );
//			return array( );

			if ( $login->verifyPassword( $args[ 'password' ] ) )
			{
				_dbg( )->issueDebug( 'user logged in:', helper_Debug::DEVEL, $login->getLoginName( ) );
				$this->setView( 'User_LoggedIn' );
				$user = new dao_User( );
				$user->setUserId( $login->getUserId( ) );
				$user->select( );
				return array( 'username' => $user->getFirstName( ) . ' ' . $user->getLastName( ) );
			}
			else
			{
				_dbg( )->issueDebug( 'logging in failed, login/pass incorrect:', helper_Debug::DEVEL, $args[ 'login' ], $args[ 'password' ] );
//				$this->setView( 'User_LoginPage' );
				return array( 'loginFailed' => true );
			}
		}
		return array( );
	}

	protected function logoutUser( )
	{
		_dbg( )->issueDebug( 'user have been logged out:', helper_Debug::VERBOSE );
		$this->setView( 'User_LoggedOut' );
		return array( );
	}
}
?>