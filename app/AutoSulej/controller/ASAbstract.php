<?php
class controller_ASAbstract extends controller_Abstract
{
	public function preProcess( &$ro )
	{
		$rao = new dao_Request( $ro );
		$cd = new dao_HTMLHeader( new do_HTMLHeader( ) );
		$cd->setDocType( 'html5' );
		$cd->setTitle( 'AutoSulej' );
		$cd->setMeta( array( 'charset' => 'utf-8') );
		$cd->setMeta( 'description', 'AutoSulej Auto Naprawa Krzysztof Sulej' );
		$cd->setMeta( 'keywords', 'autosulej auto naprawa krzysztof sulej warsztat okregowa stacja kontroli pojazdow blacharka mechanika lakiernictwo minsk mazowiecki' );
		$cd->setMeta( 'author', 'Grzegorz Leśniewski' );
		$cd->setCSS( '/css/reset.css' );
		$cd->setCSS( '/css/main.css' );
		$cd->setLink( array( 'rel' => 'shortcut icon', 'href' => '/favicon.ico', 'type' => 'image/x-icon' ) );
		$rao->setParam( 'htmlHeader', $cd->getDO( ) );
		$rao->setParam( 'menuItem', $this->alias . '/' . $rao->getAction( ) );
		return;
	}
}