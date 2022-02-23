<?php
class view_Header extends view_Abstract
{
	public function preRender( $content )
	{
		array_unshift( $content->title, "PHP SVN Admin" );
		if ( !in_array( "css/main.css", $content->css ) )
			array_push( $content->css, "/phpsvnadmin/css/main.css" );
		
		// make sure at least http-equiv and content is specified in meta
//		$content->meta = array_merge( array( 'http-equiv' => 'Content-Type', 'content' => 'text/html; charset=UTF-8' ), $content->meta );
//		$content->meta = array( 'http-equiv' => 'Content-Type', 'content' => 'text/html; charset=UTF-8' );
		_dbg( )->issueDebug( 'content:', helper_Debug::DEVEL, $content );
		return $content;
	}

	protected function renderHeader( $content )
	{
		$header = $this->renderHeaderStart( );
		$header .= $this->renderTitle( $content->title );
		$header .= $this->renderMeta( $content->meta );
		$header .= $this->renderCSS( $content->css );
		$header .= $this->renderJS( $content->jsSrc, $content->jsInline );
		$header .= $this->renderHeaderEnd( );
		return $header;
	}

	protected function renderContent( $content )
	{
		return $this->renderHeaderContent( );
	}

	protected function renderFooter( $content )
	{
		return $this->renderContentStart( );
	}

	private function renderHeaderStart( )
	{
		return helper_HTML::docType( 'xhtml1', 'transitional' ) . '
<html lang="en-US" xml:lang="en-US" xmlns="http://www.w3.org/1999/xhtml">
<head>
';
	}

	private function renderTitle( $title )
	{
		$retTitle = '<title>';
		$retTitle .= implode( ' &gt; ', $title );
		$retTitle .= '</title>' . "\r";
		return $retTitle;
	}

	private function renderMeta( $meta )
	{
		$retMeta = '';
		foreach ( $meta as $m )
			$retMeta .= '<meta' . helper_HTML::a2hp( $m ) . ' />' . "\r";
		return $retMeta;
	}

	private function renderCSS( $css )
	{
		$retCss = '';
		foreach ( $css as $c )
			$retCss .= '<link rel="stylesheet" type="text/css" href="' . $c . '" />' . "\r";
		return $retCss;
	}

	private function renderJS( $jsSrc, $jsInline )
	{
		$script = '';
		foreach ( $jsSrc as $js )
			$script .= '<script type="text/javascript" src="' . $js . '"></script>' . "\r";
		if ( empty( $jsInline ) )
			return $script;
		$script .= '<script type="text/javascript">' . "\r";
		foreach ( $jsInline as $js )
			$script .= $js . "\r";
		$script .= '</script>' . "\r";
		return $script;
	}

	private function renderHeaderEnd( )
	{
		return '</head>
<body>
<div id="pageContent">
';
	}

	private function renderHeaderContent( )
	{
		$retPage = '<div id="header">' . "\r";
		ob_start( );
		include( 'include/header.php' );
		$retPage .= ob_get_clean( );
		$retPage .= '</div>' . "\r";
		return $retPage;
	}

	private function renderContentStart( )
	{
		return '<div id="content">' . "\r";
	}
}
?>
