<?php
	switch( $_SERVER[ 'SERVER_NAME' ] )
	{
		case 'phpsvnadmin.localhost':
			$confEnv = 'dev';
			break;
		case 'phpsvnadmin':
			$confEnv = 'dev1';
			break;
		case 'phpsvnadmin.x25.pl':
			$confEnv = 'stg';
			break;
		case 'phpsvnadmin.pl':
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
