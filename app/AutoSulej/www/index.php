<?php
require( '../data/config/config.php' );
require( '../../../MAF.php' );

$maf = new MAF( MAF::_cfg( 'MAF' ) );

$maf->debug( 'config values, query string, post values:', 'VERBOSE', MAF::_cfg( ), $_SERVER[ 'QUERY_STRING' ], $_POST );

$app = $maf->getApp( );
try
{
	$app->run( $_SERVER[ 'QUERY_STRING' ], $_POST );
}
catch ( Exception $e )
{
	echo $map->dump( $e );
}

//echo $maf->dump( $_REQUEST[ '_debug' ] );
$dbg = $maf->getApp( 'Debug' );
if ( $dbg )
	$dbg->run( );
