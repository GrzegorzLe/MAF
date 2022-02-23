<?php
class TimerDO
{
	public $type, $start, $end, $name, $parent;
	const DISPATCH = 0, PROCESS = 1, RENDER = 2;

	public function __construct( $type, $start, $name = false, &$parent = false, $end = false )
	{
		$this->type = $type;
		$this->start = $start;
		$this->end = $end;
		$this->name = $name;
		$this->parent = &$parent;
	}
}

class helper_Timer
{
	public $process = array( ), $render = array( ), $query = array( );
	private $current = array( );

	public function __construct( )
	{
	}

	public function __destruct( )
	{
	}

	public function processStart( $name = false )
	{
		return $this->start( 'process', $name );
	}

	public function processEnd( $id = false )
	{
		return $this->end( 'process', $id );
	}

	public function processNext( $name = false )
	{
		$this->end( 'process' );
		return $this->processStart( $name );
	}

	public function renderStart( $name = false )
	{
		return $this->start( 'render', $name );
	}

	public function renderEnd( $id = false )
	{
		return $this->end( 'render', $id );
	}

	public function renderNext( $name = false )
	{
		$this->end( 'render' );
		return $this->renderStart( $name );
	}

	public function queryStart( $name = false )
	{
		return $this->start( 'query', $name );
	}

	public function queryEnd( $id = false )
	{
		return $this->end( 'query', $id );
	}

	public function queryNext( $name = false )
	{
		$this->end( 'query' );
		return $this->queryStart( $name );
	}

	private function start( $what, $name )
	{
		if ( !isset( $this->{$what}[ 0 ] ) )
		{
			array_push( $this->$what, new TimerDO( $what, microtime( ), 'total' ) );
			$this->current[ $what ] = &$this->{$what}[ 0 ];
		}
		if ( $name !== false )
		{
			array_push( $this->$what, new TimerDO( $what, microtime( ), $name, $this->current[ $what ] ) );
			$this->current[ $what ] = &$this->{$what}[ count( $this->$what ) - 1 ];
		}
		return count( $this->$what ) - 1;
	}

	private function end( $what, $id = false )
	{
		$current = false;
		if ( $id === false )
			$current = &$this->current[ $what ];
		elseif ( count( $this->$what ) > $id )
			$current = &$this->{$what}[ $id ];
//		echo helper_Debug::varDump( count( $this->$what ) . ' ' . $id );

		if ( !( $current instanceof TimerDO ) )
			helper_Debug::issueWarning( 'bad timer ID, unable to end', helper_Debug::DEVEL );
		else
		{
			if ( $current->end !== false )
				helper_Debug::issueWarning( 'timer already ended, overwriting', helper_Debug::DEVEL );
			$current->end = microtime( );
			$this->current[ $what ] = &$current->parent;

			return true;
		}

		return false;
	}
}

$GLOBALS[ '_tmr' ] = new helper_Timer( );
?>
