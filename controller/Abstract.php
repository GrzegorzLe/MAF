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
	protected $alias = 'abstract';
	/** view to return */
	protected $view = null;
	protected $defaultAction = '';
	/** map of actions => controller method */
	public static $actions = array( );

	public function __construct( $defaultAction )
	{
		// TODO: check if constructor was invoked w/o default action
		if ( $this->defaultAction == '' )
			$this->defaultAction = $defaultAction;
		return $this;
	}	

	/**
	 * Performs basic preprocessing universal for all actions before calling action method itself
	 * @param array &$ro request object
	 * @return void
	 */
	public function preProcess( &$ro )
	{
		return;
	}
	
	/**
	 * Gets controller method associated with given action
	 * @param string $action action for which controller method should be returned
	 * @return string method accociated to given action
	 */
	protected function getActionMethod( $action )
	{
		if ( array_key_exists( $action, static::$actions ) )
			return static::$actions[ $action ];
		elseif ( method_exists( $this, $action . $this->name ) )
			return $action . $this->name;
		else
			return $this->defaultAction;
	}

	/**
	 * Basic fuction processing an action
	 * @param array &$ro request object
	 * @return void
	 */
	public function processAction( &$ro )
	{
		$this->preProcess( $ro );
		$rao = new dao_Request( $ro );
		$action = $rao->getAction( );
		$method = $this->getActionMethod( $action );
		if ( !$method )
			throw( new Exception( 'Unhandled action "' . $action . '"!' ) );
		//		var_dump( $action, $method, $args );
		return $this->{ $method }( $ro );
	}

	private function resetAction( )
	{
		$this->setView( null );
	}

	// TODO: need redesign
	protected function indexAction( &$ro )
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
