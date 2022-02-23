<?php
abstract class dao_abstract_DAO
{
	protected $do = false;
	protected static $doClassName;

	public function __construct( &$do = false )
	{
		if ( $do === false )
			$do = new static::$doClassName( );
		$this->setDO( $do );

		if ( !$this->checkDOType( ) )
			throw new Exception( 'Wrong DO type!' );
		return $this;
	}

	public function getDO( )
	{
		return $this->do;
	}
	public function setDO( &$do )
	{
		$this->do = $do;
		if ( !$this->checkDOType( ) )
			$this->do = false;
		return $this->do;
	}
	public static function initDO( )
	{
		$do = new static::$doClassName( );
		$args = func_get_args( );
		$i = 0;
		$props = get_class_vars( static::$doClassName );
		foreach ( $props as $prop => $val )
		{
			if ( count( $args ) <= $i )
				break;
			$do->$prop = $args[ $i++ ];
		}
		return $do;
	}

	public function getDOClassName( )
	{
		return $this->doClassName;
	}
	public function setDOClassName( $doClassName )
	{
		$this->doClassName = $doClassName;
		return true;
	}

	public function checkDOType( )
	{
		if ( $this->do instanceof static::$doClassName )
			return true;
		return false;
	}
}
?>