<?php
require( '../data/config/config.php' );
//require( '../helper/Debug.php' );
//require( '../helper/Timer.php' );
//require( '../helper/SQL.php' );
require( '../../../MAF.php' );

$maf = new MAF( _cfg( 'MAF' ) );
//echo get_include_path( );

/* function __autoload( $class )
{
	$class = strtr( $class, '_', '/' );
	require( '../' . $class . '.php' );
} */

//$_dbg = _dbg( );
_tmr( )->start( 'execution', 'total execution time' );

//$GLOBALS[ '_dbg' ]->issueDebug( 'index.php: $_SERVER:', helper_Debug::VERBOSE, $_SERVER );

_dbg( )->issueDebug( 'config values, query string, post values:', helper_Debug::VERBOSE, $GLOBALS[ '_cfg' ], $_SERVER[ 'QUERY_STRING' ], $_POST );

_tmr( )->start( 'process', 'dispatching' );

//$args = array( );
$args = explode( ',', $_SERVER[ 'QUERY_STRING' ] );
//$args = array( );
$args = array_merge( $args, $_POST );
//echo _dbg->varDump( $args );
$page = array_shift( $args );
if ( empty( $page ) )
{
	_dbg( )->issueDebug( 'empty query string, fallback to default controller.', helper_Debug::DEVEL );
	$page = _cfg( 'PSA', 'DEFAULT_PAGE' );
}

$controller = 'controller_' . $page;
_dbg( )->issueDebug( 'controller used:', helper_Debug::DEVEL, $controller );
//echo _dbg( )->varDump( dao_HTMLHeader::initDO( "Status" ) );

//$_dbg::$timer->processEnd( $tmrSub1 );
//$tmrSub1 = $_dbg::$timer->processStart( 'controller' );
_tmr( )->next( 'process', 'controller creation' );

try
{
	_dbg( )->issueDebug( 'creating controller.', helper_Debug::VERBOSE );
	$controller = new $controller( );

//	$_dbg::$timer->processEnd( $tmrSub1 );
//	$tmrSub1 = $_dbg::$timer->processStart( 'action' );
	_tmr( )->next( 'process', 'action execution' );

	_dbg( )->issueDebug( 'processing action with arguments:', helper_Debug::VERBOSE, $args );
	$viewArgs = $controller->processAction( $args );
	_dbg( )->issueDebug( 'view arguments:', helper_Debug::VERBOSE, $viewArgs );
	$view = 'view_' . $controller->getView( );
	_dbg( )->issueDebug( 'view used:', helper_Debug::DEVEL, $view );

//	$_dbg::$timer->processEnd( $tmrSub1 );
//	$tmrSub1 = $_dbg::$timer->processStart( 'view' );
	_tmr( )->next( 'process', 'view creation' );

	_dbg( )->issueDebug( 'creating view.', helper_Debug::VERBOSE );
	$view = new $view( );
}
catch( Exception $e )
{
//	echo _dbg( )->varDump( $e );
//	echo _dbg( )->renderDebug( );
	$viewArgs = array( 'exception' => $e );
	$view = new view_Error_Exception( );
}

_tmr( )->end( 'process' );
_tmr( )->start( 'render', 'view rendering' );

_dbg( )->issueDebug( 'rendering view.', helper_Debug::VERBOSE );
try
{
	$html = $view->render( $viewArgs );
}
catch ( Exception $e )
{
	$html = _dbg( )->varDump( $e );
}
_dbg( )->issueDebug( 'processing finished.', helper_Debug::DEVEL );

_tmr( )->end( 'render' );

echo $html;

_tmr( )->end( 'execution' );

//echo _sql( )->select( new do_Repository( ) )->from( 'repositories' )->getQuery( );

_tmr( )->start( 'debug', 'debug rendering' );
echo _dbg( )->renderDebug( );
_tmr( )->end( 'debug' );
//echo _dbg( )->varDump( _tmr( ) );

?>
