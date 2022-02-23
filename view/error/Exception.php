<?php
class view_error_Exception extends view_abstract_View
{
	protected function renderContent( &$ro )
	{
		$e = $ro[ 'exception' ];
		$e = array( 'message' => $e->getMessage( ), 'code' => $e->getCode( ), 'file' => $e->getFile( ), 
				'line' => $e->getLine( ), 'trace' => $e->getTraceAsString( ) );
//		return $contents;
//		return _dbg( )->varDump( $contents );
		$ro->app->issueError( 'Exception occured: ', helper_Debug::ERROR, 0, $e );
		return '<strong>Unexpected exception occured.</strong>';
	}
}
?>
