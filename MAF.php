<?php
require( 'helper/ClassLoader.php' );
require( 'Core.php' );

class MAF extends Core
{
	private $apps = array( );
	private $modules = array( );
	private $plugins = array( );

	public function __construct( $config )
	{
		parent::__construct();
		helper_ClassLoader::register( );
		$this->loadModules( $config[ 'MODULES' ] );
		$this->loadPlugins( $config[ 'PLUGINS' ] );

		$this->debugger = $this->getApp( 'Debug' );
		$this->timer = $this->getApp( 'Timer' );
		
		$this->loadApps( $config[ 'APPS' ] );

		return $this;
	}

/* 	public function run( $get, $post )
	{
		$args = explode( ',', $get );
		$page = array_pop( $args );
		if ( count( $args ) === 0 )
			;
	} */

	private function loadModules( $modules )
	{
		foreach ( $modules as $module => $cfg )
		{
			$modulePath = 'module_' . $module . '_' . $module;
			$this->apps[ $module ] = new $modulePath( self::_cfg( $cfg ) );
		}
	}

	private function loadPlugins( $plugins )
	{
		foreach ( $plugins as $plugin => $cfg )
		{
			$pluginPath = 'plugin_' . $plugin . '_' . $plugin;
			$this->apps[ $plugin ] = new $pluginPath( self::_cfg( $cfg ) );
		}
	}

	private function loadApps( $apps )
	{
		foreach ( $apps as $app => $cfg )
		{
			$appPath = 'app_' . $app . '_' . $app;
			$this->apps[ $app ] = new $appPath( $this->debugger, $this->timer );
		}
	}

	public function getApp( $app = null )
	{
		if ( $app === null && count( $this->apps ) > 0 )
			return end( $this->apps );
		if ( array_key_exists( $app, $this->apps ) )
			return $this->apps[ $app ];
		return null;
	}

	public static function _cfg( )
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
}