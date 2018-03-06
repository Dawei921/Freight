<?php

/**
 * Class Vehicle_model
 * 车辆信息
 */

class Vehicle_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        //连接数据库 -- 从配置文件
        $this->load->database();
        $this->load->library('upload');

        //加载自写类
        $this->load->model('public_model');
        //CI不支持multiple多文件上传，因此加入自定义多文件上传类
        $this->load->library('multiple_upload');

    }

    //增加驾驶证信息
    public function addDrivingLicense($owner){
        try
        {
            //先上传文件获取文件名再存入数据库
            //文件名使用userID_aID.扩展名（因为只有一个上传文件）
            //此设置默认会在已存在文件时自动在文件名后加一个数字来存储
            $aID=$this->public_model->get_auto_increment('driving_license');
            $uploadPath='upload/users/driving_license/';
            $uploadConf=array(
                'upload_path'=>FCPATH.$uploadPath,
                'allowed_types'=>'*',
                'file_name'=>$owner.'_'.$aID
            );
            $this->upload->initialize($uploadConf);
            $result=$this->upload->do_upload('licensePhoto');
            $filePath = $uploadPath . $this->upload->data('file_name');
            if(!$result){
                throw new Exception('文件上传遇到错误：'.$this->upload->display_errors());
            }

            //从输入类里取出表单数据 -- 在Controller里已验证通过
            //注意上传文件使用相对路径名存入数据库，这样在取出后要再加 base_url() 前缀
            $data=array(
                'userID'=>$owner,
                'drivingLicense'=>$this->input->post('drivingLicense'),
                'licensePhoto'=>$filePath
            );
            //写入驾驶证数据并同步更新用户表驾驶证标识 -- 使用事务以保持数据一致性
            $this->db->trans_start();
            $this->db->insert('driving_license',$data);
            $this->db->where('aID',$owner)->update('users',array('drivingLicense'=>$aID));
            if($this->db->affected_rows()==0){
                throw new Exception('写入数据库出错：没有匹配的用户数据。');
            }

            $this->db->trans_complete();
            if($this->db->trans_status() === FALSE){ //官方用法，无详细说明
                throw new Exception('写入数据库出错：'.$this->db->error());
            }

            return array(true,$aID);
        }
        catch (Exception $e)
        {
            //遇到错误取消操作，删除已上传文件
            if(isset($filePath)){unlink(FCPATH.$filePath);}
            return array(false,$e);
        }
    }

    //撤消驾驶证
    public function repealDrivingLicense($drivingLicenseID,$owner){
        try{
            //使用事务同步更新users表drivingLicense字段为空，任一表更新失败将抛出异常并回滚
            $this->db->trans_start();
            //只有属于该用户的且recordStatus=0正常状态的可撤消
            $this->db->where(array('aID'=>$drivingLicenseID,'userID'=>$owner,'recordStatus'=>0))->update('driving_license',array('recordStatus'=>-1));
            if($this->db->affected_rows()==0){
                throw new Exception('写入数据库出错：没有匹配的驾驶证数据。');
            }

            $this->db->where(array('aID'=>$owner,'drivingLicense'=>$drivingLicenseID))->update('users',array('drivingLicense'=>null));
            if($this->db->affected_rows()==0){
                throw new Exception('写入数据库出错：没有匹配的用户数据。');
            }

            $this->db->trans_complete();
            if($this->db->trans_status() === FALSE){ //官方用法，无详细说明
                throw new Exception('写入数据库出错：'.$this->db->error());
            }

            return array(true,'');
        }
        catch (Exception $e){
            return array(false,$e);
        }
    }

    //添加车辆
    public function addVehicle($owner){
        try
        {
            //先上传文件获取文件名再存入数据库
            //文件名使用userID_aID.扩展名（因为只有一个上传文件）
            //此设置默认会在已存在文件时自动在文件名后加一个数字来存储
            $aID=$this->public_model->get_auto_increment('vehicle');
            $uploadPath='upload/users/vehicle/';
            $uploadConf=array(
                'upload_path'=>FCPATH.$uploadPath,
                'allowed_types'=>'*',
                'file_name'=>$owner.'_'.$aID.'_license_'
            );
            $this->upload->initialize($uploadConf);
            $result=$this->upload->do_upload('licensePlatePhoto');
            if(!$result){
                throw new Exception('文件上传遇到错误：'.$this->upload->display_errors());
            }
            $licensePhotoPath = $uploadPath . $this->upload->data('file_name');

            $uploadConf['file_name']=$owner.'_'.$aID.'_vehicle_';
            $this->upload->initialize($uploadConf);
            $result=$this->multiple_upload->do_multiple_upload('vehiclePhotos',$this->upload);
            $uploadFilesArray=$this->multiple_upload->multiple_data('file_name');
            if(!$result){
                throw new Exception('文件上传遇到错误：'.$this->multiple_upload->dispaly_multiple_errors());
            }

            $separator=",".$uploadPath;
            $vehiclePhotosPath=implode($separator,$uploadFilesArray);
            $vehiclePhotosPath=$uploadPath.$vehiclePhotosPath;

            //从输入类里取出表单数据 -- 在Controller里已验证通过
            //注意上传文件使用相对路径名存入数据库，这样在取出后要再加 base_url() 前缀
            $data=array(
                'userID'=>$owner,
                'licensePlate'=>$this->input->post('licensePlate'),
                'licensePlatePhoto'=>$licensePhotoPath,
                'vehiclePhotos'=>$vehiclePhotosPath
            );
            //写入车辆数据并更新users表车辆数量vehicleCount -- 使用事务以保持数据一致性
            $this->db->trans_start();
            $this->db->insert('vehicles',$data);
            $this->db->set('vehicleCount','vehicleCount+1',false)->where('aID',$owner)->update('users');
            if($this->db->affected_rows()==0){
                throw new Exception('写入数据库出错：没有匹配的用户数据。');
            }

            $this->db->trans_complete();
            if($this->db->trans_status() === FALSE){ //官方用法，无详细说明
                throw new Exception('写入数据库出错：'.$this->db->error());
            }

            return array(true,$aID);
        }
        catch (Exception $e)
        {
            //遇到错误取消操作，删除已上传文件
            if(isset($licensePhotoPath)){unlink(FCPATH.$licensePhotoPath);}
            if(isset($uploadFilesArray)){
                foreach ($uploadFilesArray as $file){
                    unlink(FCPATH.$uploadPath.$file);
                }
            }

            return array(false,$e);
        }
    }

    //撤消行驶证（车辆）
    public function repealVehicle($vehicleID,$owner){
        try{
            //使用事务同步更新users表drivingLicense字段为空，任一表更新失败将抛出异常并回滚
            $this->db->trans_start();
            //只有属于该用户的且recordStatus=0正常状态的可撤消
            $this->db->where(array('aID'=>$vehicleID,'userID'=>$owner,'recordStatus'=>0))->update('vehicles',array('recordStatus'=>-1));
            if($this->db->affected_rows()==0){
                throw new Exception('写入数据库出错：没有匹配的行驶证数据。');
            }

            $this->db->set('vehicleCount','vehicleCount-1',false)->where(array('aID'=>$owner))->update('users');
            if($this->db->affected_rows()==0){
                throw new Exception('写入数据库出错：没有匹配的用户数据。');
            }

            $this->db->trans_complete();
            if($this->db->trans_status() === FALSE){ //官方用法，无详细说明
                throw new Exception('写入数据库出错：'.$this->db->error());
            }

            return array(true,'');
        }
        catch (Exception $e){
            return array(false,$e);
        }
    }

    //用户车辆信息
    public function getUserVehicles($userID){
        //属于用户的且状态为正常（recordStatus=0）的车辆
        return $this->db->select('aID,licensePlate')->where(array('userID'=>$userID,'recordStatus'=>0))->get('vehicles')->result();
    }

    //车长表
    public function getVehicleLongness()
    {
        return $this->db->get('vehicle_longness')->result();
    }

    //车型表
    public function getVehicleTypes()
    {
        return $this->db->get('vehicle_types')->result();
    }

}