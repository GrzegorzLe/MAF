<?php
class view_abstract_Header extends view_abstract_View
{
/* 	public function preRender( $content )
	{
		if ( !is_array( $content ) )
			$content = array( );
		if ( !array_key_exists( 'docType', $content ) )
			$content[ 'docType' ] = 'html5';
		if ( !array_key_exists( 'htmlProps', $content ) )
			$content[ 'htmlProps' ] = array( );
		if ( !array_key_exists( 'title', $content ) )
			$content[ 'title' ] = '';
		if ( !array_key_exists( 'meta', $content ) )
			$content[ 'meta' ] = array( );
		if ( !array_key_exists( 'css', $content ) )
			$content[ 'css' ] = array( );
		if ( !array_key_exists( 'jsSrc', $content ) )
			$content[ 'jsSrc' ] = array( );
		if ( !array_key_exists( 'jsInline', $content ) )
			$content[ 'jsInline' ] = array( );
		return $content;
	} */

	protected function renderHeader( &$ro )
	{
		$rao = new dao_Request( $ro );
		$content = $rao->getParam( 'htmlHeader' );
		$header = $this->renderHeaderStart( $content->docType, $content->htmlProps );
		$header .= $this->renderTitle( $content->title );
		$header .= $this->renderMeta( $content->meta );
		$header .= $this->renderLink( $content->link );
		$header .= $this->renderCSS( $content->css );
		$header .= $this->renderJS( $content->jsSrc, $content->jsInline );
		$header .= $this->renderHeaderEnd( );
		return $header;
	}

	protected function renderContent( &$ro )
	{
		return '';	
	}

	protected function renderFooter( &$ro )
	{
		return '</div>
<div id="content">
';
	}

	private function renderHeaderStart( $docType = 'html5', $htmlProps = array( ) )
	{
		return helper_HTML::docType( $docType ) . '
<html' . helper_HTML::a2hp( $htmlProps ) . '>
<head>
';
	}

	private function renderTitle( $title )
	{
		$retTitle = '<title>';
		$retTitle .= implode( ' &gt; ', $title );
		$retTitle .= '</title>
';
		return $retTitle;
	}

	private function renderMeta( $meta )
	{
		$retMeta = '';
		foreach ( $meta as $m )
			$retMeta .= '<meta' . helper_HTML::a2hp( $m ) . ' />
';
		return $retMeta;
	}

	private function renderCSS( $css )
	{
		$retCss = '';
		foreach ( $css as $c )
			$retCss .= '<link rel="stylesheet" type="text/css" href="' . $c . '" />
';
		return $retCss;
	}

	private function renderJS( $jsSrc, $jsInline )
	{
		$script = '';
		foreach ( $jsSrc as $js )
			$script .= '<script type="text/javascript" src="' . $js . '"></script>
';
		if ( empty( $jsInline ) )
			return $script;
		$script .= '<script type="text/javascript">
';
		foreach ( $jsInline as $js )
			$script .= $js . "\r";
		$script .= '</script>
';
		return $script;
	}

	private function renderLink( $link )
	{
		$retLink = '';
		foreach ( $link as $l )
			$retLink .= '<link' . helper_HTML::a2hp( $l ) . ' />
';
		return $retLink;
	}
	
	private function renderHeaderEnd( )
	{
		return '</head>
<body>
<div id="pageContent">
<div id="header">
';
	}
}
?>
