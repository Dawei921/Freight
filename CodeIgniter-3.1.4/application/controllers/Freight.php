<?php

/**
 * Class Freight
 * 发货、找货Controller
 */

class Freight extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->library('session');
        $this->load->library('form_validation');

        //加载自写类
        //CI不支持验证文件表单，此是自定义文件表单验证类
        $this->load->library('file_validation');

        $this->load->model('freights_model');
        $this->load->model('vehicle_model');
        $this->load->model('freight_franchisers_model');
        $this->load->model('orders_model');
        $this->load->model('users_model');
    }

    //发布货源
    public function publish()
    {
        //如果有提交表单 publishSubmit="true"
        if ($this->input->post('publishSubmit') != '') {
            //验证表单数据 -- 注意：必须用array('field'=>'value','label'=>'value','rules'=>'value')的绝对key/value形式，否则验证规则无效且不报错
            $this->form_validation->set_rules(array(
                array('field' => 'vehicleLongness', 'label' => '所需车长', 'rules' => 'required|integer'),
                array('field' => 'vehicleType', 'label' => '车型', 'rules' => 'required|exact_length[3]|numeric'),
                array('field' => 'beginProvince', 'label' => '出发：省', 'rules' => 'required'),
                array('field' => 'beginCity', 'label' => '出发：市', 'rules' => 'required'),
                array('field' => 'beginDistrict', 'label' => '出发：县/区', 'rules' => 'required'),
                array('field' => 'beginStreet', 'label' => '发货具体村庄/街道', 'rules' => 'required'),
                array('field' => 'beginPosition', 'label' => '出发：地图定位', 'rules' => 'required'),
                array('field' => 'endProvince', 'label' => '卸货：省', 'rules' => 'required'),
                array('field' => 'endCity', 'label' => '卸货：市', 'rules' => 'required'),
                array('field' => 'endDistrict', 'label' => '卸货：县/区', 'rules' => 'required'),
                array('field' => 'endStreet', 'label' => '卸货具体村庄/街道', 'rules' => 'required'),
                array('field' => 'endPosition', 'label' => '卸货：地图定位', 'rules' => 'required'),
                array('field' => 'expenses', 'label' => '运费', 'rules' => 'required|numeric'),
                array('field' => 'distance', 'label' => '相距公里', 'rules' => 'required|integer'),
                array('field' => 'cubage', 'label' => '所占立方', 'rules' => 'required|integer'),
                array('field' => 'weight', 'label' => '货重', 'rules' => 'required|integer'),
                array('field' => 'goodsType', 'label' => '货物类型', 'rules' => 'required'),
                array('field' => 'phone', 'label' => '装货责任人号码', 'rules' => 'required|min_length[7]|numeric')
            ));

            //CI不支持验证文件表单，此是自定义文件表单验证类
            $this->file_validation->set_rules('screen', '上传货物照片', 'required|file_type[jpg,png,gif]');

            //如果表单未通过验证或有文件未上传则返回提示并转到注册页 -- 注意顺序：先运行验证再与判断
            $result = $this->form_validation->run();
            $result = $this->file_validation->run() && $result;
            if (!$result) {
                $variablesArray = array('content' => '部分数据不满足录入要求，是否返回修改？', 'errors' => $this->file_validation->file_validation_errors());
                $this->load->view('prompt/confirm', $variablesArray);
            } //验证通过写入数据
            else {
                $result = $this->freights_model->publish();

                //如果写入成功 -- 提示并重新转到发货页面
                if ($result[0]) {
                    $variablesArray = array('content' => '发布成功！', 'url' => site_url('freight/publish'));
                    $this->load->view('prompt/alert', $variablesArray);
                } //否则 -- 提示失败，是否返回
                else {
                    $variablesArray = array('content' => '很抱歉，发布时遇到错误，您是否需要返回重试。', 'errors' => $result[1]);
                    $this->load->view('prompt/confirm', $variablesArray);
                }
            }
        } //无提交表单则直接转到 我要发货 页
        else {
            //车长列表
            $vehicleLongnessArray = $this->vehicle_model->getVehicleLongness();
            //车型列表
            $vehicleTypeArray = $this->vehicle_model->getVehicleTypes();
            $variablesArray = array('title' => '我要发货', 'vehicleLongnessArray' => $vehicleLongnessArray, 'vehicleTypeArray' => $vehicleTypeArray);
            $this->load->view('freight/publish', $variablesArray);
        }
    }

    //列出货源
    public function lists()
    {
        //如果请求json数据 -- 定位获取相应区域货源
        if (isset($_REQUEST['beginProvince'])) {
            $freights = $this->freights_model->getAllowOrderFreights($this->input->post('beginProvince'),$this->input->post('beginCity'));
            $freightsJson = json_encode($freights);
            echo $freightsJson;
        } //否则直接显示页面
        else {
            //车长列表
            $vehicleLongnessArray = $this->vehicle_model->getVehicleLongness();
            //车型列表
            $vehicleTypeArray = $this->vehicle_model->getVehicleTypes();
            $variablesArray = array('title' => '查看货源', 'vehicleLongnessArray' => $vehicleLongnessArray, 'vehicleTypeArray' => $vehicleTypeArray);
            $this->load->view('freight/lists', $variablesArray);
        }
    }

    //货源信息
    public function view($freightID = '')
    {
        //如果不存在运输车辆则提示用户先完善资料
        if(!isset($this->session->vehicleCount) || $this->session->vehicleCount==0){
            $variablesArray = array('content' => '请先完善车辆信息后方可接单','url'=>site_url('user/info#page_4'));
            $this->load->view('prompt/alert', $variablesArray);
        }//限制必须有freightID才可以进入
        elseif ($freightID != '') {
            //检索货源信息
            $freight= $this->freights_model->getFreightDetail(urldecode($freightID));
            //用户可接单车辆
            $userVehicels=$this->vehicle_model->getUserVehicles($this->session->userID);
            //检索货源反馈信息
            $franchisers = $this->freight_franchisers_model->getFranchisers($freight->freightID);
            $variablesArray = array('title' => '', 'freight' => $freight,'vehicles'=>$userVehicels,'franchisers'=>$franchisers);
            $this->load->view('freight/view', $variablesArray);
        }
    }

    //我想接单 -- ajax
    public function franchiser($freightID = '',$franchiser='')
    {
        //反馈不为空时进行处理
        if ($freightID != ''){
            //运费太低
            if ($franchiser == -1){
                $result = $this->freight_franchisers_model->freightFranchiser($freightID, $this->session->userID, $franchiser);
            }//我想接单
            elseif($franchiser==1){
                //验证表单数据
                $rules=array(array('field'=>'vehicle','label'=>'选择车辆','rules'=>'required'));
                //如果有添加驾驶员
                if(isset($_REQUEST['driver'])){
                    $rules[]=array('field'=>'driver[]','label'=>'驾驶员','rules'=>'required');
                    $rules[]=array('field'=>'drivingLicense[]','label'=>'驾驶证','rules'=>'required|exact_length[12]');
                }
                $this->form_validation->set_rules($rules);
                //如果表单未通过验证终止并返回验证失败信息
                if (!$this->form_validation->run()) {
                    echo validation_errors();
                    return;
                }

                $drivers='';
                //如果有添加驾驶员 -- 先写入驾驶员信息并回传记录aID
                if(isset($_REQUEST['driver'])) {
                    $driversArray=$this->input->post('driver');
                    $drivingLicensesArray=$this->input->post('drivingLicense');
                    foreach ($driversArray as $key => $driver) {
                        $result = $this->freight_franchisers_model->addDriver($drivingLicensesArray[$key], $driver, $this->session->userID);
                        if ($result[0]) {
                            $drivers = $drivers . ',' . $result[1];
                        }//如果失败终止执行返回出错信息
                        else {
                            echo $result[1];
                            return;
                        }
                    }
                    //去掉第一次多余的,号
                    $drivers=ltrim($drivers,',');
                }

                $result = $this->freight_franchisers_model->freightFranchiser($freightID, $this->session->userID, $franchiser,$this->input->post('vehicle'),$drivers);
            }

            //执行成功不返回任何信息
            if ($result[0]) {
                echo "";
            }//如果失败返回出错信息
            else {
                echo $result[1];
            }
        }
    }

    //同意接单 -- ajax
    public function reply($freightID = '',$franchiserID='')
    {
        //同意反馈不能为空
        if ($freightID != '' && $franchiserID != ''){
            $result = $this->freight_franchisers_model->freightReply($freightID,$franchiserID);
            //执行成功不返回任何信息
            if ($result[0]) {
                echo "";
            }//如果失败返回出错信息
            else {
                echo $result[1];
            }
        }
    }

}

