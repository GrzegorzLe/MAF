<?php
class controller_Page extends controller_ASAbstract
{
	/** controller name */
	protected $name = 'Page';
	protected $alias = 'strona';
	public static $actions = array( 'o-firmie' => 'aboutAction', 'glowna' => 'landingAction', 'dojazd' => 'directionsAction', 'aktualnosci' => 'newsAction', 
			'oferta' => 'offerAction', 'kontakt' => 'contactAction', 'media' => 'mediaAction' );
	
	protected function indexAction( &$ro )
	{
		$rao = new dao_Request( $ro );
		$this->setView( 'Page' );
		// TODO: handle non-existing includes
		$rao->setParam( 'include', $rao->getArg( 1 ) );
		return $rao->getDO( );
	}

	protected function landingAction( &$ro )
	{
		$rao = new dao_Request( $ro );
		$cd = $rao->getParam( 'htmlHeader' );
		$cd = new dao_HTMLHeader( $cd );
		$cd->setTitle( 'Auto Naprawa Krzysztof Sulej' );
		$cd->setCSS( '/css/landing.css' );
		$cd->setJSSrc( 'https://maps.googleapis.com/maps/api/js?key=AIzaSyBCwXRRnmXkGNrx7LiWJm6V1xchq6-BB1s&sensor=false"' );
		$cd->setJSInline( '
	function initialize() {
		var mapOptions = {
			center: new google.maps.LatLng(52.198138,21.53698),
			zoom: 12,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		var map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
		var marker = new google.maps.Marker({
				position: new google.maps.LatLng(52.194344,21.514632),
				map: map,
				title: "AutoSulej - Auto Naprawa Krzysztof Sulej"
			});
	}
	google.maps.event.addDomListener(window, "load", initialize);
			' );
		$cd->setJSSrc( '/js/jquery-1.10.2.min.js' );
		$cd->setJSSrc( '/js/unslider.min.js' );
		$cd->setJSSrc( '/js/jquery.event.swipe.js' );
		$cd->setJSSrc( '/js/jquery.autoellipsis-1.0.10.js' );
		$cd->setJSInline( '$(function() { $(".sliderBox").unslider({speed: 1000, delay: 8000}); });' );
		$cd->setJSInline( '$(function() { $(".newsBox").unslider({speed: 1000, delay: 10000}); });' );
//		$cd->setJSInline( '$(function() { $(".newBox").ellipsis(); });' );
		$this->setView( 'Landing' );
		$rao->setParam( 'slideBoxes', array( 'o-firmie' , 'oferta', 'oferta-stacja-kontroli' ) );
		$rao->setParam( 'bigBoxes', array( 'oferta', 'dojazd', 'oferta-stacja-kontroli' ) );
		$rao->setParam( 'smallBoxes', array( 'oferta-lakiernictwo', 'oferta-blacharstwo', 'oferta-mechanika', 'media-socjalne' ) );
		//		$rao->setParam( 'include', $rao->getArg( 1 ) );
		return $rao->getDO( );
	}

	protected function aboutAction( &$ro )
	{
		$rao = new dao_Request( $ro );
		$cd = $rao->getParam( 'htmlHeader' );
		$cd = new dao_HTMLHeader( $cd );
		$cd->setTitle( 'O Firmie' );
		$rao->setParam( 'title', 'O Firmie' );
		$rao->setParam( 'wrapClass', 'horizBox' );
		$this->setView( 'Page' );
		$rao->setParam( 'include', array( 'o-firmie-historia', 'kontakt-dane' ) );
		return $rao->getDO( );
	}

	protected function directionsAction( &$ro )
	{
		$rao = new dao_Request( $ro );
		$cd = $rao->getParam( 'htmlHeader' );
		$cd = new dao_HTMLHeader( $cd );
		$cd->setTitle( 'Dojazd do zakładu' );
		$cd->setJSSrc( 'https://maps.googleapis.com/maps/api/js?key=AIzaSyBCwXRRnmXkGNrx7LiWJm6V1xchq6-BB1s&sensor=false"' );
		$cd->setJSInline( '
	function initialize() {
		var mapOptions = {
			center: new google.maps.LatLng(52.225696,21.286011),
			zoom: 11,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		var map = new google.maps.Map(document.getElementById("map-canvas-waw"), mapOptions);
		var marker = new google.maps.Marker({
				position: new google.maps.LatLng(52.194344,21.514632),
				map: map,
				title: "AutoNaprawa Krzysztof Sulej"
			});
		var mapOptions = {
			center: new google.maps.LatLng(52.211814,21.904678),
			zoom: 10,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		var map = new google.maps.Map(document.getElementById("map-canvas-sdl"), mapOptions);
		var marker = new google.maps.Marker({
				position: new google.maps.LatLng(52.194344,21.514632),
				map: map,
				title: "AutoNaprawa Krzysztof Sulej"
			});
	}
	google.maps.event.addDomListener(window, "load", initialize);
			' );
		$rao->setParam( 'title', 'Dojazd do zakładu' );
		$rao->setParam( 'wrapClass', 'horizBox' );
		$this->setView( 'Page' );
		$rao->setParam( 'include', array( 'dojazd-warszawa', 'dojazd-siedlce' ) );
		return $rao->getDO( );
	}

	protected function newsAction( &$ro )
	{
		$rao = new dao_Request( $ro );
		$cd = $rao->getParam( 'htmlHeader' );
		$cd = new dao_HTMLHeader( $cd );
		$cd->setTitle( 'Aktualności' );
		$rao->setParam( 'title', 'Aktualności' );
		$this->setView( 'Page' );
		$rao->setParam( 'include', array( 'aktualnosci' ) );
		return $rao->getDO( );
	}

	protected function offerAction( &$ro )
	{
		$rao = new dao_Request( $ro );
		$cd = $rao->getParam( 'htmlHeader' );
		$cd = new dao_HTMLHeader( $cd );
		$cd->setTitle( 'Oferta Zakładu' );
		$rao->setParam( 'title', 'Oferta zakladu' );
		$this->setView( 'Page' );
		$rao->setParam( 'display', 'miniBox' );
		$rao->setParam( 'wrapClass', 'bigBox' );
		$rao->setParam( 'include', array( 'oferta-blacharstwo', 'oferta-stacja-kontroli', 'oferta-serwis-ogumienia', 'oferta-lakiernictwo', 'oferta-mechanika', 'oferta-naprawy-powypadkowe' ) );
		return $rao->getDO( );
	}

	protected function contactAction( &$ro )
	{
		$rao = new dao_Request( $ro );
		$cd = $rao->getParam( 'htmlHeader' );
		$cd = new dao_HTMLHeader( $cd );
		$cd->setTitle( 'Kontakt' );
		$rao->setParam( 'title', 'Kontakt z nami' );
		$rao->setParam( 'wrapClass', 'horizBox' );
		$this->setView( 'Page' );
		$rao->setParam( 'include', array( 'kontakt' ) );
		return $rao->getDO( );
	}

	protected function mediaAction( &$ro )
	{
		$rao = new dao_Request( $ro );
		$cd = $rao->getParam( 'htmlHeader' );
		$cd = new dao_HTMLHeader( $cd );
		$cd->setTitle( 'Media socjalne' );
		$rao->setParam( 'title', 'Media socjalne' );
		$this->setView( 'Page' );
		$rao->setParam( 'include', array( 'media-socjalne' ) );
		return $rao->getDO( );
	}
}
