<?php
class dao_ViewContent extends dao_abstract_DAO
{
	protected static $doClassName = 'do_ViewContent';

	public function getHeader( )
	{
		return $this->do->header;
	}
	public function setHeader( $header )
	{
		$this->do->header = $header;
		return true;
	}

	public function getContent( )
	{
		return $this->content;
	}
	public function setContent( $content )
	{
		$this->content = $content;
		return true;
	}

	public function getFooter( )
	{
		return $this->footer;
	}
	public function setFooter( $footer )
	{
		$this->footer = $footer;
		return true;
	}
}
?>