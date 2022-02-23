<?php
class view_Header extends view_abstract_Header
{
	function renderContent( &$ro )
	{
		$ret = ( new view_abstract_Image( '/img/content/Logo2.png', 'AutoSulej Logo', 260, 80, 'headerLogo', '/strona/glowna' ) )->render( $ro );
		$ret .= '<div class="right"><span class="phoneBig">tel. 570 545 545</span><br /><span class="phone">tel. &nbsp;(25) 758 63 79</span><br /><span class="phone">kom. 608 66 22 75</span></div>';
		$ret .= ( new view_abstract_Menu( 
					array( 'O Firmie' => 'strona/o-firmie', 'Oferta' => 'strona/oferta'/* , 'Aktualności' => 'strona/aktualnosci' */, 
						'Dojazd' => 'strona/dojazd' ), '|', array( 'class' => 'mainMenu' ), array( 'class' => 'mainMenuItem' ) ) )->render( $ro );
		return $ret;
	}
}
