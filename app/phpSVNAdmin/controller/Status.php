<?php
class controller_Status extends controller_Abstract
{
	/** controller name */
	protected $name = 'Status';

	protected function indexAction( )
	{
		$GLOBALS[ '_dbg' ]->issueDebug( 'setting view.', helper_Debug::VERBOSE );

		$this->setView( 'Status' );

		$user = new dao_Login( );

		return array( 'status' => 'BUU', 'user' => $user->select( )->getLoginName( ) );
	}
}
?>
