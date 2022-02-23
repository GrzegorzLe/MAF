<?php
class TimerDO
{
	public $type, $start, $end, $name, $comment, $parent;

	public function __construct( $type, $start, $name = false, $comment = false, &$parent = false, $end = false )
	{
		$this->type = $type;
		$this->start = $start;
		$this->end = $end;
		$this->name = $name;
		$this->comment = $comment;
		$this->parent = &$parent;
	}
}

class helper_Timer extends helper_abstract_Helper
{
	public $times = array( );
	private $current = false;

	public function start( $type, $name, $comment = false )
	{
		$newTDO = new TimerDO( $type, microtime( ), $name, $comment, $this->current );
		array_push( $this->times, $newTDO );
		$this->current = &$newTDO;
	}

	public function end( $type, $name = false )
	{
		for ( $i = count( $this->times ) - 1; $i >= 0; $i-- )
		{
			$this->times[ $i ]->end = microtime( );
			if ( $this->times[ $i ]->type == $type && ( $name === false || $this->times[ $i ]->name == $name ) )
			{
				$this->current = &$this->times[ $i ]->parent;
				return true;
			}
		}
		return false;
	}

	public function next( $type, $name, $comment = false )
	{
		$this->end( $type );
		$this->start( $type, $name, $comment );
	}

	public function render( &$i = null, $parent = null )
	{
		if ( is_null( $i ) )
			$i = 0;
	}
}
?>
