<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Because Ci doesn't support multiple attribute of input, So extend it.
 * Note: The input name must be like name[]
 */
class Multiple_upload
{
    public function __construct()
    {

    }

    private $dataArray=array(); //封装CI upload->data()
    private $errorsArray=array();   //封装CI upload->display_errors()

    public function do_multiple_upload($fieldName,$CI_upload)
    {
        //默认执行成功
        $result=true;
        try
        {
            //判断是否multiple属性或name=name[]的方式上传的多个文件
            if(is_array($_FILES[$fieldName]['name']))
            {
                //循环文件数组另建$_FILES['fieldName...']以仍然可以健壮调用CI do_upload()来完成上传
                foreach ($_FILES[$fieldName]['name'] as $key=>$fileName)
                {
                    $tempFieldName=$fieldName.'['.$key.']';
                    $_FILES[$tempFieldName]=array(
                        'name'=>$fileName,
                        'type'=>$_FILES[$fieldName]['type'][$key],
                        'tmp_name'=>$_FILES[$fieldName]['tmp_name'][$key],
                        'error'=>$_FILES[$fieldName]['error'][$key],
                        'size'=>$_FILES[$fieldName]['size'][$key]
                    );

                    if($CI_upload->do_upload($tempFieldName))
                    {
                        $this->dataArray[$tempFieldName]=$CI_upload->data();
                    }
                    else
                    {
                        $this->errorsArray[$tempFieldName]=$CI_upload->display_errors();
                        $result=false;
                    }
                }
            }
            //否则非multple属性或单个文件改为调用CI的do_upload以保证程序的最大健壮性
            else
            {
                if($CI_upload->do_upload($fieldName))
                {
                    $this->dataArray=$CI_upload->data();
                }
                else
                {
                    $this->errorsArray[$fieldName]=$CI_upload->display_errors();
                    $result=false;
                }
            }
        }
        catch (Exception $e)
        {
            $this->errorsArray[$fieldName]=$e;
            return false;
        }

        return $result;
    }

    //从结果数组返回指定属性的数组
    public function multiple_data($attribute=null)
    {
        $result=array();

        //如果未提供参数则返回整个结果数组
        if($attribute===null)
        {
            $result=$this->dataArray;
        }
        //否则从结果数组取出该属性组成数组
        else
        {
            foreach ($this->dataArray as $key=>$data)
            {
                $result[$key]=$data[$attribute];
            }
        }
        return $result;
    }

    //将错误信息数组连接为用换行符分隔的字符串返回
    public function display_multiple_errors()
    {
        return implode("\n",$this->errorsArray);
    }
}