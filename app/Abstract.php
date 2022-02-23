<?php
class app_Abstract extends Core
{
	protected static $controllers = array( );

	public function bootstrap( &$ro )
	{
		$rao = new dao_Request( $ro );
		$rao->setApp( $this );
		$args = explode( '/', $rao->getGet( ) );
		if ( count( $args ) < 2 && $this->getConfig( 'FORCE_DEFAULT_PAGE' ) )
			$page = $this->getConfig( 'DEFAULT_PAGE' );
		else
			$page = array_shift( $args );
		if ( empty( $page ) )
		{
			$this->debug( 'empty query string, fallback to default controller.', 'DEVEL' );
			$page = $this->getConfig( 'DEFAULT_PAGE' );
		}
		$rao->setArg( $page );
//		else 
		if ( array_key_exists( $page, static::$controllers ) )
			$page = static::$controllers[ $page ];
		$rao->setController( $page );
		$action = array_shift( $args );
		$page = 'controller_' . $page;
		if ( empty( $action ) )
		{
			$this->debug( 'empty query string, fallback to default action.', 'DEVEL' );
			$action = $this->getConfig( 'DEFAULT_ACTION' );
		}
		$rao->setArg( $action );
		if ( $action != $this->getConfig( 'DEFAULT_ACTION' ) && !array_key_exists( $action, $page::$actions ) )
			{
			$this->debug( 'action not defined, fallback to default action.', 'DEVEL' );
			$action = $this->getConfig( 'DEFAULT_ACTION' );
		}
		$rao->setAction( $action );
	}

	public function run( $get, $post = false )
	{
		$ro = dao_Request::initDO( $get, $post );
		$this->bootstrap( $ro );
		$rao = new dao_Request( $ro );
		$controller = 'controller_' . $rao->getController( );
		$this->debug( 'controller used:', 'DEVEL', $controller, $rao->getDO( ) );
		try
		{
			$this->debug( 'creating controller.', 'VERBOSE' );
			$controller = new $controller( $this->getConfig( 'DEFAULT_ACTION' ) );
			$this->debug( 'processing action with arguments:', 'VERBOSE', $rao->getDO( ) );
			$viewArgs = $controller->processAction( $ro );
			$this->debug( 'view arguments:', 'VERBOSE', $rao->getDO( ) );
			$view = 'view_' . $controller->getView( );
			$this->debug( 'view used:', 'DEVEL', $view );
			$this->debug( 'creating view.', 'VERBOSE' );
			$view = new $view( );
		}
		catch( Exception $e )
		{
			$viewArgs = array( 'exception' => $e );
			$view = new view_error_Exception( );
		}
		$this->debug( 'rendering view.', 'VERBOSE' );
		try
		{
			echo $view->render( $viewArgs );
		}
		catch ( Exception $e )
		{
			echo $this->dump( $e );
		}
	}
}