<?php
class do_Request
{
	public $get = '';
	public $post = false;
	public $args = array( );

	public $controller = false;
	public $action = false;
	public $view = false;

	public $app = null;
	
	public $params = array( );
//	public $despatched = false;
}