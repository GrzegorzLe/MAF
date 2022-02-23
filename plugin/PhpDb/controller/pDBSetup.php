<?php
class controller_pDBSetup extends controller_Abstract
{
	protected $defaultAction = 'indexAction';
	public static $actions = array( 'setup' => 'setupAction' );
	
	public function indexAction( &$ro )
	{
		$this->setView( 'pDBSetup' );
	}

	public function setupAction( &$ro )
	{
		$rao = new dao_Request( $ro );
		$this->setView( 'pDBSetup' );
		
	}
}