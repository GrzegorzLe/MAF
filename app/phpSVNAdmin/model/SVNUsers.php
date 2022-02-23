<?php
class model_SVNUsers extends model_File
{
	private $svnusers = array( );

	public function __construct( )
	{
		parent::__construct( $cnf[ 'SVN_USERS' ] );
		if ( !$this->exists( ) )
			throw new Exception( 'svnusers file not found!', 10 );
		$this->parse( );
	}

	public function save( )
	{
		return false;
	}

	private function parse( )
	{
		while ( $line = $this->fetchLine( ) )
		{
			list( $user ) = explode( ':', $line );
			array_push( $this->svnusers, $user );
		}
	}

	public function getUsers( )
	{
		return $this->svnusers;
	}

	public function addUser( $login, $pass )
	{
		$cmd = '/usr/sbin/htpasswd -b /var/svn/conf/svnusers ' . escapeshellcmd( $login ) . ' ' . escapeshellcmd( $pass );
		exec( $cmd, $out, $ret );
	}

	public function removeUser( $login )
	{
		$cmd = '/usr/sbin/htpasswd -D /var/svn/conf/svnusers ' . escapeshellcmd( $login );
		exec( $cmd, $out, $ret );
	}
}
?>