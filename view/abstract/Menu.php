<?php
class view_abstract_Menu extends view_abstract_View
{
	protected $items = array( );

	protected $separator = false;
	protected $menuProps = array( 'class' => 'menu' );
	protected $itemProps = array( );
	protected $activeProp = 'active';
	protected $type = 'ul';
	protected $root = '/';

	function renderHeader( &$ro )
	{
		if ( $this->type == 'ul' )
			return '<ul' . $this->a2hp( $this->menuProps ) . '>';
	}

	function renderContent( &$ro )
	{
		$rao = new dao_Request( $ro );
		$activeItem = $rao->getParam( 'menuItem' );
		$bkpClass = false;
		$ret = '';
		if ( $this->type == 'ul' )
			foreach( $this->items as $label => $link )
			{
				if ( $this->separator && $ret != '' )
					$ret .= '<li>' . $this->separator . '</li>';
				if ( $link == $activeItem )
				{
					if ( array_key_exists( 'class', $this->itemProps ) )
					{
						$bkpClass = $this->itemProps[ 'class' ];
						$this->itemProps[ 'class' ] .= ' ' . $this->activeProp;
					}
					else
						$this->itemProps[ 'class' ] = $this->activeProp;
				}
				$ret .= '<li' . $this->a2hp( $this->itemProps ) . '><a href="' . $this->root . $link . '">' . $label . '</a></li>';
				if ( $link == $activeItem )
				{
					if ( $bkpClass )
						$this->itemProps[ 'class' ] = $bkpClass;
					else
						unset(  $this->itemProps[ 'class' ] );
				}
			}
		return $ret;
	}

	function renderFooter( &$ro )
	{
		if ( $this->type == 'ul' )
			return '</ul>';
	}
}
