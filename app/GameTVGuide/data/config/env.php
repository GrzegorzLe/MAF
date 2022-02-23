<?php
	switch( $_SERVER[ 'SERVER_NAME' ] )
	{
		case 'gtvg.localhost':
			$confEnv = 'dev';
			break;
		case 'gtvg':
			$confEnv = 'dev1';
			break;
		case 'gtvg.x14.eu':
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
