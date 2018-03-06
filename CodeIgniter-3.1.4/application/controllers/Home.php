<?php

/**
 * Class Home
 * 首页
 */
class Home extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
    }

    //首页 -- 默认路由
    public function wellcome()
	{
	    $variablesArray=array('title'=>'欢迎使用！');
		$this->load->view('home/wellcome',$variablesArray);
	}
}
