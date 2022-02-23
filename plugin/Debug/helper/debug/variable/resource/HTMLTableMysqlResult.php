<?php
class helper_debug_variable_resource_HTMLTableMysqlResult
{
	public static function render( $var )
	{
		$retVal = '<tr><td></td>';
		mysql_field_seek( $var, 0 );
		while ( $res = mysql_fetch_field( $var ) )
			$retVal .= '<td class="_dbg_var_resource_key">' . $res->name . ' (' . $res->type . ')</td>';
		$retVal .= '</tr>';
		$i = 0;
		if ( mysql_num_rows( $var ) )
		{
			mysql_data_seek( $var, 0 );
			while ( $res = mysql_fetch_row( $var ) )
			{
				$rowClass = $i % 2 ? ' class="_dbg_var_resource_oddrow"' : '';
				$retVal .= '<tr' . $rowClass . '><td class="_dbg_var_resource_key">' . $i . '</td>';
				foreach( $res as $fld )
					$retVal .= '<td>' . $fld . '</td>';
				$retVal .= '</tr>';
				$i++;
			}
		}
		return $retVal;
	}
}
?>
