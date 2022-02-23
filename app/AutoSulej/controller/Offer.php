<?php
class controller_Offer extends controller_ASAbstract
{
	/** controller name */
	protected $name = 'Offer';
	protected $defaultAction = 'indexAction';
//	public static $actions = array( 'blacharstwo' => 'indexAction', mechani );
//	protected $includes = array(  );
	
	protected function indexAction( &$ro )
	{
		$rao = new dao_Request( $ro );
		$this->setView( 'Offer' );
		$rao->setParam( 'include', 'oferta-' . $rao->getArg( 1 ) );
		return $rao->getDO( );
	}
}
