<?php
class model_Repository
{
	private $htAccess;
	private $name;
	private $owner;
	private $path;
	private $url;

	public function __construct( $name )
	{
		$this->setName( $name );
		$this->setPath( $cnf[ 'SVN_REPOS' ] . '/' . $this->owner->getName( ) . '/' . $name );
		if ( file_exists( $this->getUrl( ) ) )
			$this->load( );
		else
			$this->create( );
	}

	public function create( )
	{
		mkdir( $this->getPath( ), 0755, false );
		$cmd = 'svnadmin create file://' . $cnf[ 'SVN_REPOS' ] . '/' . $this->owner->getName( ) . '/' . $name;
		exec( $cmd, $out, $ret );
	}

	public function import( $path )
	{
		$cmd = 'svn import ' . $path . ' ' . $this->getUrl( );
		exec( $cmd, $out, $ret );
	}

	public function setPath( $path )
	{
		$this->path = $path;
	}
	public function getPath( )
	{
		return $this->path;
	}
	public function getUrl( )
	{
		return 'file://' . $this->path;
	}

	public function setName( $name )
	{
		$this->name = $name;
	}
	public function getName( )
	{
		return $this->name;
	}
}
?>
