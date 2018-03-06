<?php

/**
 * Class User
 * 用户注册、登录Controller
 */

class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->library('form_validation');

        //加载自写类
        //注意这里首字母未对应类名大写 -- CI的特性 -- 参数 小写名 直接作为该类成员来使用
        //CI不支持验证文件表单，此是自定义文件表单验证类
        $this->load->library('file_validation');

        $this->load->model('users_model');
        $this->load->model('vehicle_model');
        $this->load->model('freights_model');
        $this->load->model('freight_franchisers_model');
        $this->load->model('orders_model');
    }

    //用户注册
	public function register()
	{
	    //如果有提交注册数据 -- 提交表单 submit="true"
	    if($this->input->post('registerSubmit')!='')
        {
            //验证表单数据 -- 注意：必须用array('field'=>'value','label'=>'value','rules'=>'value')的绝对key/value形式，否则验证规则无效且不报错
            $validateArray=array(
                array('field'=>'credentialType','label'=>'证件类型','rules'=>'required|numeric'),
                array('field'=>'mobileNumber','label'=>'电话或手机号','rules'=>'required|min_length[11]|numeric'),
                array('field'=>'userPassword','label'=>'输入密码','rules'=>'required|min_length[6]|callback_validatePassword['.$this->input->post('ensurePassword').']')
            );
            //如果是身份证类型
            if($this->input->post('credentialType')==1){
                $validateArray[]= array('field'=>'credentialNumber','label'=>'身份证号','rules'=>'required|exact_length[18]|callback_credentialNumber');
                $validateArray[]=array('field'=>'fullName','label'=>'真实姓名','rules'=>'required|min_length[2]');
                $fileValidateArray=array(
                    array('field'=>'photo1','label'=>'身份证正面照片','rules'=>'required|file_type[jpg,png,gif]'),
                    array('field'=>'photo2','label'=>'身份证反面照片','rules'=>'required|file_type[jpg,png,gif]'),
                    array('field'=>'photo3','label'=>'本人手持身份证照片','rules'=>'required|file_type[jpg,png,gif]')
                );
            }//否则是营业执照类型
            else{
                $validateArray[]= array('field'=>'credentialNumber','label'=>'营业执照编号','rules'=>'required|exact_length[15]|callback_credentialNumber');
                $validateArray[]=array('field'=>'fullName','label'=>'公司名称','rules'=>'required|min_length[2]');
                $fileValidateArray=array(array('field'=>'photo1','label'=>'营业执照正面照片','rules'=>'required|file_type[jpg,png,gif]'));
            }
            $this->form_validation->set_rules($validateArray);
            $this->file_validation->set_rules($fileValidateArray);

            //如果表单未通过验证或有文件未上传则返回提示并转到注册页 -- 注意顺序：先运行验证再与判断
            $result=$this->form_validation->run();
            $result=$this->file_validation->run() && $result;
            if(!$result)
            {
                $variablesArray=array('content'=>'部分数据不满足录入要求，是否返回修改？','errors'=>$this->file_validation->file_validation_errors());
                $this->load->view('prompt/confirm',$variablesArray);
            }
            //验证通过写入数据
            else
            {
                //返回值为aID
                $result=$this->users_model->register();

                //如果写入成功 -- 转到 找货页面
                if($result[0])
                {
                    $variablesArray=array('content'=>'恭喜您注册成功！','url'=>site_url('user/main'));
                    $sessionData=array('userID'=>$result[1],'fullName'=>$this->input->post('fullName'));
                    $this->setSession($sessionData);
                    $this->load->view('prompt/alert',$variablesArray);
                }
                //否则 -- 提示失败，是否返回
                else
                {
                    $variablesArray=array('content'=>'很抱歉，注册时遇到错误，您可返回重试。','errors'=>$result[1]);
                    $this->load->view('prompt/confirm',$variablesArray);
                }
            }
        }
        //否则直接进入注册页
        else
        {
            $credentialType=$this->users_model->getcredentialType();
            $variablesArray =array('title'=>'欢迎注册！','credentialTypeArray'=>$credentialType);
            $this->load->view('user/register', $variablesArray);
        }
	}

	//更改密码 -- ajax
	public function changePassword(){
        $validateArray = array(
            array('field'=>'userPassword','label'=>'旧密码','rules'=>'required|min_length[6]'),
            array('field' => 'newPassword', 'label' => '新密码', 'rules' => 'required|min_length[6]|callback_validatePassword['.$this->input->post('ensurePassword').']')
        );
        $this->form_validation->set_rules($validateArray);
        //输入值合法 -- 验证通过后再更改密码
        if($this->form_validation->run())
        {
            $row=$this->users_model->isExistUser($this->input->post('credentialNumber'),$this->input->post('userPassword'));
            //如果没有匹配的用户名密码
            if($row==null)
            {
                echo '旧密码不正确';
                return;
            }

            //更改密码
            $result=$this->users_model->changePassword($this->input->post('credentialNumber'),$this->input->post('newPassword'));
            //如果修改成功返回空
            if($result[0]){
                echo '';
            }//否则返回出错信息
            else{
                echo $result[1];
            }
        }
        //如果不符合规则要求则提示并返回登录页
        else
        {
            echo validation_errors();
        }
    }

    //用户登录
	public function login()
    {
        //如果有提交表单 submit="true"
        if($this->input->post('loginSubmit')!='')
        {
            //验证用户名密码不能为空且满足规则
            $this->form_validation->set_rules(array(
                array('field'=>'credentialNumber','label'=>'身份证号/营业执照编号','rules'=>'required|min_length[15]'),
                array('field'=>'userPassword','label'=>'密码','rules'=>'required|min_length[6]')
            ));

            //验证通过 -- 用户名密码正确进入用户信息页面
            if($this->form_validation->run())
            {
                $row=$this->users_model->isExistUser($this->input->post('credentialNumber'),$this->input->post('userPassword'));
                if($row)
                {
                    $sessionData=array('userID'=>$row->aID,'fullName'=>$row->fullName,'vehicleCount'=>$row->vehicleCount);
                    $this->setSession($sessionData);
					//重定向到主页面
                    redirect('/user/main');
                }//否则提示用户名密码错误
                else{
                    $variablesArray=array('content'=>'用户名或密码错误！','url'=>site_url('user/login'));
                    $this->load->view('prompt/alert',$variablesArray);
                }
            }
            //如果不符合规则要求则提示并返回登录页
            else
            {
                $variablesArray=array('content'=>'用户名或密码错误，请重新登录。','url'=>site_url('user/login'));
                $this->load->view('prompt/alert',$variablesArray);
            }
        }
        //否则直接进入登录页
        else
        {
            $variablesArray =array('title'=>'欢迎登录！');
            $this->load->view('user/login', $variablesArray);
        }
    }

    //未登录提示并转到登录页
    public function nologin(){
        $variablesArray = array('content' => '尚未登录，请先登录。', 'url' => site_url('user/login'));
        $this->load->view('prompt/alert', $variablesArray);
    }

    //用户信息页
    public function main()
    {
        $variablesArray = array('title' => '用户信息');
        $this->load->view('user/main', $variablesArray);
    }

    //用户信息页面
    public function info(){
        $userInfo=$this->users_model->getUserInfo($this->session->userID);
        $userVehicels=$this->vehicle_model->getUserVehicles($this->session->userID);
        $variablesArray = array('title' => '用户信息','user'=>$userInfo,'vehicleArray'=>$userVehicels);
        $this->load->view('user/info', $variablesArray);
    }

    //我的货源页
    public function freights()
    {
        $runningFreights = $this->freights_model->getUserFreights($this->session->userID,'进行中');
        $fishiedFreights = $this->freights_model->getUserFreights($this->session->userID,'已完成');
        $variablesArray = array('title' => '我的货源', 'runningFreights' => $runningFreights,'finishiedFreights'=>$fishiedFreights);
        $this->load->view('user/freights', $variablesArray);        
    }

    //意向接单页
    public function hopes()
    {
        $hopeFreights = $this->freight_franchisers_model->getHopeFreights($this->session->userID);
        $variablesArray = array('title' => '我想接单', 'hopeFreights' => $hopeFreights);
        $this->load->view('user/hopes', $variablesArray);
    }

    //我的运单页
    public function orders()
    {
        //未完成
        $runningOrders = $this->orders_model->getUserOrders($this->session->userID, '进行中');
        $fishiedOrders = $this->orders_model->getUserOrders($this->session->userID, '已完成');
        $variablesArray = array('title' => '我的运单', 'runningOrders' => $runningOrders, 'finishiedOrders' => $fishiedOrders);
        $this->load->view('user/orders', $variablesArray);
    }

    //credentialNumber验证 -- 用于form_validation回调验证
    //注意：必须有一个参数 -- CI 回调验证接口限制
    public function credentialNumber($value)
    {
        //credentialNumber是否已存在
        $credentialNumber=$this->input->post('credentialNumber');
        $checkResult=$this->users_model->isExistUser($credentialNumber,'');
        //返回值为aID(auto_increment)
        if($checkResult)
        {
            $this->form_validation->set_message('credentialNumber','身份证号码已存在，如您已注册请直接登录。');
            return false;
        }

        //是否是有效的身份证号 -- 接口调用待加入

        return true;
    }

    //密码强度规则不限制，影响用户体验，所以只验证两次输入密码是否相同
    //注意：必须至少有一个参数 -- 即该表单值，必须为public -- CI 回调验证接口限制
    public function validatePassword($userPassword,$ensurePassword)
    {
        if($userPassword!=$ensurePassword)
        {
            $this->form_validation->set_message('validatePassword','两次输入密码不一致！');
            return false;
        }
        return true;
    }

    //名值对数组设置session
    private function setSession($keyValueArray){
        foreach ($keyValueArray as $key=>$value){
            $this->session->$key=$value;
        }
    }

}
