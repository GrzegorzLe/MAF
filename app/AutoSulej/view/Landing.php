<?php
class view_Landing extends view_abstract_View
{
	protected function renderHeader( &$ro )
	{
		return ( new view_Header )->render( $ro );
	}

	public function renderContent( &$ro )
	{
		$rao = new dao_Request( $ro );
		$dsp = false;
		ob_start( );
		// slider row
		echo '<div class="sliderBox"><ul>';
		$dsp = 'slideBox';
		foreach ( $rao->getParam( 'slideBoxes' ) as $box )
		{
			echo '<li>';
			include 'app/AutoSulej/www/include/' . $box . '.php';
			echo '</li>';
		}
		echo '</ul></div>';
		// big box row
		echo '<div class="miniBoxContainer">';
		$dsp = 'miniBox';
		foreach ( $rao->getParam( 'bigBoxes' ) as $box )
		{
			echo '<div class="bigBox">';
			include 'app/AutoSulej/www/include/' . $box . '.php';
			echo '</div>';
		}
		// small box row
		echo '</div><div class="microBoxContainer">';
		$dsp = 'microBox';
		foreach ( $rao->getParam( 'smallBoxes' ) as $box )
		{
			echo '<div class="smallBox">';
			include 'app/AutoSulej/www/include/' . $box . '.php';
			echo '</div>';
		}
		echo '</div>';
//		include 'app/AutoSulej/www/include/' . $rao->getParam( 'include' ) . '.php';
		return ob_get_clean( );
	}

	protected function renderFooter( &$ro )
	{
		return ( new view_abstract_Footer )->render( $ro );
	}
}