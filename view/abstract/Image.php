<?php
class view_abstract_Image extends view_abstract_View
{
	protected $source = '';
	protected $alt = '';
	protected $width = 0;
	protected $height = 0;
	protected $class = 'image';
	protected $href = '';
	
	function renderContent( &$ro )
	{
		return ( $this->href != '' ? '<a href="' . $this->href . '">' : '' ) .
			'<img src="' . $this->source . '" alt="' . $this->alt . '" class="' . $this->class . '" ' .
			( $this->height > 0 ? 'height="' . $this->height . '" ' : '' ) . 
			( $this->width > 0 ? 'width="' . $this->width . '" ' : '' ) . '/>' . ( $this->href != '' ? '</a>' : '' ) . '
';
	}
}