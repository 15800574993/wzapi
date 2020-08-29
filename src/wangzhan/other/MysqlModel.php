<?php
namespace wangzhan\other;

use wangzhan\Func;
/*
 * 根据表生成模型文件
 **/
class MysqlModel{
	// 设置后坠
	public $suffix 			=	".php";
	// 配置
	private $config;
	// 请求地址
	public function __construct($config = array()){
		$this->config 		=	$config;
    }
	/**
	 * @var    根据表名称生成模型文件
	 * @return [type] [description]
	 */
	public function exeAddModel($title,$path,$pk,$prefix = "",$modelPath = "app\\model") {
		// 将下划线转驼峰
		$MyModel 			=	Func::line_tohump($title);
		$MyName 			=	$MyModel.$this->suffix;
		$MyPath 			=	$path."/".$MyName;
		Func::mkdirs($path);
		if (file_exists($MyPath)) {
			return false;
		}	
		$MyFile 			= 	fopen($MyPath, "w") or die("Unable to open file!");
		$model 				= 	$this->ModelTxt($MyModel,$title,$pk,$prefix,$modelPath);
		fwrite($MyFile, $model);
		fclose($MyFile);


		return true;
	}

	/**
	 * @var    执行全部sql语句
	 * @param  [type]  [columnss]  	数组表数据结构
	 * @param  [type]  [path]  		模型存放的路径
	 * @param  [type]  [prefix]  	表前缀
	 * @param  [type]  [modelPath]  命名空间
	 * @return [type]      [description]
	 * $data	 		=	Db::query(" show tables from `xiaoshuo`");
     *	$tables 		=	array();
     *	foreach ($data as $key => $value) {
     * 		$table 		=	($value['Tables_in_xiaoshuo']);
     *		$tables[$table]	=	Db::query(" show COLUMNS from `".$table."`");
   	 * 	}
   	 * 	$this->wzapi->getMysqlModel()->exeAllModel($tables,"/www/web/xiaoshuo.caoyujie.com/app/model","cd_","app\\model");
	 */
	public function exeAllModel($columnss,$path,$prefix = "",$modelPath = "") {
		if (!is_array($columnss)){return false;} 
		$tables 		=	array();
		foreach ($columnss as $key => $value) {
			$arr 				=	array();
			$arr['table']		=	$key;
			if ($prefix) {
				$arr['table']	=	ltrim($key,$prefix);
			}
			$arr['id']			=	"";
			foreach ($value as $k => $v) {
				if ($v['Extra'] == "auto_increment") {
					$arr['id']	=	$v['Field'];
				}
			}
			$tables[]			=	$arr;
		}
		$ret 					=	array();
		foreach ($tables as $key => $value) {
			$ret[]				=	$this->exeAddModel($value['table'],$path,$value['id'],$prefix,$modelPath);
		}

		return $ret;
		
	}


	/**
	 * @var  模型文件内容
	 */
	public function ModelTxt($MyModel,$title,$pk,$prefix,$modelPath) {
		$str 		=	"<?php
namespace ".$modelPath.";
use think\Model;
//+--------------------------------------------------
//|	生成时间  ".(date("Y-m-d H:i:s"))."
//+--------------------------------------------------
class ".$MyModel." extends Model
{
    public          \$table          = '".$prefix.$title."';
    public          \$pk             = '".$pk."';
    public static   \$p_table        = '".$prefix.$title."';
}

";
		return $str;
	}


}