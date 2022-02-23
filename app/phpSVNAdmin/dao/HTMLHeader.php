<?php
class dao_HTMLHeader extends dao_abstract_DAO
{
	protected static $doClassName = 'do_HTMLHeader';

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
	public function setMeta( $meta )
	{
		$this->do->meta[ ] = $meta;
		return true;
	}
}
?>