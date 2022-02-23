<?php
require( '../data/config/config.php' );
require( '../../../MAF.php' );

$maf = new MAF( _cfg( 'MAF' ) );

_dbg( )->issueDebug( 'config values, query string, post values:', helper_Debug::VERBOSE, $GLOBALS[ '_cfg' ], $_SERVER[ 'QUERY_STRING' ], $_POST );

//$maf->run( $_SERVER[ 'QUERY_STRING' ], $_POST );
$app = $maf->getApp( 'GameTVGuide' );
$app->run( $_SERVER[ 'QUERY_STRING' ], $_POST );

$dbg = $maf->getApp( 'Debug' );
if ( $dbg )
	$dbg->run( );

