<?php
class dao_HTMLHeader extends dao_abstract_DAO
{
	protected static $doClassName = 'do_HTMLHeader';

	public function getDocType( )
	{
		return $this->do->docType;
	}
	public function setDocType( $docType )
	{
		$this->do->docType = $docType;
		return true;
	}

	public function getHtmlProps( )
	{
		return $this->do->htmlProps;
	}
	public function setHtmlProps( $htmlProps )
	{
		$this->do->htmlProps[ ] = $htmlProps;
		return true;
	}

	public function getTitle( )
	{
		return $this->do->title;
	}
	public function setTitle( $title )
	{
		$this->do->title[ ] = $title;
		return true;
	}

	public function getJSSrc( )
	{
		return $this->do->jsSrc;
	}
	public function setJSSrc( $jsSrc )
	{
		$this->do->jsSrc[ ] = $jsSrc;
		return true;
	}

	public function getJSInline( )
	{
		return $this->do->jsInline;
	}
	public function setJSInline( $jsInline )
	{
		$this->do->jsInline[ ] = $jsInline;
		return true;
	}

	public function getCSS( )
	{
		return $this->do->css;
	}
	public function setCSS( $css )
	{
		$this->do->css[ ] = $css;
		return true;
	}

	public function getMeta( )
	{
		return $this->do->meta;
	}
	public function setMeta( $meta, $arg2 = null )
	{
		if ( is_array( $meta ) )
			$this->do->meta[ ] = $meta;
		else
			$this->do->meta[ ] = array( 'name' => $meta, 'content' => $arg2 );
		return true;
	}

	public function getLink( )
	{
		return $this->do->link;
	}
	public function setLink( $link )
	{
		$this->do->link[ ] = $link;
		return true;
	}
}
?>