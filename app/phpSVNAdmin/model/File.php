<?php
class model_File
{
	private $path;
	private $contents;
	private $contents_i;

	private $changed = false;

	public function __construct( $path )
	{
		$this->load( $path );
	}

	public function load( $path = false )
	{
		if ( $path )
			$this->path = $path;
		$this->contents = file_get_contents( $path );
		$this->contents_i = 0;
	}

	public function save( )
	{
		return file_put_contents( $this->path, $this->contents );
	}

	public function isChanged( )
	{
		return $this->changed;
	}

	public function exists( )
	{
		return file_exists( $this->path );
	}

	public function getPath( )
	{
		return $this->path;
	}

	public function setPath( $path )
	{
		$this->path = $path;
	}

	public function getContents( )
	{
		return $this->contents;
	}

	public function setContents( $contents )
	{
		$this->contents = $contents;
	}

	public function fetchLine( $index = false )
	{
		if ( !$index )
			$index = $this->contents_i;

		if ( $index >= count( $this->contents ) )
			return false;

		return $this->contents[ $index ];
	}

	public function addContents( $contents )
	{
		if ( !$this->contents )
			$this->contents = array( );
		array_push( $this->contents, $contents );
	}

	public function clearContents( )
	{
		$this->contents = false;
	}
}
?>