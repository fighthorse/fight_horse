<?php

/**
 * controller 
 *获取上传文件
 *分析数据成数组
 *数据库对比排重
 *导入数据库
 */

class mysqlConModel{

	private $dbModel ;
	private static $_instance;

	private function __construct()   
	{   
	    $this->dbModel = $this->getConnect(); 
	}   
	private function __clone()  
	{  
	}//覆盖__clone()方法，禁止克隆  


	public static function getInstance() {
    	if (! (self::$_instance instanceof self)) {
    		self::$_instance = new self ();
    	}
    	return self::$_instance;
    }

	public function find($name){
		$data = array();
		$sql = "select * from users where username = '".$name."' limit 0,1";
		$result = $this->dbModel->query($sql);
		while($row = $result -> fetch(PDO::FETCH_ASSOC)){ 
	        $data = $row;
	    }
	    return $data;
	}

	public function add($name,$time){

		$sql = "INSERT INTO users ( username, addtime) VALUES ( ".$name.", ".$time.") ";

		$result = $this->dbModel->exec($sql);
	    return $result;
	}

	public function getConnect(){
		try{
			//获取数据库配置
			$dbconfig = self::getconfig() ;
		 	$pdo = new PDO("mysql:host=localhost;dbname=mffc","root","root"); 
		    if (!$pdo) {
		      die('Could not connect DB' );
		    }
		    return $pdo ;
	    }catch(\Exception $e){
	    	die("Error :".$e->getMessage());
	    }
	}

	public function getconfig(){
		
		// 从文件中读取数据到PHP变量
		$json_string = file_get_contents('db.json');
		// 把JSON字符串转成PHP数组
		$data = json_decode($json_string, true);

		return $data;
	}
}


	function databack($status,$msg,$data=null){
		echo json_encode(array("status"=>$status,"message"=>$msg,"data"=>$data));
		die;
	}

	$db = mysqlConModel::getInstance();
	//验证excel
	if ($_FILES["file"]["error"] > 0)
	{
		databack(1,"上传文件错误". $_FILES["file"]["error"] );
	}
	$file_types = explode(".", $_FILES ['file'] ['name']);
    $file_type = $file_types [count($file_types) - 1];
    //判别是不是.xls .xlsx文件
    if (strtolower($file_type) != "xls"  && strtolower($file_type) != "xlsx") {
        databack(2,"上传文件格式错误: ". $file_type);
        die;
    }
	$tmp_name = $_FILES["file"]["tmp_name"] ;

	//解析文件
	$filename = date("YmdHis").rand(0,10).".csv";
    move_uploaded_file($tmp_name, "./$filename");
	$file_path = "./".$filename ;

    $success = $fail = $repeat = array();

	//PHPExcel To Do
	    require  "./PHPExcel.php";
	    //创建PHPExcel对象，注意，不能少了\
	    $PHPExcel=new \PHPExcel();
	    //如果excel文件后缀名为.xls，导入这个类
	    if(strtolower($file_type) == 'xls'){
	    	require  "./PHPExcel/Reader/Excel5.php";
	        $PHPReader=new \PHPExcel_Reader_Excel5();
	    }
	    //如果excel文件后缀名为.xlsx，导入这下类
	    if(strtolower($file_type) == 'xlsx'){
	    	require  "./PHPExcel/Reader/Excel2007.php";
	        $PHPReader=new \PHPExcel_Reader_Excel2007();
	    }
	    //载入文件
	    $PHPExcel=$PHPReader->load($file_path);
	    //获取表中的第一个工作表
	    $currentSheet=$PHPExcel->getSheet(0);
	    //获取总列数
	    $allColumn=$currentSheet->getHighestColumn();
	    //获取总行数
	    $allRow=$currentSheet->getHighestRow();
        
        try{

            $i=0;
            $data = array();
            //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
            for($currentRow=2;$currentRow<=$allRow;$currentRow++){
                //从哪列开始，A表示第一列
                for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
                    //数据坐标
                    $address=$currentColumn.$currentRow;
                    //读取到的数据，保存到数组$arr中
                    $arr[$currentRow][$currentColumn]=$currentSheet->getCell($address)->getValue();
                }
                if(!$arr[$currentRow]['A'] ){
                    continue;
                }

                $name = $arr[$currentRow]['A'];
                $result = $db->find($name );
                if($result){
                	$repeat[] = $name;
                }else{
                	$data[]=$name;
                }
                
            }
            if(empty($data)){
            	databack(1,"请填写数据后导入",null);
            } 
        }catch(Exception $e){
        	echo $e->getMessage();
        	die;
        }

    $time = date("Y-m-d H:i:s");
	foreach ($data as $key => $value) {
		$res =  $db->add($value, $time);
		if($res){
			$success[] = $value;
		}else{
			$fail[] = $value;
		}
	}
	unlink($file_path );
	$respon = array(
		"suc_num"  => count($success) ,
		"suc_info" => implode(",", $success) ,
		"rep_num" => count($repeat),
		"rep_info" => implode(",", $repeat),
		"fail_num" => count($fail),
		"fail_info" => implode(",", $fail),
		);
	databack(0,"上传成功",$respon);






