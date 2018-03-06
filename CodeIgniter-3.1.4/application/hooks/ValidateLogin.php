<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * used for no login to prompt and switch to login page
 */

class ValidateLogin
{
	//CI超级对象
	protected $CI;
	//除外的动作
	protected $allowAction = array('login','nologin','register');

	public function __construct()
	{
		//获取CI超级对象并加载需要类库
		$this->CI =& get_instance();
	}

	//如果未登录提示并转到登录页
	public function validateLogin()
	{
		switch ($this->CI->uri->segment(1)) {
			//过滤的控制器及动作
			case 'user':
			case 'freight':
			case 'order':
			case 'vehicle':
				//取控制器动作
				$action=$this->CI->uri->segment(2);
				if(!in_array($action,$this->allowAction) && !isset($this->CI->session->userID)){
					redirect('user/nologin');
				}
				break;	
			//除以上外不执行过滤		
			default:
				# code...
				break;
		}		
	}

}
