<?php
class view_abstract_Footer extends view_abstract_View
{
	protected function renderHeader( &$ro )
	{
		return '
</div>
<div id="footer" name="footer">
';
	}

	protected function renderContent( &$ro )
	{
		return '';
	}

	protected function renderFooter( &$ro )
	{
		return '
</div>
</div>
</body>
</html>
';
	}
}
?>