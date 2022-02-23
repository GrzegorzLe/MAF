<?php
class view_Abstract
{
	public function preRender( $content )
	{
		return $content;
	}

	public function render( $content )
	{
		$content = $this->preRender( $content );

		$retPage = $this->renderHeader( $content );
		$retPage .= $this->renderContent( $content );
		$retPage .= $this->renderFooter( $content );

		return $retPage;
	}

	protected function renderHeader( $contents )
	{
		return '';
	}

	protected function renderContent( $contents )
	{
		return '';
	}

	protected function renderFooter( $contents )
	{
		return '';
	}
}
?>