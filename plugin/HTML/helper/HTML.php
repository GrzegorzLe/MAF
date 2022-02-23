<?php
/**
 * HTML Helper class
 *
 * Class supplies methots to ease HTML generation
 *
 * @uses HTMLTable
 * @author Grzegorz Lesniewski
 * @copyright Grzegorz Lesniewski 2009
 */
class helper_HTML
{
	/**
	 * Creates new HTMLTable
	 *
	 * Creates new HTMLTable
	 *
	 * @static
	 * @param string $id id property for new table
	 * @param string $class class property for new table
	 * @param string $style style property for new table
	 * @param string $title title property for new table
	 * @return HTMLTable newly created table
	 * @uses HTMLTable
	 * @author Grzegorz Lesniewski
	 * @copyright Grzegorz Lesniewski 2009
	 */
	public static function newTable( $id = false, $class = false, $style = false, $title = false )
	{
		return new HTMLTable( $id, $class, $style, $title );
	}

	public static function docType( $type = 'HTML5', $variant = '' )
	{
		$retVal = '<!DOCTYPE ';
		$dtd = '';
		switch( $type )
		{
			case 'HTML5':
			case 'html5':
				$retVal .= 'html>';
				break;
			case 'HTML4':
			case 'html4':
				$retVal .= 'HTML PUBLIC "-//W3C//DTD HTML 4.01';
				switch( $variant )
				{
					case '':
					case 'strict':
					case 'Strict':
						$retVal .= '//EN" ';
						$dtd = 'strict';
						break;
					case 'transitional':
					case 'Transitional':
						$retVal .= ' Transitional//EN" ';
						$dtd = 'loose';
						break;
					case 'frameset':
					case 'Frameset':
						$retVal .= ' Frameset//EN" ';
						$dtd = 'frameset';
						break;
				}
				$retVal .= '"http://www.w3.org/TR/html4/' . $dtd . '.dtd">';
				break;
			case 'XHTML1':
			case 'xhtml1':
				$retVal .= 'html PUBLIC "-//W3C//DTD XHTML 1.0 ';
				switch( $variant )
				{
					case '':
					case 'strict':
					case 'Strict':
						$retVal .= 'Strict//EN" ';
						$dtd = 'strict';
						break;
					case 'transitional':
					case 'Transitional':
						$retVal .= 'Transitional//EN" ';
						$dtd = 'transitional';
						break;
					case 'frameset':
					case 'Frameset':
						$retVal .= 'Frameset//EN" ';
						$dtd = 'frameset';
						break;
				}
				$retVal .= '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-' . $dtd . '.dtd">';
				break;
		}
		return $retVal;
	}

	/**
	 * Array to html properties
	 *
	 * Translates array into string $key='$value'.
	 * Useful for translating array into html tag properties
	 *
	 * @static
	 * @param array $array array of properties to translate
	 * @return string property array converted to string
	 * @author Wincor-Nixdorf
	 * @copyright Wincor-Nixdorf 2009
	 */
	public static function a2hp( $array )
	{
		$retval = "";
		if ( isset( $array ) && !empty( $array ) )
			foreach ( $array as $i => $v )
				if ( !empty( $v ) )
					$retval .= " $i='$v'";
		return $retval;
	}
}

$GLOBALS[ '_html' ] = new helper_HTML( );
?>