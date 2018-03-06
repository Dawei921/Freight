<?php

/**
 * Class Vehicle
 * 车辆、驾照 Controller
 */

class Vehicle extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->library('form_validation');

        //加载自写类
        //CI不支持验证文件表单，此是自定义文件表单验证类
        $this->load->library('file_validation');

        $this->load->model('vehicle_model');


        /*$this->load->helper('url');
        $this->load->model('users_model');
        $this->load->model('freights_model');
        $this->load->model('freight_franchisers_model');
        $this->load->model('orders_model');*/
    }

    //增加驾驶证 -- ajax
    public function addDrivingLicense(){
        //验证表单数据
        $this->form_validation->set_rules('drivingLicense','驾驶证号','required|exact_length[12]');
        $this->file_validation->set_rules('licensePhoto','驾驶证照片','required|file_type[jpg,png,gif]');
        //如果表单未通过验证或有文件未上传则返回提示 -- 注意顺序：先运行验证再与判断
        $result=$this->form_validation->run();
        $result=$this->file_validation->run() && $result;
        if(!$result)
        {
            echo validation_errors().$this->file_validation->file_validation_errors();
        }//验证通过写入数据
        else {
            $result=$this->vehicle_model->addDrivingLicense($this->session->userID);
            //如果成功返回空字符串
            if ($result[0]) {
                echo "";
            } //否则返回失败信息
            else {
                echo $result[1];
            }
        }        
    }

    //撤消驾驶证 -- ajax
    public function repealDrivingLicense($drivingLicenseID){
        $result=$this->vehicle_model->repealDrivingLicense($drivingLicenseID,$this->session->userID);
        //如果成功返回空字符串
        if ($result[0]) {
            echo "";
        } //否则返回失败信息
        else {
            echo $result[1];
        }
    }

    //添加车辆 -- ajax
    public function addVehicle(){
        //验证表单数据
        $this->form_validation->set_rules('licensePlate','行驶证号','required|exact_length[12]');
        $validateArray=array(
            array('field'=>'licensePlatePhoto','label'=>'行驶证照片','rules'=>'required|file_type[jpg,png,gif]'),
            array('field'=>'vehiclePhotos','label'=>'货车照片','rules'=>'required|file_type[jpg,png,gif]')
        );
        $this->file_validation->set_rules($validateArray);
        //如果表单未通过验证或有文件未上传则返回提示 -- 注意顺序：先运行验证再与判断
        $result=$this->form_validation->run();
        $result=$this->file_validation->run() && $result;
        if(!$result)
        {
            echo validation_errors().$this->file_validation->file_validation_errors();
        }//验证通过写入数据
        else {
            $result=$this->vehicle_model->addVehicle($this->session->userID);
            //如果成功返回空字符串
            if ($result[0]) {
                echo "";
            } //否则返回失败信息
            else {
                echo $result[1];
            }
        }
    }

    //撤消行驶证（车辆） -- ajax
    public function repealVehicle($vehicleID){
        $result=$this->vehicle_model->repealVehicle($vehicleID,$this->session->userID);
        //如果成功返回空字符串
        if ($result[0]) {
            echo "";
        } //否则返回失败信息
        else {
            echo $result[1];
        }
    }

}
