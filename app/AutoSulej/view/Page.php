<?php
class view_Page extends view_abstract_View
{
	protected function renderHeader( &$ro )
	{
		return ( new view_Header )->render( $ro );
	}

	public function renderContent( &$ro )
	{
		$rao = new dao_Request( $ro );
		$dsp = $rao->getParam( 'display' );
		$includes = $rao->getParam( 'include' );
		$wrapClass = $rao->getParam( 'wrapClass' );
		ob_start( );
		echo '<h1>' . $rao->getParam( 'title' ) . '</h1>';
		if ( is_string( $includes ) )
		{
			if ( $wrapClass )
				echo '<div class="' . $wrapClass . '">';
			include 'app/AutoSulej/www/include/' . $includes . '.php';
			if ( $wrapClass )
				echo '</div>';
		}
		else
		{
			$i = 1;
			echo '<div>';
			foreach( $includes as $include )
			{
				if ( $wrapClass )
					echo '<div class="' . $wrapClass . '">';
				include 'app/AutoSulej/www/include/' . $include . '.php';
				if ( $wrapClass )
					echo '</div>';
				if ( $i++ % 3 == 0 )
					echo '</div><div>';
			}
			echo '</div>';
		}
		return ob_get_clean( );
	}

	protected function renderFooter( &$ro )
	{
		return ( new view_abstract_Footer )->render( $ro );
	}
}
