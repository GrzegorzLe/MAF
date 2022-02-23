<?php
class view_Footer extends view_Abstract
{
	protected function renderHeader( $contents )
	{
		$footer = $this->renderContentEnd( );
		$footer .= $this->renderFooterStart( );
		return $footer;
	}

	protected function renderContent( $contents )
	{
		return $this->renderFooterContent( );
	}

	protected function renderFooter( $contents )
	{
//		return $this->renderFooterEnd( $GLOBALS[ '_dbg' ]->renderDebug( ) );
		return $this->renderFooterEnd( );
	}

	private function renderContentEnd( $content = false )
	{
		return $content . "\r" . '</div>' . "\r";
	}

	private function renderFooterStart( $content = false )
	{
		return $content . "\r" . '<div id="footer" name="footer">' . "\r";
	}

	private function renderFooterContent( $content1 = false, $content2 = false )
	{
		ob_start( );
		include( 'include/footer.php' );
		$content = ob_get_clean( );
		return $content1 . "\r" . $content . "\r" . $content2;
	}

	private function renderFooterEnd( $content = false )
	{
		return '</div>' . "\r" . $content . '
</div>
</body>
</html>
';
	}
}
?>