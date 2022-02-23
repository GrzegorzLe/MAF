<?php
/**
 * Configuration file
 *
 * File containing configuration:
 *	- directories, files
 *	- database settings
 */
require( 'env.php' );
/**
 * @var array $cfg Variable containing configuration settings
 */
$GLOBALS[ '_cfg' ] = array(
	'MAF' => array(
		'APPS' => array( 
			'GameTVGuide' => 'GTVG',
		),
		'MODULES' => array(
			'CMS' => 'CMS',
		),
		'PLUGINS' => array(
			'Debug' => 'DBG',
			'HTML' => 'HTML',
		),
	),
	'GTVG' => array(
		'DEFAULT_PAGE' => 'go',
		'FORCE_DEFAULT_PAGE' => true,
		'DEFAULT_ACTION' => 'home',
		'DEFAULT_VIEW' => 'Include',

		'APP_VERSION' => '0.0.1',
	),
	'DBG' => array(
		'ROOTPATH' => DIRECTORY_SEPARATOR . 'GameTVGuide' . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR,
	),
	'CMS' => array(
	),
	'HTML' => array(
	),
);

$envConfig = array(
	'dev' => array( 
	),
	'dev1' => array( 
	),
	'stg' => array( 
	),
	'live' => array(
	)
);

function &array_merge_recursive_distinct( array &$array1, &$array2 = null )
{
  $merged = $array1;
 
  if ( is_array( $array2 ) )
    foreach ( $array2 as $key => $val )
      if ( is_array( $array2[ $key ] ) )
        $merged[ $key ] = is_array( $merged[ $key ] ) ? array_merge_recursive_distinct( $merged[ $key ], $array2[ $key ] ) : $array2[ $key ];
      else
        $merged[ $key ] = $val;
 
  return $merged;
}

if ( array_key_exists( $confEnv, $envConfig ) && is_array( $envConfig[ $confEnv ] ) )
	$GLOBALS[ '_cfg' ] = array_merge_recursive_distinct( $GLOBALS[ '_cfg' ], $envConfig[ $confEnv ] );

function _cfg( )
{
	$args = func_get_args( );
	if ( count( $args ) == 3 )
		return $GLOBALS[ '_cfg' ][ $args[ 0 ] ][ $args[ 1 ] ][ $args[ 2 ] ];
	elseif ( count( $args ) == 2 )
		return $GLOBALS[ '_cfg' ][ $args[ 0 ] ][ $args[ 1 ] ];
	elseif ( count( $args ) == 1 )
		return $GLOBALS[ '_cfg' ][ $args[ 0 ] ];
	else
		return $GLOBALS[ '_cfg' ];
}
function _dbg( )
{
	if ( !array_key_exists( '_dbg', $GLOBALS ) )
		$GLOBALS[ '_dbg' ] = new helper_Debug( );
	return $GLOBALS[ '_dbg' ];
}
function _sql( )
{
	if ( !array_key_exists( '_sql', $GLOBALS ) )
		$GLOBALS[ '_sql' ] = new helper_SQL( );
	return $GLOBALS[ '_sql' ];
}
function _tmr( )
{
	if ( !array_key_exists( '_tmr', $GLOBALS ) )
		$GLOBALS[ '_tmr' ] = new helper_Timer( );
	return $GLOBALS[ '_tmr' ];
}

