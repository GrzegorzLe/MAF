<?php
class view_Abstract_View
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

	protected function renderHeader( $content )
	{
		return '';
	}

	protected function renderContent( $content )
	{
		return '';
	}

	protected function renderFooter( $content )
	{
		return '';
	}

	public static function a2hp( $array )
	{
		$propsString = '';
		if ( isset( $array ) && !empty( $array ) )
			foreach ( $array as $i => $v )
				$propsString .= " $i='$v'";
		return $propsString;
	}
}
?>