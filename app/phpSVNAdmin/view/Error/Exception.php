<?php
class view_Error_Exception extends view_Abstract_Page
{
	protected function renderContent( $contents )
	{
		$e = $contents[ 'exception' ];
		$e = array( 'message' => $e->getMessage( ), 'code' => $e->getCode( ), 'file' => $e->getFile( ), 
				'line' => $e->getLine( ), 'trace' => $e->getTraceAsString( ) );
//		return $contents;
//		return _dbg( )->varDump( $contents );
		_dbg( )->issueError( 'Exception occured: ', helper_Debug::ERROR, 0, $e );
		return '<strong>Unexpected exception occured.</strong>';
	}
}
?>
