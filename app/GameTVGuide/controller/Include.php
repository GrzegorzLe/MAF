<?php
class controller_Include extends controller_Abstract
{
	/** controller name */
	protected $name = 'Include';
	protected $defaultAction = 'indexAction';
	/** map of actions => controller method */
	public static $actions = array( );
	
	public function indexAction( &$ro )
	{
		$rao = new dao_Request( $ro );
		$this->setView( 'Include' );
		$rao->setParam( 'include', $rao->getArg( 1 ) );
		return $rao->getDO( );
	}
}