<?php
class dao_Request extends dao_abstract_DAO
{
	protected static $doClassName = 'do_Request';

	public function getGet( )
	{
		return $this->do->get;
	}
	public function setGet( $get )
	{
		$this->do->get = $get;
		return true;
	}

	public function getPost( )
	{
		return $this->do->post;
	}
	public function setPost( $post )
	{
		$this->do->post = $post;
		return true;
	}
	
	public function getController( )
	{
		return $this->do->controller;
	}
	public function setController( $controller )
	{
		$this->do->controller = $controller;
		return true;
	}
	
	public function getAction( )
	{
		return $this->do->action;
	}
	public function setAction( $action )
	{
		$this->do->action = $action;
		return true;
	}
	
	public function getApp( )
	{
		return $this->do->app;
	}
	public function setApp( &$app )
	{
		$this->do->app = $app;
		return true;
	}
	
	public function getParam( $key )
	{
		if ( !array_key_exists( $key, $this->do->params ) )
			return false;
		return $this->do->params[ $key ];
	}
	public function setParam( $key, $param )
	{
		$this->do->params[ $key ] = $param;
		return true;
	}
	
	public function getParams( )
	{
		return $this->do->params;
	}
	public function setParams( &$params )
	{
		$this->do->params = $params;
		return true;
	}

	public function getArg( $index )
	{
		if ( !array_key_exists( $index, $this->do->args ) )
			return false;
		return $this->do->args[ $index ];
	}
	public function setArg( $value )
	{
		$this->do->args[ ] = $value;
		return true;
	}
	
	public function getArgs( )
	{
		return $this->do->args;
	}
	public function setArgs( &$args )
	{
		$this->do->params = $args;
		return true;
	}
}