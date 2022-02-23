<?php
	switch( $_SERVER[ 'SERVER_NAME' ] )
	{
		case 'autosulej':
			$confEnv = 'dev';
			break;
		case 'autosulej.x25.pl':
			$confEnv = 'stg';
			break;
		case 'autosulej.pl':
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
