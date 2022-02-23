<?php
class view_pDBSetup extends view_abstract_View
{
	public function renderContent( &$ro )
	{
		ob_start( );
		echo 'This is pDB setup page.
				';
		return ob_get_clean( );
	}
}
