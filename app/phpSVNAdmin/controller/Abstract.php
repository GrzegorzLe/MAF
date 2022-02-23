<?php
/**
 * Basic controller class
 *
 * @abstract
 * @author Lesio
 * @copyright Grzegorz Lesniewski 2009
 */
class controller_Abstract
{
	/** controller name */
	protected $name = 'Abstract';
	/** view to return */
	protected $view = null;
	/** map of actions => controller method */
	protected $actions = array( );

	/**
	 * Gets controller method associated with given action
	 * @param string $action action for which controller method should be returned
	 * @return string method accociated to given action
	 */
	protected function getActionMethod( $action )
	{
		if ( array_key_exists( $action, $this->actions ) )
			return $this->actions[ $action ];
		elseif ( method_exists( $this, $action . $this->name ) )
			return $action . $this->name;
		else
			return false;
	}

	/**
	 * Basic fuction processing an action
	 * @param array $args array with action and action arguments
	 * @return void
	 */
	public function processAction( $args )
	{
		$action = array_shift( $args );
		$method = $this->getActionMethod( $action );
		if ( !$method )
			$method = _cfg( 'PSA', 'DEFAULT_ACTION' );
//		var_dump( $action, $method, $args );
		return $this->{ $method }( $args );
	}

	private function resetAction( )
	{
		$this->setView( null );
	}

	protected function indexAction( )
	{
		global $cfg;

		$this->setView( $cfg[ 'DEFAULT_VIEW' ] );
		throw( new Exception( 'Unhandled default action!' ) );
	}

	public function getView( )
	{
		return $this->view;
	}
	protected function setView( $view )
	{
		$this->view = $view;
		return true;
	}
}
?>
