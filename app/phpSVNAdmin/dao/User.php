<?php
class dao_User extends dao_abstract_DBAO
{
	// DAO statics
	protected static $doClassName = 'do_User';

	// DBAO statics
	protected static $dbTableName = 's_user';

	public function getUserId( )
	{
		return $this->do->userId;
	}
	public function setUserId( $userId )
	{
		$this->do->userId = $userId;
		return true;
	}
	
	public function getFirstName( )
	{
		return $this->do->firstName;
	}
	public function setFirstName( $firstName )
	{
		$this->do->firstName = $firstName;
		return true;
	}

	public function getLastName( )
	{
		return $this->do->lastName;
	}
	public function setLastName( $lastName )
	{
		$this->do->lastName = $lastName;
		return true;
	}
	
	public function getActive( )
	{
		return $this->do->active;
	}
	public function setActive( $active )
	{
		// TODO: Password strength check
		$this->do->active = $active;
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