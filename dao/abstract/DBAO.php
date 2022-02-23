<?php
abstract class dao_abstract_DBAO extends dao_abstract_DAO
{
	protected static $dbTableName;
	protected static $dbFieldAlias;
	protected static $dbForeignKeys = array( );
//	protected static $dbIDField;
	protected $modified = array( );

	public function __construct( $do = false )
	{
		if ( is_array( $do ) )
			return $this->selectWhere( $do );

		return parent::__construct( $do );
	}

	// TODO: it'd be great if i'd remember what's this for :D
	public function initDBO( )
	{
//		$do = new static::$doClassName( );

//		_sql( )->select( $do )->from( static::$dbTableName );
		$where = '';
		$select = '';
		$ssep = '';
		$wsep = '';

		$args = func_get_args( );
		if ( count( $args ) === 0 )
			return false;

		$i = 0;
		$props = get_class_vars( static::$doClassName );
		foreach ( $props as $prop => $val )
		{
			$select .= $ssep . $prop;
			if ( count( $args ) <= $i && $args[ $i ] !== false )
			{
				$where .= $wsep . $prop . ' = ' . $args;
				$wsep = ', ';
			}
			$ssep = ', ';
		}
	}

	protected function setDOProperty( $property, $value )
	{
		$this->do->$property = $value;
		$this->modified[ $property ] = true;
		return true;
	}

	public function update( )
	{
		// any other way to get first member of $this->do ??
		foreach ( $this->do as $field => $value )
//			return _sql( )->update( static::$dbTableName )->set( $this->do )->where( $field, $value )->runQuery( model_DB::create( _tmr( ) ) );
			return _sql( )->update( static::$dbTableName )->set( $this->do )->where( $field . ' = ' . $value )->getQuery( );
	}

	public function insert( )
	{
//		return _sql( )->insert( )->into( static::$dbTableName, $this->do )->values( $this->do )->runQuery( model_DB::create( _tmr( ) ) );
		return _sql( )->insert( )->into( static::$dbTableName, $this->do )->values( $this->do )->getQuery( );
	}

	public function select( )
	{
		if ( $this->do === false )
//			$this->do = new static::$doClassName( );
			_dbg( )->issueError( );

	//	return _sql( )->select( $this->do )->from( static::$dbTableName )->where( $this->do )->runQuery( model_DB::create( _tmr( ) ) );
	//	return _sql( )->select( $this->do )->from( static::$dbTableName )->where( $this->do )->getQuery( );
		$result = _sql( )->select( $this->do )->from( static::$dbTableName )->where( $this->do )->runQuery( model_DB::create( _tmr( ) ) );
		if ( $result && !empty( $result ) )
			$this->do = $result->getObject( static::$doClassName );
			
	//	return _sql( )->select( $this->do )->from( static::$dbTableName )->where( $this->do )->getQuery( );
//		return $this->do;
		return $this;
	}

	public function selectWhere( $where )
	{
		$result = _sql( )->select( $this->do )->from( static::$dbTableName )->where( $where )->runQuery( model_DB::create( _tmr( ) ) );
		if ( $result && !empty( $result ) )
			$this->do = $result->getObject( static::$doClassName );
		return $this;
	}
}
?>
