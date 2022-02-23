<?php
class view_error_Error extends view_abstract_Page
{
	protected function renderContent( &$ro )
	{
		return $ro->app->varDump( $ro );
	}
}
?>
