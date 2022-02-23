<?php
	switch( $_SERVER[ 'SERVER_NAME' ] )
	{
		case 'appsample':
			$confEnv = 'dev';
			break;
		case 'appsample.x25.pl':
			$confEnv = 'stg';
			break;
		case 'appsample.pl':
			$confEnv = 'live';
			break;
	}

	if ( !isset( $confEnv ) )
	switch( $_SERVER[ 'SERVER_ADDR' ] . ':' . $_SERVER[ 'SERVER_PORT' ] )
	{
		default:
			$confEnv = 'dev';
			break;
	}
?>
