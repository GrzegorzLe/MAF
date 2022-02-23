<?php
class view_Error_NotFound extends view_Abstract_Page
{
	protected function renderContent( $contents )
	{
		return _dbg( )->varDump( $content );
	}
}
?>
