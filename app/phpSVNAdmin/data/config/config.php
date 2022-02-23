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
	/* core config */
	'MAF' => array(
		'APPS' => array( 
			'phpSVNAdmin' => 'PSA',
		),
		'MODULES' => array(
			'CMS' => 'CMS',
		),
		'PLUGINS' => array(
			'Debug' => 'DBG',
			'HTML' => 'HTML',
		),
	),
	'DB' => array(
		'HOST' => 'localhost',
		'USER' => 'lesio_psa',
		'PASS' => 'le1sio',
		'NAME' => 'lesio_psa',
	
	),
	/* apps configs */
	'PSA' => array(
		'SVN_USERS' => '/var/svn/conf/svnusers',
		'SVN_REPOS' => '/var/svn/repos/',
	
		'DEFAULT_PAGE' => 'Status',
		'DEFAULT_ACTION' => 'indexAction',
		'DEFAULT_VIEW' => 'Status',

		'APP_VERSION' => '0.0.5',
	),
	/* plugin configs */
	'DBG' => array(
		'ROOTPATH' => DIRECTORY_SEPARATOR . 'phpSVNAdmin' . DIRECTORY_SEPARATOR,
	),
	'HTML' => array(
	),
	/* modules configs */
	'CMS' => array(
	),
);

$envConfig = array(
	'dev' => array( 
		'DB' => array( 
//			'USER' => 'lesio',
		), 
	),
	'dev1' => array( 
		'DB' => array(
			'USER' => 'lesio',
		),
		'DB_HOST' => 'mysql-959088.vipserv.org',
	),
	'stg' => array( 
		'DB' => array(
				'HOST' => 'mysql5',
		),
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
?>

