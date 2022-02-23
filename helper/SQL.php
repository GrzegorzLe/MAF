<?php
class helper_SQL
{
	const SELECT = 0, INSERT = 1, UPDATE = 2, DELETE = 3;
	const AND_ = 'and', OR_ = 'or';

	private $sqlQuery = '';
	private $type = false;
	private $indent = false;

	private $error = false;

//	private $select, $flags, $columns, $from, $where, $group, $having, $order, $limit;
//	private $insert, $into, $values;
	public function select( $cols, $flags = false )
	{
		$sep = ' ';

		$this->type = helper_SQL::SELECT;
		$this->sqlQuery = 'select';
		if ( $flags !== false )
			$this->sqlQuery .= ' ' . $flags;
		if ( is_string( $cols ) )
			$this->sqlQuery .= ' ' . $cols;
		elseif ( is_array( $cols ) || is_object( $cols ) )
		{
			foreach ( $cols as $key => $value )
			{
				$this->sqlQuery .= $sep . $key;
				$sep = ', ';
			}
		}
		else
			$this->error = 'select( ): wrong type of first argument - ' . gettype( $cols );

		return $this;
	}

	public function from( $table, $short = false )
	{
		$this->sqlQuery .= ' from ' . $table;
		if ( $short !== false )
			$this->sqlQuery .= ' ' . $short;

		return $this;
	}

	public function update( $tableName )
	{
		$this->sqlQuery = 'update ' . $tableName;

		return $this;
	}

	public function set( $keyValue, $modified = false, $skipFirst = true )
	{
		$sep = ' ';

		$this->sqlQuery .= ' set';
		if ( is_string( $keyValue ) )
			$this->sqlQuery .= ' ' . $keyValue;
		else
			foreach( $keyValue as $key => $value )
			{
				if ( is_array( $modified ) && !$modified[ $key ] )
					continue;
//				if ( $skipFirst && $sep == ' ' )
//				{
//					$skipFirst = false;
//					continue;
//				}
				$this->sqlQuery .= $sep . $key . ' = \'' . $value . '\'';
				$sep = ', ';
			}

		return $this;
	}

	public function insert( )
	{
		$this->sqlQuery = 'insert';

		return $this;
	}

	public function into( $tableName, $fields = false )
	{
		$sep = ' ';

		$this->sqlQuery .= ' into ' . $tableName;
		
		if ( is_string( $fields ) )
			$this->sqlQuery .= '( ' . $fields . ' )';
		elseif ( $fields !== false )
		{
			$this->sqlQuery .= '(';
			foreach ( $fields as $field => $value )
			{
				$this->sqlQuery .= $sep . $field;
				$sep = ', ';
			}
			$this->sqlQuery .= ' )';
		}

		return $this;
	}

	public function values( $values )
	{
		$sep = ' ';

		$this->sqlQuery .= ' values(';

		if ( is_string( $fields ) )
			$this->sqlQuery .= ' ' . $values . ' )';
		else
		{
			foreach ( $values as $field => $value )
			{
					$this->sqlQuery .= $sep . '\'' . $value . '\'';
					$sep = ', ';
			}
			$this->sqlQuery .= ' )';
		}

		return $this;
	}

	// TODO: Fix problem with empty where( )
	public function where( $cond = false )
	{
		$this->indent = 0;
		$where = '';

		$sep = ' ';
		if ( is_string( $cond ) )
			$where = ' ' . $cond;
		elseif ( is_array( $cond ) || is_object( $cond ) )
		{
			foreach ( $cond as $key => $value )
			{
				if ( $value === false || $value === null )
					continue;
				$where .= $sep . $key . ' = \'' . $value . '\'';
				$sep = ' and ';
			}
		}
		if ( $where != '' )
			$this->sqlQuery .= ' where' . $where;

		return $this;
	}

	public function and_( $field, $value = false, $indent = 0 )
	{
		if ( is_string( $field ) && $value === false )
			return $this->andor( helper_SQL::AND_, $field, $indent );
		elseif ( $value !== false )
			return $this->andor( helper_SQL::AND_, array( $field => $value ), $indent );
		elseif ( is_array( $field ) || is_object( $field ) )
			return $this->andor( helper_SQL::AND_, $field, $indent );
	}

	public function or_( $field, $value = false, $indent = 0 )
	{
		if ( is_string( $field ) && $value === false )
			return $this->andor( helper_SQL::OR_, $field, $indent );
		elseif ( $value !== false )
			return $this->andor( helper_SQL::OR_, array( $field => $value ), $indent );
		elseif ( is_array( $field ) || is_object( $field ) )
			return $this->andor( helper_SQL::OR_, $field, $indent );
	}

	public function getQuery( )
	{
		if ( $this->error === false )
			return $this->sqlQuery;
		return false;
	}

	public function runQuery( $db )
	{
		if ( $this->error !== false )
		{
			_dbg( )->issueError( $this->error, helper_Debug::ERROR );
			return false;
		}
		elseif ( is_object( $db ) )
			return $db->query( $this->sqlQuery );
		else
			return mysql_query( $this->sqlQuery, $db );
	}

	public function getError( )
	{
		return $this->error;
	}

	private function andor( $type, $cond, $indent )
	{
		if ( $this->indent === false )
		{
			$this->error = 'and/or( ): start conditions with where( )';
			return $this;
		}

		if ( $this->indent < $indent )
			$this->sqlQuery .= ' ' . $type . ' (';
		elseif ( $this->indent > $indent )
			$this->sqlQuery .= ' )' . ' ' . $type;
		$this->indent = $indent;

		if ( is_string( $cond ) )
			$this->sqlQuery .= ' ' . $cond;
		else
			foreach ( $cond as $field => $value )
				$this->sqlQuery .= ' ' . $field . ' = ' . $value;

		return $this;
	}
}

$GLOBALS[ '_sql' ] = new helper_SQL( );
?>
