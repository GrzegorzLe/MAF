<?php
class dao_Login extends dao_abstract_DBAO
{
	// DAO statics
	protected static $doClassName = 'do_Login';

	// DBAO statics
	protected static $dbTableName = 's_login';
	protected static $dbForeignKeys = array( 'User' => array( 'userId', 'dao_User' ), 'ModBy' => array( 'modBy', 'dao_Login' ) );

	public function getLoginId( )
	{
		return $this->do->loginId;
	}
	public function setLoginId( $loginId )
	{
		$this->do->loginId = $loginId;
		return true;
	}
	
	public function getUserId( )
	{
		return $this->do->userId;
	}
	public function setUserId( $userId )
	{
		$this->do->userId = $userId;
		return true;
	}

	public function getLoginName( )
	{
		return $this->do->loginName;
	}
	public function setLoginName( $loginName )
	{
		$this->do->loginName = $loginName;
		return true;
	}

	public function getPassword( )
	{
		return $this->do->password;
	}
	public function setPassword( $password )
	{
		$this->do->password = $password;
		return true;
	}
	public function verifyPassword( $password )
	{
		$md5Password = _sql( )->select( "MD5('$password')" )->runQuery( model_DB::create( _tmr( ) ) )->getField( );;//
		_dbg( )->issueDebug( 'verifying password:', helper_Debug::VERBOSE, $md5Password, $this->getPassword( ) );
		return $md5Password == $this->getPassword( );
	}

	public function getActive( )
	{
		return $this->do->active;
	}
	public function setActive( $active )
	{
//		$this->do->active = $active;
		$this->setDOProperty( 'active', $active );
		return true;
	}

	public function getModDate( )
	{
		return $this->do->modDate;
	}
	public function setModDate( $modDate )
	{
		// TODO: Password strength check
		$this->do->modDate = $modDate;
		return true;
	}

	public function getModBy( )
	{
		return $this->do->modBy;
	}
	public function setModBy( $modBy )
	{
		// TODO: Password strength check
		$this->do->modBy = $modBy;
		return true;
	}
}
?>