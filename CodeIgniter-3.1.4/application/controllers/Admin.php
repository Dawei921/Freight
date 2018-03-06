<?php

/**
 *
 */
class Admin extends CI_Controller
{
    public function __construct(){
        parent::__construct();
        $this->load->helper('url');
        $this->load->library('session');
        //加载自写类
        $this->load->model('users_model');
    }

    //管理控制台登录
    public function login()
    {
        //如果有进行登录 -- 提交表单 submit="true"
        if($this->input->post('loginSubmit')!='')
        {
            //当前只有一个用户名密码admin/admin，未建立数据表，因此只在此简单验证用户名密码是否正确
            if($this->input->post('userName')=='admin' && $this->input->post('userPassword')=='admin'){
                //创建session表示已管理员登录
                $this->session->adminUser='admin';
                //转到注册用户列表页面
                $this->users();
            }//否则提示用户名密码错误
            else{
                $variablesArray=array('content'=>'用户名或密码错误！','url'=>site_url('admin/login'));
                $this->load->view('prompt/alert',$variablesArray);
            }
        }
        //否则直接进入管理控制台登录页
        else
        {
            $variablesArray =array('title'=>'管理控制台');
            $this->load->view('admin/login', $variablesArray);
        }
    }

    //注册用户列表
    public function users(){
        //必须是管理员登录方可运行
        if(isset($this->session->adminUser)) {
            $registerUsers=$this->users_model->getRegisterUsers();
            $variablesArray =array('title'=>'管理控制台','users'=>$registerUsers);
            $this->load->view('admin/users', $variablesArray);
        }//否则提示后转到管理控制台登录页面
        else{
            $variablesArray=array('content'=>'您尚未登录！','url'=>site_url('admin/login'));
            $this->load->view('prompt/alert',$variablesArray);
        }
    }

    //用户注册信息审核页面
    public function verify($userID=null){
        //必须是管理员登录方可运行
        if(isset($this->session->adminUser)) {
            //如果有提交审核则处理 -- ajax
            if(isset($_REQUEST['verifySubmit'])){
                if($this->input->post('verifySubmit')=='不通过'){
                    //未通过必须填写原因
                    if(strlen($_REQUEST['reason'])==0){
                        $variablesArray=array('content'=>'如不通过请填写原因！','url'=>site_url('admin/verify'));
                        $this->load->view('prompt/alert',$variablesArray);
                    }
                    $recordStatus=-1;
                    $reason=$this->input->post('reason');
                }
                elseif ($this->input->post('verifySubmit')=='通过') {
                    $recordStatus=1;
                    $reason='';
                }
                $result=$this->users_model->userVerify($this->input->post('userID'),$recordStatus,$reason);
                //如果审核成功返回空以刷新该页
                if($result[0]){
                    echo '';
                }//否则返回错误消息
                else{
                    echo $result[1];
                }
            }//否则显示审核页面
            else{
                $userInfo=$this->users_model->getUserInfo($userID);
                $variablesArray = array('title' => '审核用户信息','user'=>$userInfo);
                $this->load->view('admin/verify', $variablesArray);
            }
        }//否则提示后转到管理控制台登录页面
        else{
            $variablesArray=array('content'=>'您尚未登录！','url'=>site_url('admin/login'));
            $this->load->view('prompt/alert',$variablesArray);
        }
    }






}