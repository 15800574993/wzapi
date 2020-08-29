<?php
namespace app\api\controller;
use think\facade\Db;

// 首页相关数据

class Home extends Header{
    /*
     * @api post Home/getHome  | 获取首页数据接口1  
     * @apiDescription  首页顶部固定参数配置
     * @apiHeader {String} [HTTP_TOKEN]       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {float}          results.my_balance          | 我的余额
     * @apiReturn (返回的数据) {float}          results.yesterday_income    | 昨天收入
     * @apiReturn (返回的数据) {float}          results.novice_guide        | 新手引导地址
     * @apiReturn (返回的数据) {array}          results.ad_list             | 消息列表数组
     * @apiReturn (返回的数据) {int}            results.ad_list.ad_id       | 消息主键
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_url      | 跳转地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_img      | 图片地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_title    | 消息内容
     * @apiReturn (返回的数据) {array}          results.banner              | 轮播图数组
     * @apiReturn (返回的数据) {string}         results.banner.image_url    | 图片地址
     * @apiReturn (返回的数据) {string}         results.banner.url          | 跳转地址
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"获取成功","results":{"my_balance":"0.00","yesterday_income":0,"ad_list":[{"ad_id":1,"ad_url":"测试","ad_img":"测试","ad_title":"测试"}],"novice_guide":"http://mp.weixin.qq.com/mp/video?__biz=MzIyODYzNDgwMw==&mid=100000019&sn=90a96745964946090a89c6b9d3992bc5&vid=wxv_1366965046735224833&idx=1&vidsn=81f90d58847b3331d7b4385af7edaae4&fromid=1&scene=18&xtrack=1#wechat_redirect","banner":[{"image_url":"https://res.appgan.com/admin/202006/202006022212140225401.png","url":"https://mp.weixin.qq.com/s/8-MbaArBuPIrXYNUnmoCHQ"}],"data":[]}}
     **/
    public function getHome() {
        $ret                        =   array();
        // 我的余额
        $ret['my_balance']          =   $this->member_info['amount'];
        // 昨天收入
        $ret['yesterday_income']    =   $this->exeYesterdayIncome();
        $ret["novice_guide"]        =   $this->exeHtml(Db::name("config")->where("name","novice_guide")->value("data"));
        $ret['ad_list']             =   $this->getAdData();
		$ret['banner']              =   $this->exeBanner();
        
        return_json("1","SUCCESS",$ret);
    }
    
    /*
     * @api {post} Home/getProgramData  | 小程序列表接口  
     * @apiVersion 1.0.0
     * @apiDescription  小程序列表带分页
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiParam (输入参数：) {int}         
     * @apiParam (输入参数：) {int}          [page]             | 页数
     * @apiParam (输入参数：) {int}          [platform_type]    | 平台类型  0 字节   1微信   2 QQ  3 H5   4 app 默认0
     * @apiParam (输入参数：) {int}          [title]            | 模糊查询小程序
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {array}          results.data | 小程序数组
     * @apiReturn (返回的数据) {int}            results.data.id | 小程序系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_id | 小程序id
     * @apiReturn (返回的数据) {string}            results.data.title       | 小程序名称
     * @apiReturn (返回的数据) {string}            results.data.image_url       | 小程序logo
     * @apiReturn (返回的数据) {string}            results.data.description       | 小程序描述
     * @apiReturn (返回的数据) {string}            results.data.system_type       | 小程序系统类型android|0,ios|1  双端逗号并接
     * @apiReturn (返回的数据) {string}            results.data.client_type       | 小程序抖音|0,头条|1,皮皮虾|2  只有字节平台有这个值
     * @apiReturn (返回的数据) {string}            results.data.total_prop       | 小程序总分成
     * @apiReturn (返回的数据) {string}            results.data.role_id       | 小程序可推权限 0/1 普通用户  2 vip用户  3 合伙人 向下兼容
     * @apiReturn (返回的数据) {string}            results.data.status       | 小程序状态 待发布|201,正常|200,不可用|0,广告佣金不足下架|101,封禁|100
     * @apiReturn (返回的数据) {string}            results.data.yesterday_top       | 小程序昨日最高收益
     * @apiReturn (返回的数据) {string}            results.data.program_modular_total       | 小程序模块数量
     * @apiReturn (返回的数据) {string}            results.data.program_modular       | 小程序模块数组
     * @apiReturn (返回的数据) {string}            results.data.program_modular.pm_id       | 小程序模块系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_modular.img       | 小程序模块图片
     * @apiReturn (返回的数据) {string}            results.data.program_modular.title       | 小程序模块标题
     * @apiReturn (返回的数据) {string}            results.data.program_modular.subdescribe       | 小程序模块描述
     * @apiReturn (返回的数据) {object}            results.info       | 分数数据对象
     * @apiReturn (返回的数据) {object}            results.info.page       | 当前页数
     * @apiReturn (返回的数据) {object}            results.info.total_pages       | 总页数
     * @apiReturn (返回的数据) {object}            results.info.total_count       | 总条数
     * @apiReturn (返回的数据) {object}            results.info.total_results       | 当前显示数
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"SUCCESS","results":{"data":[{"id":38,"program_id":"aaj7yt","title":"同年同月同日生查询","image_url":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","description":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！","system_type":"0,1","client_type":"0,1","total_prop":"0.95","role_id":1,"status":200,"yesterday_top":"4896","program_modular_total":1,"program_modular":[{"pm_id":190,"img":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","title":"同年同月同日生查询","subdescribe":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！"}]}]},"info":{"page":1,"total_pages":8,"total_count":15,"total_results":2}
}
     **/
    public function getProgramData() {
        list($start,$end)           =   get_limit(1);
        $platform_type              =   get_post("platform_type/d",0);
        $title                      =   get_post("title","");
        $field                      =   array(
            "id","program_id","title","image_url","description",  
            "system_type","client_type","total_prop","role_id",
            "status"
        );
        $where                      =   array();
        $where[]                    =   ["platform_type","=",$platform_type];
        if ($title) {
            $where[]                =   ["title","like","%".$title."%"];
        }
        // $where[]                    =   ["status","=",200];
        $where[]                    =   ["pid","=",0];
        $total                      =   Db::name("program")->where($where)->count();
        $data                       =   Db::name("program")->where($where)->field($field)->limit($start,$end)->order("sort desc")->select();
        foreach ($data as $key=>$value) {
            // 昨日最高收益 缓存到凌晨
            $value["yesterday_top"]     =   cache("yesterday_top_".$value["program_id"]);
		    if (!$value["yesterday_top"] or $value["yesterday_top"]=="") {
		        $value["yesterday_top"] =   (string)rand(999,5999);
		        $cacheTimt              =   (24 * 3600)- (time() - strtotime('today'));
		        cache("yesterday_top_".$value["program_id"],$value["yesterday_top"],$cacheTimt);
		    }
		    $value['program_modular_total'] =   $this->exeProgramData($value,1);
            $value['program_modular']       =   $this->exeProgramData($value);
            
            $data[$key]                     =   $value;
        }
        
        $info                       =   get_page($total,$data);
        
        
        return_json("1","SUCCESS",array(),$data,$info);
        
        
    }

    /*
     * @api {post} Home/getHome  | 获取首页数据接口  
     * @apiVersion 1.0.0
     * @apiDescription  首页顶部固定参数配置
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {float}          results.my_balance          | 我的余额
     * @apiReturn (返回的数据) {float}          results.yesterday_income    | 昨天收入
     * @apiReturn (返回的数据) {float}          results.novice_guide        | 新手引导地址
     * @apiReturn (返回的数据) {array}          results.ad_list             | 消息列表数组
     * @apiReturn (返回的数据) {int}            results.ad_list.ad_id       | 消息主键
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_url      | 跳转地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_img      | 图片地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_title    | 消息内容
     * @apiReturn (返回的数据) {array}          results.banner              | 轮播图数组
     * @apiReturn (返回的数据) {string}         results.banner.image_url    | 图片地址
     * @apiReturn (返回的数据) {string}         results.banner.url          | 跳转地址
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"获取成功","results":{"my_balance":"0.00","yesterday_income":0,"ad_list":[{"ad_id":1,"ad_url":"测试","ad_img":"测试","ad_title":"测试"}],"novice_guide":"http://mp.weixin.qq.com/mp/video?__biz=MzIyODYzNDgwMw==&mid=100000019&sn=90a96745964946090a89c6b9d3992bc5&vid=wxv_1366965046735224833&idx=1&vidsn=81f90d58847b3331d7b4385af7edaae4&fromid=1&scene=18&xtrack=1#wechat_redirect","banner":[{"image_url":"https://res.appgan.com/admin/202006/202006022212140225401.png","url":"https://mp.weixin.qq.com/s/8-MbaArBuPIrXYNUnmoCHQ"}],"data":[]}}
     **/
    public function getHome() {
        $ret                        =   array();
        // 我的余额
        $ret['my_balance']          =   $this->member_info['amount'];
        // 昨天收入
        $ret['yesterday_income']    =   $this->exeYesterdayIncome();
        $ret["novice_guide"]        =   $this->exeHtml(Db::name("config")->where("name","novice_guide")->value("data"));
        $ret['ad_list']             =   $this->getAdData();
        $ret['banner']              =   $this->exeBanner();
        
        return_json("1","SUCCESS",$ret);
    }
    
    /*
     * @api {post} Home/getProgramData  | 小程序列表接口  
     * @apiVersion 1.0.0
     * @apiDescription  小程序列表带分页
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiParam (输入参数：) {int}          [count]            | 每页显示数量
     * @apiParam (输入参数：) {int}          [page]             | 页数
     * @apiParam (输入参数：) {int}          [platform_type]    | 平台类型  0 字节   1微信   2 QQ  3 H5   4 app 默认0
     * @apiParam (输入参数：) {int}          [title]            | 模糊查询小程序
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {array}          results.data | 小程序数组
     * @apiReturn (返回的数据) {int}            results.data.id | 小程序系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_id | 小程序id
     * @apiReturn (返回的数据) {string}            results.data.title       | 小程序名称
     * @apiReturn (返回的数据) {string}            results.data.image_url       | 小程序logo
     * @apiReturn (返回的数据) {string}            results.data.description       | 小程序描述
     * @apiReturn (返回的数据) {string}            results.data.system_type       | 小程序系统类型android|0,ios|1  双端逗号并接
     * @apiReturn (返回的数据) {string}            results.data.client_type       | 小程序抖音|0,头条|1,皮皮虾|2  只有字节平台有这个值
     * @apiReturn (返回的数据) {string}            results.data.total_prop       | 小程序总分成
     * @apiReturn (返回的数据) {string}            results.data.role_id       | 小程序可推权限 0/1 普通用户  2 vip用户  3 合伙人 向下兼容
     * @apiReturn (返回的数据) {string}            results.data.status       | 小程序状态 待发布|201,正常|200,不可用|0,广告佣金不足下架|101,封禁|100
     * @apiReturn (返回的数据) {string}            results.data.yesterday_top       | 小程序昨日最高收益
     * @apiReturn (返回的数据) {string}            results.data.program_modular_total       | 小程序模块数量
     * @apiReturn (返回的数据) {string}            results.data.program_modular       | 小程序模块数组
     * @apiReturn (返回的数据) {string}            results.data.program_modular.pm_id       | 小程序模块系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_modular.img       | 小程序模块图片
     * @apiReturn (返回的数据) {string}            results.data.program_modular.title       | 小程序模块标题
     * @apiReturn (返回的数据) {string}            results.data.program_modular.subdescribe       | 小程序模块描述
     * @apiReturn (返回的数据) {object}            results.info       | 分数数据对象
     * @apiReturn (返回的数据) {object}            results.info.page       | 当前页数
     * @apiReturn (返回的数据) {object}            results.info.total_pages       | 总页数
     * @apiReturn (返回的数据) {object}            results.info.total_count       | 总条数
     * @apiReturn (返回的数据) {object}            results.info.total_results       | 当前显示数
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"SUCCESS","results":{"data":[{"id":38,"program_id":"aaj7yt","title":"同年同月同日生查询","image_url":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","description":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！","system_type":"0,1","client_type":"0,1","total_prop":"0.95","role_id":1,"status":200,"yesterday_top":"4896","program_modular_total":1,"program_modular":[{"pm_id":190,"img":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","title":"同年同月同日生查询","subdescribe":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！"}]}]},"info":{"page":1,"total_pages":8,"total_count":15,"total_results":2}
}
     **/
    public function getProgramData() {
        list($start,$end)           =   get_limit(1);
        $platform_type              =   get_post("platform_type/d",0);
        $title                      =   get_post("title","");
        $field                      =   array(
            "id","program_id","title","image_url","description",  
            "system_type","client_type","total_prop","role_id",
            "status"
        );
        $where                      =   array();
        $where[]                    =   ["platform_type","=",$platform_type];
        if ($title) {
            $where[]                =   ["title","like","%".$title."%"];
        }
        // $where[]                    =   ["status","=",200];
        $where[]                    =   ["pid","=",0];
        $total                      =   Db::name("program")->where($where)->count();
        $data                       =   Db::name("program")->where($where)->field($field)->limit($start,$end)->order("sort desc")->select();
        foreach ($data as $key=>$value) {
            // 昨日最高收益 缓存到凌晨
            $value["yesterday_top"]     =   cache("yesterday_top_".$value["program_id"]);
            if (!$value["yesterday_top"] or $value["yesterday_top"]=="") {
                $value["yesterday_top"] =   (string)rand(999,5999);
                $cacheTimt              =   (24 * 3600)- (time() - strtotime('today'));
                cache("yesterday_top_".$value["program_id"],$value["yesterday_top"],$cacheTimt);
            }
            $value['program_modular_total'] =   $this->exeProgramData($value,1);
            $value['program_modular']       =   $this->exeProgramData($value);
            
            $data[$key]                     =   $value;
        }
        
        $info                       =   get_page($total,$data);
        
        
        return_json("1","SUCCESS",array(),$data,$info);
        
        
    }

    /*
     * @api {post} Home/getHome  | 获取首页数据接口  
     * @apiVersion 1.0.0
     * @apiDescription  首页顶部固定参数配置
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {float}          results.my_balance          | 我的余额
     * @apiReturn (返回的数据) {float}          results.yesterday_income    | 昨天收入
     * @apiReturn (返回的数据) {float}          results.novice_guide        | 新手引导地址
     * @apiReturn (返回的数据) {array}          results.ad_list             | 消息列表数组
     * @apiReturn (返回的数据) {int}            results.ad_list.ad_id       | 消息主键
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_url      | 跳转地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_img      | 图片地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_title    | 消息内容
     * @apiReturn (返回的数据) {array}          results.banner              | 轮播图数组
     * @apiReturn (返回的数据) {string}         results.banner.image_url    | 图片地址
     * @apiReturn (返回的数据) {string}         results.banner.url          | 跳转地址
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"获取成功","results":{"my_balance":"0.00","yesterday_income":0,"ad_list":[{"ad_id":1,"ad_url":"测试","ad_img":"测试","ad_title":"测试"}],"novice_guide":"http://mp.weixin.qq.com/mp/video?__biz=MzIyODYzNDgwMw==&mid=100000019&sn=90a96745964946090a89c6b9d3992bc5&vid=wxv_1366965046735224833&idx=1&vidsn=81f90d58847b3331d7b4385af7edaae4&fromid=1&scene=18&xtrack=1#wechat_redirect","banner":[{"image_url":"https://res.appgan.com/admin/202006/202006022212140225401.png","url":"https://mp.weixin.qq.com/s/8-MbaArBuPIrXYNUnmoCHQ"}],"data":[]}}
     **/
    public function getHome() {
        $ret                        =   array();
        // 我的余额
        $ret['my_balance']          =   $this->member_info['amount'];
        // 昨天收入
        $ret['yesterday_income']    =   $this->exeYesterdayIncome();
        $ret["novice_guide"]        =   $this->exeHtml(Db::name("config")->where("name","novice_guide")->value("data"));
        $ret['ad_list']             =   $this->getAdData();
        $ret['banner']              =   $this->exeBanner();
        
        return_json("1","SUCCESS",$ret);
    }
    
    /*
     * @api {post} Home/getProgramData  | 小程序列表接口  
     * @apiVersion 1.0.0
     * @apiDescription  小程序列表带分页
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiParam (输入参数：) {int}          [count]            | 每页显示数量
     * @apiParam (输入参数：) {int}          [page]             | 页数
     * @apiParam (输入参数：) {int}          [platform_type]    | 平台类型  0 字节   1微信   2 QQ  3 H5   4 app 默认0
     * @apiParam (输入参数：) {int}          [title]            | 模糊查询小程序
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {array}          results.data | 小程序数组
     * @apiReturn (返回的数据) {int}            results.data.id | 小程序系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_id | 小程序id
     * @apiReturn (返回的数据) {string}            results.data.title       | 小程序名称
     * @apiReturn (返回的数据) {string}            results.data.image_url       | 小程序logo
     * @apiReturn (返回的数据) {string}            results.data.description       | 小程序描述
     * @apiReturn (返回的数据) {string}            results.data.system_type       | 小程序系统类型android|0,ios|1  双端逗号并接
     * @apiReturn (返回的数据) {string}            results.data.client_type       | 小程序抖音|0,头条|1,皮皮虾|2  只有字节平台有这个值
     * @apiReturn (返回的数据) {string}            results.data.total_prop       | 小程序总分成
     * @apiReturn (返回的数据) {string}            results.data.role_id       | 小程序可推权限 0/1 普通用户  2 vip用户  3 合伙人 向下兼容
     * @apiReturn (返回的数据) {string}            results.data.status       | 小程序状态 待发布|201,正常|200,不可用|0,广告佣金不足下架|101,封禁|100
     * @apiReturn (返回的数据) {string}            results.data.yesterday_top       | 小程序昨日最高收益
     * @apiReturn (返回的数据) {string}            results.data.program_modular_total       | 小程序模块数量
     * @apiReturn (返回的数据) {string}            results.data.program_modular       | 小程序模块数组
     * @apiReturn (返回的数据) {string}            results.data.program_modular.pm_id       | 小程序模块系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_modular.img       | 小程序模块图片
     * @apiReturn (返回的数据) {string}            results.data.program_modular.title       | 小程序模块标题
     * @apiReturn (返回的数据) {string}            results.data.program_modular.subdescribe       | 小程序模块描述
     * @apiReturn (返回的数据) {object}            results.info       | 分数数据对象
     * @apiReturn (返回的数据) {object}            results.info.page       | 当前页数
     * @apiReturn (返回的数据) {object}            results.info.total_pages       | 总页数
     * @apiReturn (返回的数据) {object}            results.info.total_count       | 总条数
     * @apiReturn (返回的数据) {object}            results.info.total_results       | 当前显示数
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"SUCCESS","results":{"data":[{"id":38,"program_id":"aaj7yt","title":"同年同月同日生查询","image_url":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","description":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！","system_type":"0,1","client_type":"0,1","total_prop":"0.95","role_id":1,"status":200,"yesterday_top":"4896","program_modular_total":1,"program_modular":[{"pm_id":190,"img":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","title":"同年同月同日生查询","subdescribe":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！"}]}]},"info":{"page":1,"total_pages":8,"total_count":15,"total_results":2}
}
     **/
    public function getProgramData() {
        list($start,$end)           =   get_limit(1);
        $platform_type              =   get_post("platform_type/d",0);
        $title                      =   get_post("title","");
        $field                      =   array(
            "id","program_id","title","image_url","description",  
            "system_type","client_type","total_prop","role_id",
            "status"
        );
        $where                      =   array();
        $where[]                    =   ["platform_type","=",$platform_type];
        if ($title) {
            $where[]                =   ["title","like","%".$title."%"];
        }
        // $where[]                    =   ["status","=",200];
        $where[]                    =   ["pid","=",0];
        $total                      =   Db::name("program")->where($where)->count();
        $data                       =   Db::name("program")->where($where)->field($field)->limit($start,$end)->order("sort desc")->select();
        foreach ($data as $key=>$value) {
            // 昨日最高收益 缓存到凌晨
            $value["yesterday_top"]     =   cache("yesterday_top_".$value["program_id"]);
            if (!$value["yesterday_top"] or $value["yesterday_top"]=="") {
                $value["yesterday_top"] =   (string)rand(999,5999);
                $cacheTimt              =   (24 * 3600)- (time() - strtotime('today'));
                cache("yesterday_top_".$value["program_id"],$value["yesterday_top"],$cacheTimt);
            }
            $value['program_modular_total'] =   $this->exeProgramData($value,1);
            $value['program_modular']       =   $this->exeProgramData($value);
            
            $data[$key]                     =   $value;
        }
        
        $info                       =   get_page($total,$data);
        
        
        return_json("1","SUCCESS",array(),$data,$info);
        
        
    }

    /*
     * @api {post} Home/getHome  | 获取首页数据接口  
     * @apiVersion 1.0.0
     * @apiDescription  首页顶部固定参数配置
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {float}          results.my_balance          | 我的余额
     * @apiReturn (返回的数据) {float}          results.yesterday_income    | 昨天收入
     * @apiReturn (返回的数据) {float}          results.novice_guide        | 新手引导地址
     * @apiReturn (返回的数据) {array}          results.ad_list             | 消息列表数组
     * @apiReturn (返回的数据) {int}            results.ad_list.ad_id       | 消息主键
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_url      | 跳转地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_img      | 图片地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_title    | 消息内容
     * @apiReturn (返回的数据) {array}          results.banner              | 轮播图数组
     * @apiReturn (返回的数据) {string}         results.banner.image_url    | 图片地址
     * @apiReturn (返回的数据) {string}         results.banner.url          | 跳转地址
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"获取成功","results":{"my_balance":"0.00","yesterday_income":0,"ad_list":[{"ad_id":1,"ad_url":"测试","ad_img":"测试","ad_title":"测试"}],"novice_guide":"http://mp.weixin.qq.com/mp/video?__biz=MzIyODYzNDgwMw==&mid=100000019&sn=90a96745964946090a89c6b9d3992bc5&vid=wxv_1366965046735224833&idx=1&vidsn=81f90d58847b3331d7b4385af7edaae4&fromid=1&scene=18&xtrack=1#wechat_redirect","banner":[{"image_url":"https://res.appgan.com/admin/202006/202006022212140225401.png","url":"https://mp.weixin.qq.com/s/8-MbaArBuPIrXYNUnmoCHQ"}],"data":[]}}
     **/
    public function getHome() {
        $ret                        =   array();
        // 我的余额
        $ret['my_balance']          =   $this->member_info['amount'];
        // 昨天收入
        $ret['yesterday_income']    =   $this->exeYesterdayIncome();
        $ret["novice_guide"]        =   $this->exeHtml(Db::name("config")->where("name","novice_guide")->value("data"));
        $ret['ad_list']             =   $this->getAdData();
        $ret['banner']              =   $this->exeBanner();
        
        return_json("1","SUCCESS",$ret);
    }
    
    /*
     * @api {post} Home/getProgramData  | 小程序列表接口  
     * @apiVersion 1.0.0
     * @apiDescription  小程序列表带分页
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiParam (输入参数：) {int}          [count]            | 每页显示数量
     * @apiParam (输入参数：) {int}          [page]             | 页数
     * @apiParam (输入参数：) {int}          [platform_type]    | 平台类型  0 字节   1微信   2 QQ  3 H5   4 app 默认0
     * @apiParam (输入参数：) {int}          [title]            | 模糊查询小程序
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {array}          results.data | 小程序数组
     * @apiReturn (返回的数据) {int}            results.data.id | 小程序系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_id | 小程序id
     * @apiReturn (返回的数据) {string}            results.data.title       | 小程序名称
     * @apiReturn (返回的数据) {string}            results.data.image_url       | 小程序logo
     * @apiReturn (返回的数据) {string}            results.data.description       | 小程序描述
     * @apiReturn (返回的数据) {string}            results.data.system_type       | 小程序系统类型android|0,ios|1  双端逗号并接
     * @apiReturn (返回的数据) {string}            results.data.client_type       | 小程序抖音|0,头条|1,皮皮虾|2  只有字节平台有这个值
     * @apiReturn (返回的数据) {string}            results.data.total_prop       | 小程序总分成
     * @apiReturn (返回的数据) {string}            results.data.role_id       | 小程序可推权限 0/1 普通用户  2 vip用户  3 合伙人 向下兼容
     * @apiReturn (返回的数据) {string}            results.data.status       | 小程序状态 待发布|201,正常|200,不可用|0,广告佣金不足下架|101,封禁|100
     * @apiReturn (返回的数据) {string}            results.data.yesterday_top       | 小程序昨日最高收益
     * @apiReturn (返回的数据) {string}            results.data.program_modular_total       | 小程序模块数量
     * @apiReturn (返回的数据) {string}            results.data.program_modular       | 小程序模块数组
     * @apiReturn (返回的数据) {string}            results.data.program_modular.pm_id       | 小程序模块系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_modular.img       | 小程序模块图片
     * @apiReturn (返回的数据) {string}            results.data.program_modular.title       | 小程序模块标题
     * @apiReturn (返回的数据) {string}            results.data.program_modular.subdescribe       | 小程序模块描述
     * @apiReturn (返回的数据) {object}            results.info       | 分数数据对象
     * @apiReturn (返回的数据) {object}            results.info.page       | 当前页数
     * @apiReturn (返回的数据) {object}            results.info.total_pages       | 总页数
     * @apiReturn (返回的数据) {object}            results.info.total_count       | 总条数
     * @apiReturn (返回的数据) {object}            results.info.total_results       | 当前显示数
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"SUCCESS","results":{"data":[{"id":38,"program_id":"aaj7yt","title":"同年同月同日生查询","image_url":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","description":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！","system_type":"0,1","client_type":"0,1","total_prop":"0.95","role_id":1,"status":200,"yesterday_top":"4896","program_modular_total":1,"program_modular":[{"pm_id":190,"img":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","title":"同年同月同日生查询","subdescribe":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！"}]}]},"info":{"page":1,"total_pages":8,"total_count":15,"total_results":2}
}
     **/
    public function getProgramData() {
        list($start,$end)           =   get_limit(1);
        $platform_type              =   get_post("platform_type/d",0);
        $title                      =   get_post("title","");
        $field                      =   array(
            "id","program_id","title","image_url","description",  
            "system_type","client_type","total_prop","role_id",
            "status"
        );
        $where                      =   array();
        $where[]                    =   ["platform_type","=",$platform_type];
        if ($title) {
            $where[]                =   ["title","like","%".$title."%"];
        }
        // $where[]                    =   ["status","=",200];
        $where[]                    =   ["pid","=",0];
        $total                      =   Db::name("program")->where($where)->count();
        $data                       =   Db::name("program")->where($where)->field($field)->limit($start,$end)->order("sort desc")->select();
        foreach ($data as $key=>$value) {
            // 昨日最高收益 缓存到凌晨
            $value["yesterday_top"]     =   cache("yesterday_top_".$value["program_id"]);
            if (!$value["yesterday_top"] or $value["yesterday_top"]=="") {
                $value["yesterday_top"] =   (string)rand(999,5999);
                $cacheTimt              =   (24 * 3600)- (time() - strtotime('today'));
                cache("yesterday_top_".$value["program_id"],$value["yesterday_top"],$cacheTimt);
            }
            $value['program_modular_total'] =   $this->exeProgramData($value,1);
            $value['program_modular']       =   $this->exeProgramData($value);
            
            $data[$key]                     =   $value;
        }
        
        $info                       =   get_page($total,$data);
        
        
        return_json("1","SUCCESS",array(),$data,$info);
        
        
    }

    /*
     * @api {post} Home/getHome  | 获取首页数据接口  
     * @apiVersion 1.0.0
     * @apiDescription  首页顶部固定参数配置
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {float}          results.my_balance          | 我的余额
     * @apiReturn (返回的数据) {float}          results.yesterday_income    | 昨天收入
     * @apiReturn (返回的数据) {float}          results.novice_guide        | 新手引导地址
     * @apiReturn (返回的数据) {array}          results.ad_list             | 消息列表数组
     * @apiReturn (返回的数据) {int}            results.ad_list.ad_id       | 消息主键
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_url      | 跳转地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_img      | 图片地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_title    | 消息内容
     * @apiReturn (返回的数据) {array}          results.banner              | 轮播图数组
     * @apiReturn (返回的数据) {string}         results.banner.image_url    | 图片地址
     * @apiReturn (返回的数据) {string}         results.banner.url          | 跳转地址
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"获取成功","results":{"my_balance":"0.00","yesterday_income":0,"ad_list":[{"ad_id":1,"ad_url":"测试","ad_img":"测试","ad_title":"测试"}],"novice_guide":"http://mp.weixin.qq.com/mp/video?__biz=MzIyODYzNDgwMw==&mid=100000019&sn=90a96745964946090a89c6b9d3992bc5&vid=wxv_1366965046735224833&idx=1&vidsn=81f90d58847b3331d7b4385af7edaae4&fromid=1&scene=18&xtrack=1#wechat_redirect","banner":[{"image_url":"https://res.appgan.com/admin/202006/202006022212140225401.png","url":"https://mp.weixin.qq.com/s/8-MbaArBuPIrXYNUnmoCHQ"}],"data":[]}}
     **/
    public function getHome() {
        $ret                        =   array();
        // 我的余额
        $ret['my_balance']          =   $this->member_info['amount'];
        // 昨天收入
        $ret['yesterday_income']    =   $this->exeYesterdayIncome();
        $ret["novice_guide"]        =   $this->exeHtml(Db::name("config")->where("name","novice_guide")->value("data"));
        $ret['ad_list']             =   $this->getAdData();
        $ret['banner']              =   $this->exeBanner();
        
        return_json("1","SUCCESS",$ret);
    }
    
    /*
     * @api {post} Home/getProgramData  | 小程序列表接口  
     * @apiVersion 1.0.0
     * @apiDescription  小程序列表带分页
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiParam (输入参数：) {int}          [count]            | 每页显示数量
     * @apiParam (输入参数：) {int}          [page]             | 页数
     * @apiParam (输入参数：) {int}          [platform_type]    | 平台类型  0 字节   1微信   2 QQ  3 H5   4 app 默认0
     * @apiParam (输入参数：) {int}          [title]            | 模糊查询小程序
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {array}          results.data | 小程序数组
     * @apiReturn (返回的数据) {int}            results.data.id | 小程序系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_id | 小程序id
     * @apiReturn (返回的数据) {string}            results.data.title       | 小程序名称
     * @apiReturn (返回的数据) {string}            results.data.image_url       | 小程序logo
     * @apiReturn (返回的数据) {string}            results.data.description       | 小程序描述
     * @apiReturn (返回的数据) {string}            results.data.system_type       | 小程序系统类型android|0,ios|1  双端逗号并接
     * @apiReturn (返回的数据) {string}            results.data.client_type       | 小程序抖音|0,头条|1,皮皮虾|2  只有字节平台有这个值
     * @apiReturn (返回的数据) {string}            results.data.total_prop       | 小程序总分成
     * @apiReturn (返回的数据) {string}            results.data.role_id       | 小程序可推权限 0/1 普通用户  2 vip用户  3 合伙人 向下兼容
     * @apiReturn (返回的数据) {string}            results.data.status       | 小程序状态 待发布|201,正常|200,不可用|0,广告佣金不足下架|101,封禁|100
     * @apiReturn (返回的数据) {string}            results.data.yesterday_top       | 小程序昨日最高收益
     * @apiReturn (返回的数据) {string}            results.data.program_modular_total       | 小程序模块数量
     * @apiReturn (返回的数据) {string}            results.data.program_modular       | 小程序模块数组
     * @apiReturn (返回的数据) {string}            results.data.program_modular.pm_id       | 小程序模块系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_modular.img       | 小程序模块图片
     * @apiReturn (返回的数据) {string}            results.data.program_modular.title       | 小程序模块标题
     * @apiReturn (返回的数据) {string}            results.data.program_modular.subdescribe       | 小程序模块描述
     * @apiReturn (返回的数据) {object}            results.info       | 分数数据对象
     * @apiReturn (返回的数据) {object}            results.info.page       | 当前页数
     * @apiReturn (返回的数据) {object}            results.info.total_pages       | 总页数
     * @apiReturn (返回的数据) {object}            results.info.total_count       | 总条数
     * @apiReturn (返回的数据) {object}            results.info.total_results       | 当前显示数
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"SUCCESS","results":{"data":[{"id":38,"program_id":"aaj7yt","title":"同年同月同日生查询","image_url":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","description":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！","system_type":"0,1","client_type":"0,1","total_prop":"0.95","role_id":1,"status":200,"yesterday_top":"4896","program_modular_total":1,"program_modular":[{"pm_id":190,"img":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","title":"同年同月同日生查询","subdescribe":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！"}]}]},"info":{"page":1,"total_pages":8,"total_count":15,"total_results":2}
}
     **/
    public function getProgramData() {
        list($start,$end)           =   get_limit(1);
        $platform_type              =   get_post("platform_type/d",0);
        $title                      =   get_post("title","");
        $field                      =   array(
            "id","program_id","title","image_url","description",  
            "system_type","client_type","total_prop","role_id",
            "status"
        );
        $where                      =   array();
        $where[]                    =   ["platform_type","=",$platform_type];
        if ($title) {
            $where[]                =   ["title","like","%".$title."%"];
        }
        // $where[]                    =   ["status","=",200];
        $where[]                    =   ["pid","=",0];
        $total                      =   Db::name("program")->where($where)->count();
        $data                       =   Db::name("program")->where($where)->field($field)->limit($start,$end)->order("sort desc")->select();
        foreach ($data as $key=>$value) {
            // 昨日最高收益 缓存到凌晨
            $value["yesterday_top"]     =   cache("yesterday_top_".$value["program_id"]);
            if (!$value["yesterday_top"] or $value["yesterday_top"]=="") {
                $value["yesterday_top"] =   (string)rand(999,5999);
                $cacheTimt              =   (24 * 3600)- (time() - strtotime('today'));
                cache("yesterday_top_".$value["program_id"],$value["yesterday_top"],$cacheTimt);
            }
            $value['program_modular_total'] =   $this->exeProgramData($value,1);
            $value['program_modular']       =   $this->exeProgramData($value);
            
            $data[$key]                     =   $value;
        }
        
        $info                       =   get_page($total,$data);
        
        
        return_json("1","SUCCESS",array(),$data,$info);
        
        
    }

    /*
     * @api {post} Home/getHome  | 获取首页数据接口  
     * @apiVersion 1.0.0
     * @apiDescription  首页顶部固定参数配置
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {float}          results.my_balance          | 我的余额
     * @apiReturn (返回的数据) {float}          results.yesterday_income    | 昨天收入
     * @apiReturn (返回的数据) {float}          results.novice_guide        | 新手引导地址
     * @apiReturn (返回的数据) {array}          results.ad_list             | 消息列表数组
     * @apiReturn (返回的数据) {int}            results.ad_list.ad_id       | 消息主键
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_url      | 跳转地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_img      | 图片地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_title    | 消息内容
     * @apiReturn (返回的数据) {array}          results.banner              | 轮播图数组
     * @apiReturn (返回的数据) {string}         results.banner.image_url    | 图片地址
     * @apiReturn (返回的数据) {string}         results.banner.url          | 跳转地址
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"获取成功","results":{"my_balance":"0.00","yesterday_income":0,"ad_list":[{"ad_id":1,"ad_url":"测试","ad_img":"测试","ad_title":"测试"}],"novice_guide":"http://mp.weixin.qq.com/mp/video?__biz=MzIyODYzNDgwMw==&mid=100000019&sn=90a96745964946090a89c6b9d3992bc5&vid=wxv_1366965046735224833&idx=1&vidsn=81f90d58847b3331d7b4385af7edaae4&fromid=1&scene=18&xtrack=1#wechat_redirect","banner":[{"image_url":"https://res.appgan.com/admin/202006/202006022212140225401.png","url":"https://mp.weixin.qq.com/s/8-MbaArBuPIrXYNUnmoCHQ"}],"data":[]}}
     **/
    public function getHome() {
        $ret                        =   array();
        // 我的余额
        $ret['my_balance']          =   $this->member_info['amount'];
        // 昨天收入
        $ret['yesterday_income']    =   $this->exeYesterdayIncome();
        $ret["novice_guide"]        =   $this->exeHtml(Db::name("config")->where("name","novice_guide")->value("data"));
        $ret['ad_list']             =   $this->getAdData();
        $ret['banner']              =   $this->exeBanner();
        
        return_json("1","SUCCESS",$ret);
    }
    
    /*
     * @api {post} Home/getProgramData  | 小程序列表接口  
     * @apiVersion 1.0.0
     * @apiDescription  小程序列表带分页
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiParam (输入参数：) {int}          [count]            | 每页显示数量
     * @apiParam (输入参数：) {int}          [page]             | 页数
     * @apiParam (输入参数：) {int}          [platform_type]    | 平台类型  0 字节   1微信   2 QQ  3 H5   4 app 默认0
     * @apiParam (输入参数：) {int}          [title]            | 模糊查询小程序
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {array}          results.data | 小程序数组
     * @apiReturn (返回的数据) {int}            results.data.id | 小程序系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_id | 小程序id
     * @apiReturn (返回的数据) {string}            results.data.title       | 小程序名称
     * @apiReturn (返回的数据) {string}            results.data.image_url       | 小程序logo
     * @apiReturn (返回的数据) {string}            results.data.description       | 小程序描述
     * @apiReturn (返回的数据) {string}            results.data.system_type       | 小程序系统类型android|0,ios|1  双端逗号并接
     * @apiReturn (返回的数据) {string}            results.data.client_type       | 小程序抖音|0,头条|1,皮皮虾|2  只有字节平台有这个值
     * @apiReturn (返回的数据) {string}            results.data.total_prop       | 小程序总分成
     * @apiReturn (返回的数据) {string}            results.data.role_id       | 小程序可推权限 0/1 普通用户  2 vip用户  3 合伙人 向下兼容
     * @apiReturn (返回的数据) {string}            results.data.status       | 小程序状态 待发布|201,正常|200,不可用|0,广告佣金不足下架|101,封禁|100
     * @apiReturn (返回的数据) {string}            results.data.yesterday_top       | 小程序昨日最高收益
     * @apiReturn (返回的数据) {string}            results.data.program_modular_total       | 小程序模块数量
     * @apiReturn (返回的数据) {string}            results.data.program_modular       | 小程序模块数组
     * @apiReturn (返回的数据) {string}            results.data.program_modular.pm_id       | 小程序模块系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_modular.img       | 小程序模块图片
     * @apiReturn (返回的数据) {string}            results.data.program_modular.title       | 小程序模块标题
     * @apiReturn (返回的数据) {string}            results.data.program_modular.subdescribe       | 小程序模块描述
     * @apiReturn (返回的数据) {object}            results.info       | 分数数据对象
     * @apiReturn (返回的数据) {object}            results.info.page       | 当前页数
     * @apiReturn (返回的数据) {object}            results.info.total_pages       | 总页数
     * @apiReturn (返回的数据) {object}            results.info.total_count       | 总条数
     * @apiReturn (返回的数据) {object}            results.info.total_results       | 当前显示数
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"SUCCESS","results":{"data":[{"id":38,"program_id":"aaj7yt","title":"同年同月同日生查询","image_url":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","description":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！","system_type":"0,1","client_type":"0,1","total_prop":"0.95","role_id":1,"status":200,"yesterday_top":"4896","program_modular_total":1,"program_modular":[{"pm_id":190,"img":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","title":"同年同月同日生查询","subdescribe":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！"}]}]},"info":{"page":1,"total_pages":8,"total_count":15,"total_results":2}
}
     **/
    public function getProgramData() {
        list($start,$end)           =   get_limit(1);
        $platform_type              =   get_post("platform_type/d",0);
        $title                      =   get_post("title","");
        $field                      =   array(
            "id","program_id","title","image_url","description",  
            "system_type","client_type","total_prop","role_id",
            "status"
        );
        $where                      =   array();
        $where[]                    =   ["platform_type","=",$platform_type];
        if ($title) {
            $where[]                =   ["title","like","%".$title."%"];
        }
        // $where[]                    =   ["status","=",200];
        $where[]                    =   ["pid","=",0];
        $total                      =   Db::name("program")->where($where)->count();
        $data                       =   Db::name("program")->where($where)->field($field)->limit($start,$end)->order("sort desc")->select();
        foreach ($data as $key=>$value) {
            // 昨日最高收益 缓存到凌晨
            $value["yesterday_top"]     =   cache("yesterday_top_".$value["program_id"]);
            if (!$value["yesterday_top"] or $value["yesterday_top"]=="") {
                $value["yesterday_top"] =   (string)rand(999,5999);
                $cacheTimt              =   (24 * 3600)- (time() - strtotime('today'));
                cache("yesterday_top_".$value["program_id"],$value["yesterday_top"],$cacheTimt);
            }
            $value['program_modular_total'] =   $this->exeProgramData($value,1);
            $value['program_modular']       =   $this->exeProgramData($value);
            
            $data[$key]                     =   $value;
        }
        
        $info                       =   get_page($total,$data);
        
        
        return_json("1","SUCCESS",array(),$data,$info);
        
        
    }

    /*
     * @api {post} Home/getHome  | 获取首页数据接口  
     * @apiVersion 1.0.0
     * @apiDescription  首页顶部固定参数配置
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {float}          results.my_balance          | 我的余额
     * @apiReturn (返回的数据) {float}          results.yesterday_income    | 昨天收入
     * @apiReturn (返回的数据) {float}          results.novice_guide        | 新手引导地址
     * @apiReturn (返回的数据) {array}          results.ad_list             | 消息列表数组
     * @apiReturn (返回的数据) {int}            results.ad_list.ad_id       | 消息主键
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_url      | 跳转地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_img      | 图片地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_title    | 消息内容
     * @apiReturn (返回的数据) {array}          results.banner              | 轮播图数组
     * @apiReturn (返回的数据) {string}         results.banner.image_url    | 图片地址
     * @apiReturn (返回的数据) {string}         results.banner.url          | 跳转地址
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"获取成功","results":{"my_balance":"0.00","yesterday_income":0,"ad_list":[{"ad_id":1,"ad_url":"测试","ad_img":"测试","ad_title":"测试"}],"novice_guide":"http://mp.weixin.qq.com/mp/video?__biz=MzIyODYzNDgwMw==&mid=100000019&sn=90a96745964946090a89c6b9d3992bc5&vid=wxv_1366965046735224833&idx=1&vidsn=81f90d58847b3331d7b4385af7edaae4&fromid=1&scene=18&xtrack=1#wechat_redirect","banner":[{"image_url":"https://res.appgan.com/admin/202006/202006022212140225401.png","url":"https://mp.weixin.qq.com/s/8-MbaArBuPIrXYNUnmoCHQ"}],"data":[]}}
     **/
    public function getHome() {
        $ret                        =   array();
        // 我的余额
        $ret['my_balance']          =   $this->member_info['amount'];
        // 昨天收入
        $ret['yesterday_income']    =   $this->exeYesterdayIncome();
        $ret["novice_guide"]        =   $this->exeHtml(Db::name("config")->where("name","novice_guide")->value("data"));
        $ret['ad_list']             =   $this->getAdData();
        $ret['banner']              =   $this->exeBanner();
        
        return_json("1","SUCCESS",$ret);
    }
    
    /*
     * @api {post} Home/getProgramData  | 小程序列表接口  
     * @apiVersion 1.0.0
     * @apiDescription  小程序列表带分页
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiParam (输入参数：) {int}          [count]            | 每页显示数量
     * @apiParam (输入参数：) {int}          [page]             | 页数
     * @apiParam (输入参数：) {int}          [platform_type]    | 平台类型  0 字节   1微信   2 QQ  3 H5   4 app 默认0
     * @apiParam (输入参数：) {int}          [title]            | 模糊查询小程序
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {array}          results.data | 小程序数组
     * @apiReturn (返回的数据) {int}            results.data.id | 小程序系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_id | 小程序id
     * @apiReturn (返回的数据) {string}            results.data.title       | 小程序名称
     * @apiReturn (返回的数据) {string}            results.data.image_url       | 小程序logo
     * @apiReturn (返回的数据) {string}            results.data.description       | 小程序描述
     * @apiReturn (返回的数据) {string}            results.data.system_type       | 小程序系统类型android|0,ios|1  双端逗号并接
     * @apiReturn (返回的数据) {string}            results.data.client_type       | 小程序抖音|0,头条|1,皮皮虾|2  只有字节平台有这个值
     * @apiReturn (返回的数据) {string}            results.data.total_prop       | 小程序总分成
     * @apiReturn (返回的数据) {string}            results.data.role_id       | 小程序可推权限 0/1 普通用户  2 vip用户  3 合伙人 向下兼容
     * @apiReturn (返回的数据) {string}            results.data.status       | 小程序状态 待发布|201,正常|200,不可用|0,广告佣金不足下架|101,封禁|100
     * @apiReturn (返回的数据) {string}            results.data.yesterday_top       | 小程序昨日最高收益
     * @apiReturn (返回的数据) {string}            results.data.program_modular_total       | 小程序模块数量
     * @apiReturn (返回的数据) {string}            results.data.program_modular       | 小程序模块数组
     * @apiReturn (返回的数据) {string}            results.data.program_modular.pm_id       | 小程序模块系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_modular.img       | 小程序模块图片
     * @apiReturn (返回的数据) {string}            results.data.program_modular.title       | 小程序模块标题
     * @apiReturn (返回的数据) {string}            results.data.program_modular.subdescribe       | 小程序模块描述
     * @apiReturn (返回的数据) {object}            results.info       | 分数数据对象
     * @apiReturn (返回的数据) {object}            results.info.page       | 当前页数
     * @apiReturn (返回的数据) {object}            results.info.total_pages       | 总页数
     * @apiReturn (返回的数据) {object}            results.info.total_count       | 总条数
     * @apiReturn (返回的数据) {object}            results.info.total_results       | 当前显示数
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"SUCCESS","results":{"data":[{"id":38,"program_id":"aaj7yt","title":"同年同月同日生查询","image_url":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","description":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！","system_type":"0,1","client_type":"0,1","total_prop":"0.95","role_id":1,"status":200,"yesterday_top":"4896","program_modular_total":1,"program_modular":[{"pm_id":190,"img":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","title":"同年同月同日生查询","subdescribe":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！"}]}]},"info":{"page":1,"total_pages":8,"total_count":15,"total_results":2}
}
     **/
    public function getProgramData() {
        list($start,$end)           =   get_limit(1);
        $platform_type              =   get_post("platform_type/d",0);
        $title                      =   get_post("title","");
        $field                      =   array(
            "id","program_id","title","image_url","description",  
            "system_type","client_type","total_prop","role_id",
            "status"
        );
        $where                      =   array();
        $where[]                    =   ["platform_type","=",$platform_type];
        if ($title) {
            $where[]                =   ["title","like","%".$title."%"];
        }
        // $where[]                    =   ["status","=",200];
        $where[]                    =   ["pid","=",0];
        $total                      =   Db::name("program")->where($where)->count();
        $data                       =   Db::name("program")->where($where)->field($field)->limit($start,$end)->order("sort desc")->select();
        foreach ($data as $key=>$value) {
            // 昨日最高收益 缓存到凌晨
            $value["yesterday_top"]     =   cache("yesterday_top_".$value["program_id"]);
            if (!$value["yesterday_top"] or $value["yesterday_top"]=="") {
                $value["yesterday_top"] =   (string)rand(999,5999);
                $cacheTimt              =   (24 * 3600)- (time() - strtotime('today'));
                cache("yesterday_top_".$value["program_id"],$value["yesterday_top"],$cacheTimt);
            }
            $value['program_modular_total'] =   $this->exeProgramData($value,1);
            $value['program_modular']       =   $this->exeProgramData($value);
            
            $data[$key]                     =   $value;
        }
        
        $info                       =   get_page($total,$data);
        
        
        return_json("1","SUCCESS",array(),$data,$info);
        
        
    }

    /*
     * @api {post} Home/getHome  | 获取首页数据接口  
     * @apiVersion 1.0.0
     * @apiDescription  首页顶部固定参数配置
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {float}          results.my_balance          | 我的余额
     * @apiReturn (返回的数据) {float}          results.yesterday_income    | 昨天收入
     * @apiReturn (返回的数据) {float}          results.novice_guide        | 新手引导地址
     * @apiReturn (返回的数据) {array}          results.ad_list             | 消息列表数组
     * @apiReturn (返回的数据) {int}            results.ad_list.ad_id       | 消息主键
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_url      | 跳转地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_img      | 图片地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_title    | 消息内容
     * @apiReturn (返回的数据) {array}          results.banner              | 轮播图数组
     * @apiReturn (返回的数据) {string}         results.banner.image_url    | 图片地址
     * @apiReturn (返回的数据) {string}         results.banner.url          | 跳转地址
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"获取成功","results":{"my_balance":"0.00","yesterday_income":0,"ad_list":[{"ad_id":1,"ad_url":"测试","ad_img":"测试","ad_title":"测试"}],"novice_guide":"http://mp.weixin.qq.com/mp/video?__biz=MzIyODYzNDgwMw==&mid=100000019&sn=90a96745964946090a89c6b9d3992bc5&vid=wxv_1366965046735224833&idx=1&vidsn=81f90d58847b3331d7b4385af7edaae4&fromid=1&scene=18&xtrack=1#wechat_redirect","banner":[{"image_url":"https://res.appgan.com/admin/202006/202006022212140225401.png","url":"https://mp.weixin.qq.com/s/8-MbaArBuPIrXYNUnmoCHQ"}],"data":[]}}
     **/
    public function getHome() {
        $ret                        =   array();
        // 我的余额
        $ret['my_balance']          =   $this->member_info['amount'];
        // 昨天收入
        $ret['yesterday_income']    =   $this->exeYesterdayIncome();
        $ret["novice_guide"]        =   $this->exeHtml(Db::name("config")->where("name","novice_guide")->value("data"));
        $ret['ad_list']             =   $this->getAdData();
        $ret['banner']              =   $this->exeBanner();
        
        return_json("1","SUCCESS",$ret);
    }
    
    /*
     * @api {post} Home/getProgramData  | 小程序列表接口  
     * @apiVersion 1.0.0
     * @apiDescription  小程序列表带分页
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiParam (输入参数：) {int}          [count]            | 每页显示数量
     * @apiParam (输入参数：) {int}          [page]             | 页数
     * @apiParam (输入参数：) {int}          [platform_type]    | 平台类型  0 字节   1微信   2 QQ  3 H5   4 app 默认0
     * @apiParam (输入参数：) {int}          [title]            | 模糊查询小程序
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {array}          results.data | 小程序数组
     * @apiReturn (返回的数据) {int}            results.data.id | 小程序系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_id | 小程序id
     * @apiReturn (返回的数据) {string}            results.data.title       | 小程序名称
     * @apiReturn (返回的数据) {string}            results.data.image_url       | 小程序logo
     * @apiReturn (返回的数据) {string}            results.data.description       | 小程序描述
     * @apiReturn (返回的数据) {string}            results.data.system_type       | 小程序系统类型android|0,ios|1  双端逗号并接
     * @apiReturn (返回的数据) {string}            results.data.client_type       | 小程序抖音|0,头条|1,皮皮虾|2  只有字节平台有这个值
     * @apiReturn (返回的数据) {string}            results.data.total_prop       | 小程序总分成
     * @apiReturn (返回的数据) {string}            results.data.role_id       | 小程序可推权限 0/1 普通用户  2 vip用户  3 合伙人 向下兼容
     * @apiReturn (返回的数据) {string}            results.data.status       | 小程序状态 待发布|201,正常|200,不可用|0,广告佣金不足下架|101,封禁|100
     * @apiReturn (返回的数据) {string}            results.data.yesterday_top       | 小程序昨日最高收益
     * @apiReturn (返回的数据) {string}            results.data.program_modular_total       | 小程序模块数量
     * @apiReturn (返回的数据) {string}            results.data.program_modular       | 小程序模块数组
     * @apiReturn (返回的数据) {string}            results.data.program_modular.pm_id       | 小程序模块系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_modular.img       | 小程序模块图片
     * @apiReturn (返回的数据) {string}            results.data.program_modular.title       | 小程序模块标题
     * @apiReturn (返回的数据) {string}            results.data.program_modular.subdescribe       | 小程序模块描述
     * @apiReturn (返回的数据) {object}            results.info       | 分数数据对象
     * @apiReturn (返回的数据) {object}            results.info.page       | 当前页数
     * @apiReturn (返回的数据) {object}            results.info.total_pages       | 总页数
     * @apiReturn (返回的数据) {object}            results.info.total_count       | 总条数
     * @apiReturn (返回的数据) {object}            results.info.total_results       | 当前显示数
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"SUCCESS","results":{"data":[{"id":38,"program_id":"aaj7yt","title":"同年同月同日生查询","image_url":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","description":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！","system_type":"0,1","client_type":"0,1","total_prop":"0.95","role_id":1,"status":200,"yesterday_top":"4896","program_modular_total":1,"program_modular":[{"pm_id":190,"img":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","title":"同年同月同日生查询","subdescribe":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！"}]}]},"info":{"page":1,"total_pages":8,"total_count":15,"total_results":2}
}
     **/
    public function getProgramData() {
        list($start,$end)           =   get_limit(1);
        $platform_type              =   get_post("platform_type/d",0);
        $title                      =   get_post("title","");
        $field                      =   array(
            "id","program_id","title","image_url","description",  
            "system_type","client_type","total_prop","role_id",
            "status"
        );
        $where                      =   array();
        $where[]                    =   ["platform_type","=",$platform_type];
        if ($title) {
            $where[]                =   ["title","like","%".$title."%"];
        }
        // $where[]                    =   ["status","=",200];
        $where[]                    =   ["pid","=",0];
        $total                      =   Db::name("program")->where($where)->count();
        $data                       =   Db::name("program")->where($where)->field($field)->limit($start,$end)->order("sort desc")->select();
        foreach ($data as $key=>$value) {
            // 昨日最高收益 缓存到凌晨
            $value["yesterday_top"]     =   cache("yesterday_top_".$value["program_id"]);
            if (!$value["yesterday_top"] or $value["yesterday_top"]=="") {
                $value["yesterday_top"] =   (string)rand(999,5999);
                $cacheTimt              =   (24 * 3600)- (time() - strtotime('today'));
                cache("yesterday_top_".$value["program_id"],$value["yesterday_top"],$cacheTimt);
            }
            $value['program_modular_total'] =   $this->exeProgramData($value,1);
            $value['program_modular']       =   $this->exeProgramData($value);
            
            $data[$key]                     =   $value;
        }
        
        $info                       =   get_page($total,$data);
        
        
        return_json("1","SUCCESS",array(),$data,$info);
        
        
    }

    /*
     * @api {post} Home/getHome  | 获取首页数据接口  
     * @apiVersion 1.0.0
     * @apiDescription  首页顶部固定参数配置
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {float}          results.my_balance          | 我的余额
     * @apiReturn (返回的数据) {float}          results.yesterday_income    | 昨天收入
     * @apiReturn (返回的数据) {float}          results.novice_guide        | 新手引导地址
     * @apiReturn (返回的数据) {array}          results.ad_list             | 消息列表数组
     * @apiReturn (返回的数据) {int}            results.ad_list.ad_id       | 消息主键
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_url      | 跳转地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_img      | 图片地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_title    | 消息内容
     * @apiReturn (返回的数据) {array}          results.banner              | 轮播图数组
     * @apiReturn (返回的数据) {string}         results.banner.image_url    | 图片地址
     * @apiReturn (返回的数据) {string}         results.banner.url          | 跳转地址
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"获取成功","results":{"my_balance":"0.00","yesterday_income":0,"ad_list":[{"ad_id":1,"ad_url":"测试","ad_img":"测试","ad_title":"测试"}],"novice_guide":"http://mp.weixin.qq.com/mp/video?__biz=MzIyODYzNDgwMw==&mid=100000019&sn=90a96745964946090a89c6b9d3992bc5&vid=wxv_1366965046735224833&idx=1&vidsn=81f90d58847b3331d7b4385af7edaae4&fromid=1&scene=18&xtrack=1#wechat_redirect","banner":[{"image_url":"https://res.appgan.com/admin/202006/202006022212140225401.png","url":"https://mp.weixin.qq.com/s/8-MbaArBuPIrXYNUnmoCHQ"}],"data":[]}}
     **/
    public function getHome() {
        $ret                        =   array();
        // 我的余额
        $ret['my_balance']          =   $this->member_info['amount'];
        // 昨天收入
        $ret['yesterday_income']    =   $this->exeYesterdayIncome();
        $ret["novice_guide"]        =   $this->exeHtml(Db::name("config")->where("name","novice_guide")->value("data"));
        $ret['ad_list']             =   $this->getAdData();
        $ret['banner']              =   $this->exeBanner();
        
        return_json("1","SUCCESS",$ret);
    }
    
    /*
     * @api {post} Home/getProgramData  | 小程序列表接口  
     * @apiVersion 1.0.0
     * @apiDescription  小程序列表带分页
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiParam (输入参数：) {int}          [count]            | 每页显示数量
     * @apiParam (输入参数：) {int}          [page]             | 页数
     * @apiParam (输入参数：) {int}          [platform_type]    | 平台类型  0 字节   1微信   2 QQ  3 H5   4 app 默认0
     * @apiParam (输入参数：) {int}          [title]            | 模糊查询小程序
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {array}          results.data | 小程序数组
     * @apiReturn (返回的数据) {int}            results.data.id | 小程序系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_id | 小程序id
     * @apiReturn (返回的数据) {string}            results.data.title       | 小程序名称
     * @apiReturn (返回的数据) {string}            results.data.image_url       | 小程序logo
     * @apiReturn (返回的数据) {string}            results.data.description       | 小程序描述
     * @apiReturn (返回的数据) {string}            results.data.system_type       | 小程序系统类型android|0,ios|1  双端逗号并接
     * @apiReturn (返回的数据) {string}            results.data.client_type       | 小程序抖音|0,头条|1,皮皮虾|2  只有字节平台有这个值
     * @apiReturn (返回的数据) {string}            results.data.total_prop       | 小程序总分成
     * @apiReturn (返回的数据) {string}            results.data.role_id       | 小程序可推权限 0/1 普通用户  2 vip用户  3 合伙人 向下兼容
     * @apiReturn (返回的数据) {string}            results.data.status       | 小程序状态 待发布|201,正常|200,不可用|0,广告佣金不足下架|101,封禁|100
     * @apiReturn (返回的数据) {string}            results.data.yesterday_top       | 小程序昨日最高收益
     * @apiReturn (返回的数据) {string}            results.data.program_modular_total       | 小程序模块数量
     * @apiReturn (返回的数据) {string}            results.data.program_modular       | 小程序模块数组
     * @apiReturn (返回的数据) {string}            results.data.program_modular.pm_id       | 小程序模块系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_modular.img       | 小程序模块图片
     * @apiReturn (返回的数据) {string}            results.data.program_modular.title       | 小程序模块标题
     * @apiReturn (返回的数据) {string}            results.data.program_modular.subdescribe       | 小程序模块描述
     * @apiReturn (返回的数据) {object}            results.info       | 分数数据对象
     * @apiReturn (返回的数据) {object}            results.info.page       | 当前页数
     * @apiReturn (返回的数据) {object}            results.info.total_pages       | 总页数
     * @apiReturn (返回的数据) {object}            results.info.total_count       | 总条数
     * @apiReturn (返回的数据) {object}            results.info.total_results       | 当前显示数
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"SUCCESS","results":{"data":[{"id":38,"program_id":"aaj7yt","title":"同年同月同日生查询","image_url":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","description":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！","system_type":"0,1","client_type":"0,1","total_prop":"0.95","role_id":1,"status":200,"yesterday_top":"4896","program_modular_total":1,"program_modular":[{"pm_id":190,"img":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","title":"同年同月同日生查询","subdescribe":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！"}]}]},"info":{"page":1,"total_pages":8,"total_count":15,"total_results":2}
}
     **/
    public function getProgramData() {
        list($start,$end)           =   get_limit(1);
        $platform_type              =   get_post("platform_type/d",0);
        $title                      =   get_post("title","");
        $field                      =   array(
            "id","program_id","title","image_url","description",  
            "system_type","client_type","total_prop","role_id",
            "status"
        );
        $where                      =   array();
        $where[]                    =   ["platform_type","=",$platform_type];
        if ($title) {
            $where[]                =   ["title","like","%".$title."%"];
        }
        // $where[]                    =   ["status","=",200];
        $where[]                    =   ["pid","=",0];
        $total                      =   Db::name("program")->where($where)->count();
        $data                       =   Db::name("program")->where($where)->field($field)->limit($start,$end)->order("sort desc")->select();
        foreach ($data as $key=>$value) {
            // 昨日最高收益 缓存到凌晨
            $value["yesterday_top"]     =   cache("yesterday_top_".$value["program_id"]);
            if (!$value["yesterday_top"] or $value["yesterday_top"]=="") {
                $value["yesterday_top"] =   (string)rand(999,5999);
                $cacheTimt              =   (24 * 3600)- (time() - strtotime('today'));
                cache("yesterday_top_".$value["program_id"],$value["yesterday_top"],$cacheTimt);
            }
            $value['program_modular_total'] =   $this->exeProgramData($value,1);
            $value['program_modular']       =   $this->exeProgramData($value);
            
            $data[$key]                     =   $value;
        }
        
        $info                       =   get_page($total,$data);
        
        
        return_json("1","SUCCESS",array(),$data,$info);
        
        
    }
    

    /*
     * @api {post} Home/getHome  | 获取首页数据接口  
     * @apiVersion 1.0.0
     * @apiDescription  首页顶部固定参数配置
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {float}          results.my_balance          | 我的余额
     * @apiReturn (返回的数据) {float}          results.yesterday_income    | 昨天收入
     * @apiReturn (返回的数据) {float}          results.novice_guide        | 新手引导地址
     * @apiReturn (返回的数据) {array}          results.ad_list             | 消息列表数组
     * @apiReturn (返回的数据) {int}            results.ad_list.ad_id       | 消息主键
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_url      | 跳转地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_img      | 图片地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_title    | 消息内容
     * @apiReturn (返回的数据) {array}          results.banner              | 轮播图数组
     * @apiReturn (返回的数据) {string}         results.banner.image_url    | 图片地址
     * @apiReturn (返回的数据) {string}         results.banner.url          | 跳转地址
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"获取成功","results":{"my_balance":"0.00","yesterday_income":0,"ad_list":[{"ad_id":1,"ad_url":"测试","ad_img":"测试","ad_title":"测试"}],"novice_guide":"http://mp.weixin.qq.com/mp/video?__biz=MzIyODYzNDgwMw==&mid=100000019&sn=90a96745964946090a89c6b9d3992bc5&vid=wxv_1366965046735224833&idx=1&vidsn=81f90d58847b3331d7b4385af7edaae4&fromid=1&scene=18&xtrack=1#wechat_redirect","banner":[{"image_url":"https://res.appgan.com/admin/202006/202006022212140225401.png","url":"https://mp.weixin.qq.com/s/8-MbaArBuPIrXYNUnmoCHQ"}],"data":[]}}
     **/
    public function getHome() {
        $ret                        =   array();
        // 我的余额
        $ret['my_balance']          =   $this->member_info['amount'];
        // 昨天收入
        $ret['yesterday_income']    =   $this->exeYesterdayIncome();
        $ret["novice_guide"]        =   $this->exeHtml(Db::name("config")->where("name","novice_guide")->value("data"));
        $ret['ad_list']             =   $this->getAdData();
		$ret['banner']              =   $this->exeBanner();
        
        return_json("1","SUCCESS",$ret);
    }
     /*
     * @api {post} Home/getProgramData  | 小程序列表接口  
     * @apiVersion 1.0.0
     * @apiDescription  小程序列表带分页
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiParam (输入参数：) {int}          [count]            | 每页显示数量
     * @apiParam (输入参数：) {int}          [page]             | 页数
     * @apiParam (输入参数：) {int}          [platform_type]    | 平台类型  0 字节   1微信   2 QQ  3 H5   4 app 默认0
     * @apiParam (输入参数：) {int}          [title]            | 模糊查询小程序
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {array}          results.data | 小程序数组
     * @apiReturn (返回的数据) {int}            results.data.id | 小程序系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_id | 小程序id
     * @apiReturn (返回的数据) {string}            results.data.title       | 小程序名称
     * @apiReturn (返回的数据) {string}            results.data.image_url       | 小程序logo
     * @apiReturn (返回的数据) {string}            results.data.description       | 小程序描述
     * @apiReturn (返回的数据) {string}            results.data.system_type       | 小程序系统类型android|0,ios|1  双端逗号并接
     * @apiReturn (返回的数据) {string}            results.data.client_type       | 小程序抖音|0,头条|1,皮皮虾|2  只有字节平台有这个值
     * @apiReturn (返回的数据) {string}            results.data.total_prop       | 小程序总分成
     * @apiReturn (返回的数据) {string}            results.data.role_id       | 小程序可推权限 0/1 普通用户  2 vip用户  3 合伙人 向下兼容
     * @apiReturn (返回的数据) {string}            results.data.status       | 小程序状态 待发布|201,正常|200,不可用|0,广告佣金不足下架|101,封禁|100
     * @apiReturn (返回的数据) {string}            results.data.yesterday_top       | 小程序昨日最高收益
     * @apiReturn (返回的数据) {string}            results.data.program_modular_total       | 小程序模块数量
     * @apiReturn (返回的数据) {string}            results.data.program_modular       | 小程序模块数组
     * @apiReturn (返回的数据) {string}            results.data.program_modular.pm_id       | 小程序模块系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_modular.img       | 小程序模块图片
     * @apiReturn (返回的数据) {string}            results.data.program_modular.title       | 小程序模块标题
     * @apiReturn (返回的数据) {string}            results.data.program_modular.subdescribe       | 小程序模块描述
     * @apiReturn (返回的数据) {object}            results.info       | 分数数据对象
     * @apiReturn (返回的数据) {object}            results.info.page       | 当前页数
     * @apiReturn (返回的数据) {object}            results.info.total_pages       | 总页数
     * @apiReturn (返回的数据) {object}            results.info.total_count       | 总条数
     * @apiReturn (返回的数据) {object}            results.info.total_results       | 当前显示数
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"SUCCESS","results":{"data":[{"id":38,"program_id":"aaj7yt","title":"同年同月同日生查询","image_url":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","description":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！","system_type":"0,1","client_type":"0,1","total_prop":"0.95","role_id":1,"status":200,"yesterday_top":"4896","program_modular_total":1,"program_modular":[{"pm_id":190,"img":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","title":"同年同月同日生查询","subdescribe":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！"}]}]},"info":{"page":1,"total_pages":8,"total_count":15,"total_results":2}
}
     **/
    public function getProgramData() {
        list($start,$end)           =   get_limit(1);
        $platform_type              =   get_post("platform_type/d",0);
        $title                      =   get_post("title","");
        $field                      =   array(
            "id","program_id","title","image_url","description",  
            "system_type","client_type","total_prop","role_id",
            "status"
        );
        $where                      =   array();
        $where[]                    =   ["platform_type","=",$platform_type];
        if ($title) {
            $where[]                =   ["title","like","%".$title."%"];
        }
        // $where[]                    =   ["status","=",200];
        $where[]                    =   ["pid","=",0];
        $total                      =   Db::name("program")->where($where)->count();
        $data                       =   Db::name("program")->where($where)->field($field)->limit($start,$end)->order("sort desc")->select();
        foreach ($data as $key=>$value) {
            // 昨日最高收益 缓存到凌晨
            $value["yesterday_top"]     =   cache("yesterday_top_".$value["program_id"]);
		    if (!$value["yesterday_top"] or $value["yesterday_top"]=="") {
		        $value["yesterday_top"] =   (string)rand(999,5999);
		        $cacheTimt              =   (24 * 3600)- (time() - strtotime('today'));
		        cache("yesterday_top_".$value["program_id"],$value["yesterday_top"],$cacheTimt);
		    }
		    $value['program_modular_total'] =   $this->exeProgramData($value,1);
            $value['program_modular']       =   $this->exeProgramData($value);
            
            $data[$key]                     =   $value;
        }
        
        $info                       =   get_page($total,$data);
        
        
        return_json("1","SUCCESS",array(),$data,$info);
        
        
    }
    

    /*
     * @api {post} Home/getHome  | 获取首页数据接口  
     * @apiVersion 1.0.0
     * @apiDescription  首页顶部固定参数配置
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {float}          results.my_balance          | 我的余额
     * @apiReturn (返回的数据) {float}          results.yesterday_income    | 昨天收入
     * @apiReturn (返回的数据) {float}          results.novice_guide        | 新手引导地址
     * @apiReturn (返回的数据) {array}          results.ad_list             | 消息列表数组
     * @apiReturn (返回的数据) {int}            results.ad_list.ad_id       | 消息主键
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_url      | 跳转地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_img      | 图片地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_title    | 消息内容
     * @apiReturn (返回的数据) {array}          results.banner              | 轮播图数组
     * @apiReturn (返回的数据) {string}         results.banner.image_url    | 图片地址
     * @apiReturn (返回的数据) {string}         results.banner.url          | 跳转地址
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"获取成功","results":{"my_balance":"0.00","yesterday_income":0,"ad_list":[{"ad_id":1,"ad_url":"测试","ad_img":"测试","ad_title":"测试"}],"novice_guide":"http://mp.weixin.qq.com/mp/video?__biz=MzIyODYzNDgwMw==&mid=100000019&sn=90a96745964946090a89c6b9d3992bc5&vid=wxv_1366965046735224833&idx=1&vidsn=81f90d58847b3331d7b4385af7edaae4&fromid=1&scene=18&xtrack=1#wechat_redirect","banner":[{"image_url":"https://res.appgan.com/admin/202006/202006022212140225401.png","url":"https://mp.weixin.qq.com/s/8-MbaArBuPIrXYNUnmoCHQ"}],"data":[]}}
     **/
    public function getHome() {
        $ret                        =   array();
        // 我的余额
        $ret['my_balance']          =   $this->member_info['amount'];
        // 昨天收入
        $ret['yesterday_income']    =   $this->exeYesterdayIncome();
        $ret["novice_guide"]        =   $this->exeHtml(Db::name("config")->where("name","novice_guide")->value("data"));
        $ret['ad_list']             =   $this->getAdData();
		$ret['banner']              =   $this->exeBanner();
        
        return_json("1","SUCCESS",$ret);
    }
     /*
     * @api {post} Home/getProgramData  | 小程序列表接口  
     * @apiVersion 1.0.0
     * @apiDescription  小程序列表带分页
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiParam (输入参数：) {int}          [count]            | 每页显示数量
     * @apiParam (输入参数：) {int}          [page]             | 页数
     * @apiParam (输入参数：) {int}          [platform_type]    | 平台类型  0 字节   1微信   2 QQ  3 H5   4 app 默认0
     * @apiParam (输入参数：) {int}          [title]            | 模糊查询小程序
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {array}          results.data | 小程序数组
     * @apiReturn (返回的数据) {int}            results.data.id | 小程序系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_id | 小程序id
     * @apiReturn (返回的数据) {string}            results.data.title       | 小程序名称
     * @apiReturn (返回的数据) {string}            results.data.image_url       | 小程序logo
     * @apiReturn (返回的数据) {string}            results.data.description       | 小程序描述
     * @apiReturn (返回的数据) {string}            results.data.system_type       | 小程序系统类型android|0,ios|1  双端逗号并接
     * @apiReturn (返回的数据) {string}            results.data.client_type       | 小程序抖音|0,头条|1,皮皮虾|2  只有字节平台有这个值
     * @apiReturn (返回的数据) {string}            results.data.total_prop       | 小程序总分成
     * @apiReturn (返回的数据) {string}            results.data.role_id       | 小程序可推权限 0/1 普通用户  2 vip用户  3 合伙人 向下兼容
     * @apiReturn (返回的数据) {string}            results.data.status       | 小程序状态 待发布|201,正常|200,不可用|0,广告佣金不足下架|101,封禁|100
     * @apiReturn (返回的数据) {string}            results.data.yesterday_top       | 小程序昨日最高收益
     * @apiReturn (返回的数据) {string}            results.data.program_modular_total       | 小程序模块数量
     * @apiReturn (返回的数据) {string}            results.data.program_modular       | 小程序模块数组
     * @apiReturn (返回的数据) {string}            results.data.program_modular.pm_id       | 小程序模块系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_modular.img       | 小程序模块图片
     * @apiReturn (返回的数据) {string}            results.data.program_modular.title       | 小程序模块标题
     * @apiReturn (返回的数据) {string}            results.data.program_modular.subdescribe       | 小程序模块描述
     * @apiReturn (返回的数据) {object}            results.info       | 分数数据对象
     * @apiReturn (返回的数据) {object}            results.info.page       | 当前页数
     * @apiReturn (返回的数据) {object}            results.info.total_pages       | 总页数
     * @apiReturn (返回的数据) {object}            results.info.total_count       | 总条数
     * @apiReturn (返回的数据) {object}            results.info.total_results       | 当前显示数
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"SUCCESS","results":{"data":[{"id":38,"program_id":"aaj7yt","title":"同年同月同日生查询","image_url":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","description":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！","system_type":"0,1","client_type":"0,1","total_prop":"0.95","role_id":1,"status":200,"yesterday_top":"4896","program_modular_total":1,"program_modular":[{"pm_id":190,"img":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","title":"同年同月同日生查询","subdescribe":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！"}]}]},"info":{"page":1,"total_pages":8,"total_count":15,"total_results":2}
}
     **/
    public function getProgramData() {
        list($start,$end)           =   get_limit(1);
        $platform_type              =   get_post("platform_type/d",0);
        $title                      =   get_post("title","");
        $field                      =   array(
            "id","program_id","title","image_url","description",  
            "system_type","client_type","total_prop","role_id",
            "status"
        );
        $where                      =   array();
        $where[]                    =   ["platform_type","=",$platform_type];
        if ($title) {
            $where[]                =   ["title","like","%".$title."%"];
        }
        // $where[]                    =   ["status","=",200];
        $where[]                    =   ["pid","=",0];
        $total                      =   Db::name("program")->where($where)->count();
        $data                       =   Db::name("program")->where($where)->field($field)->limit($start,$end)->order("sort desc")->select();
        foreach ($data as $key=>$value) {
            // 昨日最高收益 缓存到凌晨
            $value["yesterday_top"]     =   cache("yesterday_top_".$value["program_id"]);
		    if (!$value["yesterday_top"] or $value["yesterday_top"]=="") {
		        $value["yesterday_top"] =   (string)rand(999,5999);
		        $cacheTimt              =   (24 * 3600)- (time() - strtotime('today'));
		        cache("yesterday_top_".$value["program_id"],$value["yesterday_top"],$cacheTimt);
		    }
		    $value['program_modular_total'] =   $this->exeProgramData($value,1);
            $value['program_modular']       =   $this->exeProgramData($value);
            
            $data[$key]                     =   $value;
        }
        
        $info                       =   get_page($total,$data);
        
        
        return_json("1","SUCCESS",array(),$data,$info);
        
        
    }
    

    /*
     * @api {post} Home/getHome  | 获取首页数据接口  
     * @apiVersion 1.0.0
     * @apiDescription  首页顶部固定参数配置
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {float}          results.my_balance          | 我的余额
     * @apiReturn (返回的数据) {float}          results.yesterday_income    | 昨天收入
     * @apiReturn (返回的数据) {float}          results.novice_guide        | 新手引导地址
     * @apiReturn (返回的数据) {array}          results.ad_list             | 消息列表数组
     * @apiReturn (返回的数据) {int}            results.ad_list.ad_id       | 消息主键
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_url      | 跳转地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_img      | 图片地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_title    | 消息内容
     * @apiReturn (返回的数据) {array}          results.banner              | 轮播图数组
     * @apiReturn (返回的数据) {string}         results.banner.image_url    | 图片地址
     * @apiReturn (返回的数据) {string}         results.banner.url          | 跳转地址
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"获取成功","results":{"my_balance":"0.00","yesterday_income":0,"ad_list":[{"ad_id":1,"ad_url":"测试","ad_img":"测试","ad_title":"测试"}],"novice_guide":"http://mp.weixin.qq.com/mp/video?__biz=MzIyODYzNDgwMw==&mid=100000019&sn=90a96745964946090a89c6b9d3992bc5&vid=wxv_1366965046735224833&idx=1&vidsn=81f90d58847b3331d7b4385af7edaae4&fromid=1&scene=18&xtrack=1#wechat_redirect","banner":[{"image_url":"https://res.appgan.com/admin/202006/202006022212140225401.png","url":"https://mp.weixin.qq.com/s/8-MbaArBuPIrXYNUnmoCHQ"}],"data":[]}}
     **/
    public function getHome() {
        $ret                        =   array();
        // 我的余额
        $ret['my_balance']          =   $this->member_info['amount'];
        // 昨天收入
        $ret['yesterday_income']    =   $this->exeYesterdayIncome();
        $ret["novice_guide"]        =   $this->exeHtml(Db::name("config")->where("name","novice_guide")->value("data"));
        $ret['ad_list']             =   $this->getAdData();
		$ret['banner']              =   $this->exeBanner();
        
        return_json("1","SUCCESS",$ret);
    }
     /*
     * @api {post} Home/getProgramData  | 小程序列表接口  
     * @apiVersion 1.0.0
     * @apiDescription  小程序列表带分页
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiParam (输入参数：) {int}          [count]            | 每页显示数量
     * @apiParam (输入参数：) {int}          [page]             | 页数
     * @apiParam (输入参数：) {int}          [platform_type]    | 平台类型  0 字节   1微信   2 QQ  3 H5   4 app 默认0
     * @apiParam (输入参数：) {int}          [title]            | 模糊查询小程序
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {array}          results.data | 小程序数组
     * @apiReturn (返回的数据) {int}            results.data.id | 小程序系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_id | 小程序id
     * @apiReturn (返回的数据) {string}            results.data.title       | 小程序名称
     * @apiReturn (返回的数据) {string}            results.data.image_url       | 小程序logo
     * @apiReturn (返回的数据) {string}            results.data.description       | 小程序描述
     * @apiReturn (返回的数据) {string}            results.data.system_type       | 小程序系统类型android|0,ios|1  双端逗号并接
     * @apiReturn (返回的数据) {string}            results.data.client_type       | 小程序抖音|0,头条|1,皮皮虾|2  只有字节平台有这个值
     * @apiReturn (返回的数据) {string}            results.data.total_prop       | 小程序总分成
     * @apiReturn (返回的数据) {string}            results.data.role_id       | 小程序可推权限 0/1 普通用户  2 vip用户  3 合伙人 向下兼容
     * @apiReturn (返回的数据) {string}            results.data.status       | 小程序状态 待发布|201,正常|200,不可用|0,广告佣金不足下架|101,封禁|100
     * @apiReturn (返回的数据) {string}            results.data.yesterday_top       | 小程序昨日最高收益
     * @apiReturn (返回的数据) {string}            results.data.program_modular_total       | 小程序模块数量
     * @apiReturn (返回的数据) {string}            results.data.program_modular       | 小程序模块数组
     * @apiReturn (返回的数据) {string}            results.data.program_modular.pm_id       | 小程序模块系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_modular.img       | 小程序模块图片
     * @apiReturn (返回的数据) {string}            results.data.program_modular.title       | 小程序模块标题
     * @apiReturn (返回的数据) {string}            results.data.program_modular.subdescribe       | 小程序模块描述
     * @apiReturn (返回的数据) {object}            results.info       | 分数数据对象
     * @apiReturn (返回的数据) {object}            results.info.page       | 当前页数
     * @apiReturn (返回的数据) {object}            results.info.total_pages       | 总页数
     * @apiReturn (返回的数据) {object}            results.info.total_count       | 总条数
     * @apiReturn (返回的数据) {object}            results.info.total_results       | 当前显示数
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"SUCCESS","results":{"data":[{"id":38,"program_id":"aaj7yt","title":"同年同月同日生查询","image_url":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","description":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！","system_type":"0,1","client_type":"0,1","total_prop":"0.95","role_id":1,"status":200,"yesterday_top":"4896","program_modular_total":1,"program_modular":[{"pm_id":190,"img":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","title":"同年同月同日生查询","subdescribe":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！"}]}]},"info":{"page":1,"total_pages":8,"total_count":15,"total_results":2}
}
     **/
    public function getProgramData() {
        list($start,$end)           =   get_limit(1);
        $platform_type              =   get_post("platform_type/d",0);
        $title                      =   get_post("title","");
        $field                      =   array(
            "id","program_id","title","image_url","description",  
            "system_type","client_type","total_prop","role_id",
            "status"
        );
        $where                      =   array();
        $where[]                    =   ["platform_type","=",$platform_type];
        if ($title) {
            $where[]                =   ["title","like","%".$title."%"];
        }
        // $where[]                    =   ["status","=",200];
        $where[]                    =   ["pid","=",0];
        $total                      =   Db::name("program")->where($where)->count();
        $data                       =   Db::name("program")->where($where)->field($field)->limit($start,$end)->order("sort desc")->select();
        foreach ($data as $key=>$value) {
            // 昨日最高收益 缓存到凌晨
            $value["yesterday_top"]     =   cache("yesterday_top_".$value["program_id"]);
		    if (!$value["yesterday_top"] or $value["yesterday_top"]=="") {
		        $value["yesterday_top"] =   (string)rand(999,5999);
		        $cacheTimt              =   (24 * 3600)- (time() - strtotime('today'));
		        cache("yesterday_top_".$value["program_id"],$value["yesterday_top"],$cacheTimt);
		    }
		    $value['program_modular_total'] =   $this->exeProgramData($value,1);
            $value['program_modular']       =   $this->exeProgramData($value);
            
            $data[$key]                     =   $value;
        }
        
        $info                       =   get_page($total,$data);
        
        
        return_json("1","SUCCESS",array(),$data,$info);
        
        
    }
    

    /*
     * @api {post} Home/getHome  | 获取首页数据接口  
     * @apiVersion 1.0.0
     * @apiDescription  首页顶部固定参数配置
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {float}          results.my_balance          | 我的余额
     * @apiReturn (返回的数据) {float}          results.yesterday_income    | 昨天收入
     * @apiReturn (返回的数据) {float}          results.novice_guide        | 新手引导地址
     * @apiReturn (返回的数据) {array}          results.ad_list             | 消息列表数组
     * @apiReturn (返回的数据) {int}            results.ad_list.ad_id       | 消息主键
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_url      | 跳转地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_img      | 图片地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_title    | 消息内容
     * @apiReturn (返回的数据) {array}          results.banner              | 轮播图数组
     * @apiReturn (返回的数据) {string}         results.banner.image_url    | 图片地址
     * @apiReturn (返回的数据) {string}         results.banner.url          | 跳转地址
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"获取成功","results":{"my_balance":"0.00","yesterday_income":0,"ad_list":[{"ad_id":1,"ad_url":"测试","ad_img":"测试","ad_title":"测试"}],"novice_guide":"http://mp.weixin.qq.com/mp/video?__biz=MzIyODYzNDgwMw==&mid=100000019&sn=90a96745964946090a89c6b9d3992bc5&vid=wxv_1366965046735224833&idx=1&vidsn=81f90d58847b3331d7b4385af7edaae4&fromid=1&scene=18&xtrack=1#wechat_redirect","banner":[{"image_url":"https://res.appgan.com/admin/202006/202006022212140225401.png","url":"https://mp.weixin.qq.com/s/8-MbaArBuPIrXYNUnmoCHQ"}],"data":[]}}
     **/
    public function getHome() {
        $ret                        =   array();
        // 我的余额
        $ret['my_balance']          =   $this->member_info['amount'];
        // 昨天收入
        $ret['yesterday_income']    =   $this->exeYesterdayIncome();
        $ret["novice_guide"]        =   $this->exeHtml(Db::name("config")->where("name","novice_guide")->value("data"));
        $ret['ad_list']             =   $this->getAdData();
		$ret['banner']              =   $this->exeBanner();
        
        return_json("1","SUCCESS",$ret);
    }
     /*
     * @api {post} Home/getProgramData  | 小程序列表接口  
     * @apiVersion 1.0.0
     * @apiDescription  小程序列表带分页
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiParam (输入参数：) {int}          [count]            | 每页显示数量
     * @apiParam (输入参数：) {int}          [page]             | 页数
     * @apiParam (输入参数：) {int}          [platform_type]    | 平台类型  0 字节   1微信   2 QQ  3 H5   4 app 默认0
     * @apiParam (输入参数：) {int}          [title]            | 模糊查询小程序
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {array}          results.data | 小程序数组
     * @apiReturn (返回的数据) {int}            results.data.id | 小程序系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_id | 小程序id
     * @apiReturn (返回的数据) {string}            results.data.title       | 小程序名称
     * @apiReturn (返回的数据) {string}            results.data.image_url       | 小程序logo
     * @apiReturn (返回的数据) {string}            results.data.description       | 小程序描述
     * @apiReturn (返回的数据) {string}            results.data.system_type       | 小程序系统类型android|0,ios|1  双端逗号并接
     * @apiReturn (返回的数据) {string}            results.data.client_type       | 小程序抖音|0,头条|1,皮皮虾|2  只有字节平台有这个值
     * @apiReturn (返回的数据) {string}            results.data.total_prop       | 小程序总分成
     * @apiReturn (返回的数据) {string}            results.data.role_id       | 小程序可推权限 0/1 普通用户  2 vip用户  3 合伙人 向下兼容
     * @apiReturn (返回的数据) {string}            results.data.status       | 小程序状态 待发布|201,正常|200,不可用|0,广告佣金不足下架|101,封禁|100
     * @apiReturn (返回的数据) {string}            results.data.yesterday_top       | 小程序昨日最高收益
     * @apiReturn (返回的数据) {string}            results.data.program_modular_total       | 小程序模块数量
     * @apiReturn (返回的数据) {string}            results.data.program_modular       | 小程序模块数组
     * @apiReturn (返回的数据) {string}            results.data.program_modular.pm_id       | 小程序模块系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_modular.img       | 小程序模块图片
     * @apiReturn (返回的数据) {string}            results.data.program_modular.title       | 小程序模块标题
     * @apiReturn (返回的数据) {string}            results.data.program_modular.subdescribe       | 小程序模块描述
     * @apiReturn (返回的数据) {object}            results.info       | 分数数据对象
     * @apiReturn (返回的数据) {object}            results.info.page       | 当前页数
     * @apiReturn (返回的数据) {object}            results.info.total_pages       | 总页数
     * @apiReturn (返回的数据) {object}            results.info.total_count       | 总条数
     * @apiReturn (返回的数据) {object}            results.info.total_results       | 当前显示数
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"SUCCESS","results":{"data":[{"id":38,"program_id":"aaj7yt","title":"同年同月同日生查询","image_url":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","description":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！","system_type":"0,1","client_type":"0,1","total_prop":"0.95","role_id":1,"status":200,"yesterday_top":"4896","program_modular_total":1,"program_modular":[{"pm_id":190,"img":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","title":"同年同月同日生查询","subdescribe":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！"}]}]},"info":{"page":1,"total_pages":8,"total_count":15,"total_results":2}
}
     **/
    public function getProgramData() {
        list($start,$end)           =   get_limit(1);
        $platform_type              =   get_post("platform_type/d",0);
        $title                      =   get_post("title","");
        $field                      =   array(
            "id","program_id","title","image_url","description",  
            "system_type","client_type","total_prop","role_id",
            "status"
        );
        $where                      =   array();
        $where[]                    =   ["platform_type","=",$platform_type];
        if ($title) {
            $where[]                =   ["title","like","%".$title."%"];
        }
        // $where[]                    =   ["status","=",200];
        $where[]                    =   ["pid","=",0];
        $total                      =   Db::name("program")->where($where)->count();
        $data                       =   Db::name("program")->where($where)->field($field)->limit($start,$end)->order("sort desc")->select();
        foreach ($data as $key=>$value) {
            // 昨日最高收益 缓存到凌晨
            $value["yesterday_top"]     =   cache("yesterday_top_".$value["program_id"]);
		    if (!$value["yesterday_top"] or $value["yesterday_top"]=="") {
		        $value["yesterday_top"] =   (string)rand(999,5999);
		        $cacheTimt              =   (24 * 3600)- (time() - strtotime('today'));
		        cache("yesterday_top_".$value["program_id"],$value["yesterday_top"],$cacheTimt);
		    }
		    $value['program_modular_total'] =   $this->exeProgramData($value,1);
            $value['program_modular']       =   $this->exeProgramData($value);
            
            $data[$key]                     =   $value;
        }
        
        $info                       =   get_page($total,$data);
        
        
        return_json("1","SUCCESS",array(),$data,$info);
        
        
    }
    

    /*
     * @api {post} Home/getHome  | 获取首页数据接口  
     * @apiVersion 1.0.0
     * @apiDescription  首页顶部固定参数配置
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {float}          results.my_balance          | 我的余额
     * @apiReturn (返回的数据) {float}          results.yesterday_income    | 昨天收入
     * @apiReturn (返回的数据) {float}          results.novice_guide        | 新手引导地址
     * @apiReturn (返回的数据) {array}          results.ad_list             | 消息列表数组
     * @apiReturn (返回的数据) {int}            results.ad_list.ad_id       | 消息主键
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_url      | 跳转地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_img      | 图片地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_title    | 消息内容
     * @apiReturn (返回的数据) {array}          results.banner              | 轮播图数组
     * @apiReturn (返回的数据) {string}         results.banner.image_url    | 图片地址
     * @apiReturn (返回的数据) {string}         results.banner.url          | 跳转地址
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"获取成功","results":{"my_balance":"0.00","yesterday_income":0,"ad_list":[{"ad_id":1,"ad_url":"测试","ad_img":"测试","ad_title":"测试"}],"novice_guide":"http://mp.weixin.qq.com/mp/video?__biz=MzIyODYzNDgwMw==&mid=100000019&sn=90a96745964946090a89c6b9d3992bc5&vid=wxv_1366965046735224833&idx=1&vidsn=81f90d58847b3331d7b4385af7edaae4&fromid=1&scene=18&xtrack=1#wechat_redirect","banner":[{"image_url":"https://res.appgan.com/admin/202006/202006022212140225401.png","url":"https://mp.weixin.qq.com/s/8-MbaArBuPIrXYNUnmoCHQ"}],"data":[]}}
     **/
    public function getHome() {
        $ret                        =   array();
        // 我的余额
        $ret['my_balance']          =   $this->member_info['amount'];
        // 昨天收入
        $ret['yesterday_income']    =   $this->exeYesterdayIncome();
        $ret["novice_guide"]        =   $this->exeHtml(Db::name("config")->where("name","novice_guide")->value("data"));
        $ret['ad_list']             =   $this->getAdData();
		$ret['banner']              =   $this->exeBanner();
        
        return_json("1","SUCCESS",$ret);
    }
    
    /*
     * @api {post} Home/getProgramData  | 小程序列表接口  
     * @apiVersion 1.0.0
     * @apiDescription  小程序列表带分页
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiParam (输入参数：) {int}          [count]            | 每页显示数量
     * @apiParam (输入参数：) {int}          [page]             | 页数
     * @apiParam (输入参数：) {int}          [platform_type]    | 平台类型  0 字节   1微信   2 QQ  3 H5   4 app 默认0
     * @apiParam (输入参数：) {int}          [title]            | 模糊查询小程序
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {array}          results.data | 小程序数组
     * @apiReturn (返回的数据) {int}            results.data.id | 小程序系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_id | 小程序id
     * @apiReturn (返回的数据) {string}            results.data.title       | 小程序名称
     * @apiReturn (返回的数据) {string}            results.data.image_url       | 小程序logo
     * @apiReturn (返回的数据) {string}            results.data.description       | 小程序描述
     * @apiReturn (返回的数据) {string}            results.data.system_type       | 小程序系统类型android|0,ios|1  双端逗号并接
     * @apiReturn (返回的数据) {string}            results.data.client_type       | 小程序抖音|0,头条|1,皮皮虾|2  只有字节平台有这个值
     * @apiReturn (返回的数据) {string}            results.data.total_prop       | 小程序总分成
     * @apiReturn (返回的数据) {string}            results.data.role_id       | 小程序可推权限 0/1 普通用户  2 vip用户  3 合伙人 向下兼容
     * @apiReturn (返回的数据) {string}            results.data.status       | 小程序状态 待发布|201,正常|200,不可用|0,广告佣金不足下架|101,封禁|100
     * @apiReturn (返回的数据) {string}            results.data.yesterday_top       | 小程序昨日最高收益
     * @apiReturn (返回的数据) {string}            results.data.program_modular_total       | 小程序模块数量
     * @apiReturn (返回的数据) {string}            results.data.program_modular       | 小程序模块数组
     * @apiReturn (返回的数据) {string}            results.data.program_modular.pm_id       | 小程序模块系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_modular.img       | 小程序模块图片
     * @apiReturn (返回的数据) {string}            results.data.program_modular.title       | 小程序模块标题
     * @apiReturn (返回的数据) {string}            results.data.program_modular.subdescribe       | 小程序模块描述
     * @apiReturn (返回的数据) {object}            results.info       | 分数数据对象
     * @apiReturn (返回的数据) {object}            results.info.page       | 当前页数
     * @apiReturn (返回的数据) {object}            results.info.total_pages       | 总页数
     * @apiReturn (返回的数据) {object}            results.info.total_count       | 总条数
     * @apiReturn (返回的数据) {object}            results.info.total_results       | 当前显示数
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"SUCCESS","results":{"data":[{"id":38,"program_id":"aaj7yt","title":"同年同月同日生查询","image_url":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","description":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！","system_type":"0,1","client_type":"0,1","total_prop":"0.95","role_id":1,"status":200,"yesterday_top":"4896","program_modular_total":1,"program_modular":[{"pm_id":190,"img":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","title":"同年同月同日生查询","subdescribe":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！"}]}]},"info":{"page":1,"total_pages":8,"total_count":15,"total_results":2}
}
     **/
    public function getProgramData() {
        list($start,$end)           =   get_limit(1);
        $platform_type              =   get_post("platform_type/d",0);
        $title                      =   get_post("title","");
        $field                      =   array(
            "id","program_id","title","image_url","description",  
            "system_type","client_type","total_prop","role_id",
            "status"
        );
        $where                      =   array();
        $where[]                    =   ["platform_type","=",$platform_type];
        if ($title) {
            $where[]                =   ["title","like","%".$title."%"];
        }
        // $where[]                    =   ["status","=",200];
        $where[]                    =   ["pid","=",0];
        $total                      =   Db::name("program")->where($where)->count();
        $data                       =   Db::name("program")->where($where)->field($field)->limit($start,$end)->order("sort desc")->select();
        foreach ($data as $key=>$value) {
            // 昨日最高收益 缓存到凌晨
            $value["yesterday_top"]     =   cache("yesterday_top_".$value["program_id"]);
		    if (!$value["yesterday_top"] or $value["yesterday_top"]=="") {
		        $value["yesterday_top"] =   (string)rand(999,5999);
		        $cacheTimt              =   (24 * 3600)- (time() - strtotime('today'));
		        cache("yesterday_top_".$value["program_id"],$value["yesterday_top"],$cacheTimt);
		    }
		    $value['program_modular_total'] =   $this->exeProgramData($value,1);
            $value['program_modular']       =   $this->exeProgramData($value);
            
            $data[$key]                     =   $value;
        }
        
        $info                       =   get_page($total,$data);
        
        
        return_json("1","SUCCESS",array(),$data,$info);
        
        
    }
    

    /*
     * @api {post} Home/getHome  | 获取首页数据接口  
     * @apiVersion 1.0.0
     * @apiDescription  首页顶部固定参数配置
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {float}          results.my_balance          | 我的余额
     * @apiReturn (返回的数据) {float}          results.yesterday_income    | 昨天收入
     * @apiReturn (返回的数据) {float}          results.novice_guide        | 新手引导地址
     * @apiReturn (返回的数据) {array}          results.ad_list             | 消息列表数组
     * @apiReturn (返回的数据) {int}            results.ad_list.ad_id       | 消息主键
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_url      | 跳转地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_img      | 图片地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_title    | 消息内容
     * @apiReturn (返回的数据) {array}          results.banner              | 轮播图数组
     * @apiReturn (返回的数据) {string}         results.banner.image_url    | 图片地址
     * @apiReturn (返回的数据) {string}         results.banner.url          | 跳转地址
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"获取成功","results":{"my_balance":"0.00","yesterday_income":0,"ad_list":[{"ad_id":1,"ad_url":"测试","ad_img":"测试","ad_title":"测试"}],"novice_guide":"http://mp.weixin.qq.com/mp/video?__biz=MzIyODYzNDgwMw==&mid=100000019&sn=90a96745964946090a89c6b9d3992bc5&vid=wxv_1366965046735224833&idx=1&vidsn=81f90d58847b3331d7b4385af7edaae4&fromid=1&scene=18&xtrack=1#wechat_redirect","banner":[{"image_url":"https://res.appgan.com/admin/202006/202006022212140225401.png","url":"https://mp.weixin.qq.com/s/8-MbaArBuPIrXYNUnmoCHQ"}],"data":[]}}
     **/
    public function getHome() {
        $ret                        =   array();
        // 我的余额
        $ret['my_balance']          =   $this->member_info['amount'];
        // 昨天收入
        $ret['yesterday_income']    =   $this->exeYesterdayIncome();
        $ret["novice_guide"]        =   $this->exeHtml(Db::name("config")->where("name","novice_guide")->value("data"));
        $ret['ad_list']             =   $this->getAdData();
		$ret['banner']              =   $this->exeBanner();
        
        return_json("1","SUCCESS",$ret);
    }
    
    /*
     * @api {post} Home/getProgramData  | 小程序列表接口  
     * @apiVersion 1.0.0
     * @apiDescription  小程序列表带分页
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiParam (输入参数：) {int}          [count]            | 每页显示数量
     * @apiParam (输入参数：) {int}          [page]             | 页数
     * @apiParam (输入参数：) {int}          [platform_type]    | 平台类型  0 字节   1微信   2 QQ  3 H5   4 app 默认0
     * @apiParam (输入参数：) {int}          [title]            | 模糊查询小程序
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {array}          results.data | 小程序数组
     * @apiReturn (返回的数据) {int}            results.data.id | 小程序系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_id | 小程序id
     * @apiReturn (返回的数据) {string}            results.data.title       | 小程序名称
     * @apiReturn (返回的数据) {string}            results.data.image_url       | 小程序logo
     * @apiReturn (返回的数据) {string}            results.data.description       | 小程序描述
     * @apiReturn (返回的数据) {string}            results.data.system_type       | 小程序系统类型android|0,ios|1  双端逗号并接
     * @apiReturn (返回的数据) {string}            results.data.client_type       | 小程序抖音|0,头条|1,皮皮虾|2  只有字节平台有这个值
     * @apiReturn (返回的数据) {string}            results.data.total_prop       | 小程序总分成
     * @apiReturn (返回的数据) {string}            results.data.role_id       | 小程序可推权限 0/1 普通用户  2 vip用户  3 合伙人 向下兼容
     * @apiReturn (返回的数据) {string}            results.data.status       | 小程序状态 待发布|201,正常|200,不可用|0,广告佣金不足下架|101,封禁|100
     * @apiReturn (返回的数据) {string}            results.data.yesterday_top       | 小程序昨日最高收益
     * @apiReturn (返回的数据) {string}            results.data.program_modular_total       | 小程序模块数量
     * @apiReturn (返回的数据) {string}            results.data.program_modular       | 小程序模块数组
     * @apiReturn (返回的数据) {string}            results.data.program_modular.pm_id       | 小程序模块系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_modular.img       | 小程序模块图片
     * @apiReturn (返回的数据) {string}            results.data.program_modular.title       | 小程序模块标题
     * @apiReturn (返回的数据) {string}            results.data.program_modular.subdescribe       | 小程序模块描述
     * @apiReturn (返回的数据) {object}            results.info       | 分数数据对象
     * @apiReturn (返回的数据) {object}            results.info.page       | 当前页数
     * @apiReturn (返回的数据) {object}            results.info.total_pages       | 总页数
     * @apiReturn (返回的数据) {object}            results.info.total_count       | 总条数
     * @apiReturn (返回的数据) {object}            results.info.total_results       | 当前显示数
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"SUCCESS","results":{"data":[{"id":38,"program_id":"aaj7yt","title":"同年同月同日生查询","image_url":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","description":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！","system_type":"0,1","client_type":"0,1","total_prop":"0.95","role_id":1,"status":200,"yesterday_top":"4896","program_modular_total":1,"program_modular":[{"pm_id":190,"img":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","title":"同年同月同日生查询","subdescribe":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！"}]}]},"info":{"page":1,"total_pages":8,"total_count":15,"total_results":2}
}
     **/
    public function getProgramData() {
        list($start,$end)           =   get_limit(1);
        $platform_type              =   get_post("platform_type/d",0);
        $title                      =   get_post("title","");
        $field                      =   array(
            "id","program_id","title","image_url","description",  
            "system_type","client_type","total_prop","role_id",
            "status"
        );
        $where                      =   array();
        $where[]                    =   ["platform_type","=",$platform_type];
        if ($title) {
            $where[]                =   ["title","like","%".$title."%"];
        }
        // $where[]                    =   ["status","=",200];
        $where[]                    =   ["pid","=",0];
        $total                      =   Db::name("program")->where($where)->count();
        $data                       =   Db::name("program")->where($where)->field($field)->limit($start,$end)->order("sort desc")->select();
        foreach ($data as $key=>$value) {
            // 昨日最高收益 缓存到凌晨
            $value["yesterday_top"]     =   cache("yesterday_top_".$value["program_id"]);
		    if (!$value["yesterday_top"] or $value["yesterday_top"]=="") {
		        $value["yesterday_top"] =   (string)rand(999,5999);
		        $cacheTimt              =   (24 * 3600)- (time() - strtotime('today'));
		        cache("yesterday_top_".$value["program_id"],$value["yesterday_top"],$cacheTimt);
		    }
		    $value['program_modular_total'] =   $this->exeProgramData($value,1);
            $value['program_modular']       =   $this->exeProgramData($value);
            
            $data[$key]                     =   $value;
        }
        
        $info                       =   get_page($total,$data);
        
        
        return_json("1","SUCCESS",array(),$data,$info);
        
        
    }
    

    /*
     * @api {post} Home/getHome  | 获取首页数据接口  
     * @apiVersion 1.0.0
     * @apiDescription  首页顶部固定参数配置
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {float}          results.my_balance          | 我的余额
     * @apiReturn (返回的数据) {float}          results.yesterday_income    | 昨天收入
     * @apiReturn (返回的数据) {float}          results.novice_guide        | 新手引导地址
     * @apiReturn (返回的数据) {array}          results.ad_list             | 消息列表数组
     * @apiReturn (返回的数据) {int}            results.ad_list.ad_id       | 消息主键
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_url      | 跳转地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_img      | 图片地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_title    | 消息内容
     * @apiReturn (返回的数据) {array}          results.banner              | 轮播图数组
     * @apiReturn (返回的数据) {string}         results.banner.image_url    | 图片地址
     * @apiReturn (返回的数据) {string}         results.banner.url          | 跳转地址
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"获取成功","results":{"my_balance":"0.00","yesterday_income":0,"ad_list":[{"ad_id":1,"ad_url":"测试","ad_img":"测试","ad_title":"测试"}],"novice_guide":"http://mp.weixin.qq.com/mp/video?__biz=MzIyODYzNDgwMw==&mid=100000019&sn=90a96745964946090a89c6b9d3992bc5&vid=wxv_1366965046735224833&idx=1&vidsn=81f90d58847b3331d7b4385af7edaae4&fromid=1&scene=18&xtrack=1#wechat_redirect","banner":[{"image_url":"https://res.appgan.com/admin/202006/202006022212140225401.png","url":"https://mp.weixin.qq.com/s/8-MbaArBuPIrXYNUnmoCHQ"}],"data":[]}}
     **/
    public function getHome() {
        $ret                        =   array();
        // 我的余额
        $ret['my_balance']          =   $this->member_info['amount'];
        // 昨天收入
        $ret['yesterday_income']    =   $this->exeYesterdayIncome();
        $ret["novice_guide"]        =   $this->exeHtml(Db::name("config")->where("name","novice_guide")->value("data"));
        $ret['ad_list']             =   $this->getAdData();
		$ret['banner']              =   $this->exeBanner();
        
        return_json("1","SUCCESS",$ret);
    }
    
    /*
     * @api {post} Home/getProgramData  | 小程序列表接口  
     * @apiVersion 1.0.0
     * @apiDescription  小程序列表带分页
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiParam (输入参数：) {int}          [count]            | 每页显示数量
     * @apiParam (输入参数：) {int}          [page]             | 页数
     * @apiParam (输入参数：) {int}          [platform_type]    | 平台类型  0 字节   1微信   2 QQ  3 H5   4 app 默认0
     * @apiParam (输入参数：) {int}          [title]            | 模糊查询小程序
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {array}          results.data | 小程序数组
     * @apiReturn (返回的数据) {int}            results.data.id | 小程序系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_id | 小程序id
     * @apiReturn (返回的数据) {string}            results.data.title       | 小程序名称
     * @apiReturn (返回的数据) {string}            results.data.image_url       | 小程序logo
     * @apiReturn (返回的数据) {string}            results.data.description       | 小程序描述
     * @apiReturn (返回的数据) {string}            results.data.system_type       | 小程序系统类型android|0,ios|1  双端逗号并接
     * @apiReturn (返回的数据) {string}            results.data.client_type       | 小程序抖音|0,头条|1,皮皮虾|2  只有字节平台有这个值
     * @apiReturn (返回的数据) {string}            results.data.total_prop       | 小程序总分成
     * @apiReturn (返回的数据) {string}            results.data.role_id       | 小程序可推权限 0/1 普通用户  2 vip用户  3 合伙人 向下兼容
     * @apiReturn (返回的数据) {string}            results.data.status       | 小程序状态 待发布|201,正常|200,不可用|0,广告佣金不足下架|101,封禁|100
     * @apiReturn (返回的数据) {string}            results.data.yesterday_top       | 小程序昨日最高收益
     * @apiReturn (返回的数据) {string}            results.data.program_modular_total       | 小程序模块数量
     * @apiReturn (返回的数据) {string}            results.data.program_modular       | 小程序模块数组
     * @apiReturn (返回的数据) {string}            results.data.program_modular.pm_id       | 小程序模块系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_modular.img       | 小程序模块图片
     * @apiReturn (返回的数据) {string}            results.data.program_modular.title       | 小程序模块标题
     * @apiReturn (返回的数据) {string}            results.data.program_modular.subdescribe       | 小程序模块描述
     * @apiReturn (返回的数据) {object}            results.info       | 分数数据对象
     * @apiReturn (返回的数据) {object}            results.info.page       | 当前页数
     * @apiReturn (返回的数据) {object}            results.info.total_pages       | 总页数
     * @apiReturn (返回的数据) {object}            results.info.total_count       | 总条数
     * @apiReturn (返回的数据) {object}            results.info.total_results       | 当前显示数
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"SUCCESS","results":{"data":[{"id":38,"program_id":"aaj7yt","title":"同年同月同日生查询","image_url":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","description":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！","system_type":"0,1","client_type":"0,1","total_prop":"0.95","role_id":1,"status":200,"yesterday_top":"4896","program_modular_total":1,"program_modular":[{"pm_id":190,"img":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","title":"同年同月同日生查询","subdescribe":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！"}]}]},"info":{"page":1,"total_pages":8,"total_count":15,"total_results":2}
}
     **/
    public function getProgramData() {
        list($start,$end)           =   get_limit(1);
        $platform_type              =   get_post("platform_type/d",0);
        $title                      =   get_post("title","");
        $field                      =   array(
            "id","program_id","title","image_url","description",  
            "system_type","client_type","total_prop","role_id",
            "status"
        );
        $where                      =   array();
        $where[]                    =   ["platform_type","=",$platform_type];
        if ($title) {
            $where[]                =   ["title","like","%".$title."%"];
        }
        // $where[]                    =   ["status","=",200];
        $where[]                    =   ["pid","=",0];
        $total                      =   Db::name("program")->where($where)->count();
        $data                       =   Db::name("program")->where($where)->field($field)->limit($start,$end)->order("sort desc")->select();
        foreach ($data as $key=>$value) {
            // 昨日最高收益 缓存到凌晨
            $value["yesterday_top"]     =   cache("yesterday_top_".$value["program_id"]);
		    if (!$value["yesterday_top"] or $value["yesterday_top"]=="") {
		        $value["yesterday_top"] =   (string)rand(999,5999);
		        $cacheTimt              =   (24 * 3600)- (time() - strtotime('today'));
		        cache("yesterday_top_".$value["program_id"],$value["yesterday_top"],$cacheTimt);
		    }
		    $value['program_modular_total'] =   $this->exeProgramData($value,1);
            $value['program_modular']       =   $this->exeProgramData($value);
            
            $data[$key]                     =   $value;
        }
        
        $info                       =   get_page($total,$data);
        
        
        return_json("1","SUCCESS",array(),$data,$info);
        
        
    }
    

    /*
     * @api {post} Home/getHome  | 获取首页数据接口  
     * @apiVersion 1.0.0
     * @apiDescription  首页顶部固定参数配置
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {float}          results.my_balance          | 我的余额
     * @apiReturn (返回的数据) {float}          results.yesterday_income    | 昨天收入
     * @apiReturn (返回的数据) {float}          results.novice_guide        | 新手引导地址
     * @apiReturn (返回的数据) {array}          results.ad_list             | 消息列表数组
     * @apiReturn (返回的数据) {int}            results.ad_list.ad_id       | 消息主键
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_url      | 跳转地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_img      | 图片地址  可能为空字符串
     * @apiReturn (返回的数据) {string}         results.ad_list.ad_title    | 消息内容
     * @apiReturn (返回的数据) {array}          results.banner              | 轮播图数组
     * @apiReturn (返回的数据) {string}         results.banner.image_url    | 图片地址
     * @apiReturn (返回的数据) {string}         results.banner.url          | 跳转地址
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"获取成功","results":{"my_balance":"0.00","yesterday_income":0,"ad_list":[{"ad_id":1,"ad_url":"测试","ad_img":"测试","ad_title":"测试"}],"novice_guide":"http://mp.weixin.qq.com/mp/video?__biz=MzIyODYzNDgwMw==&mid=100000019&sn=90a96745964946090a89c6b9d3992bc5&vid=wxv_1366965046735224833&idx=1&vidsn=81f90d58847b3331d7b4385af7edaae4&fromid=1&scene=18&xtrack=1#wechat_redirect","banner":[{"image_url":"https://res.appgan.com/admin/202006/202006022212140225401.png","url":"https://mp.weixin.qq.com/s/8-MbaArBuPIrXYNUnmoCHQ"}],"data":[]}}
     **/
    public function getHome() {
        $ret                        =   array();
        // 我的余额
        $ret['my_balance']          =   $this->member_info['amount'];
        // 昨天收入
        $ret['yesterday_income']    =   $this->exeYesterdayIncome();
        $ret["novice_guide"]        =   $this->exeHtml(Db::name("config")->where("name","novice_guide")->value("data"));
        $ret['ad_list']             =   $this->getAdData();
		$ret['banner']              =   $this->exeBanner();
        
        return_json("1","SUCCESS",$ret);
    }
    
    /*
     * @api {post} Home/getProgramData  | 小程序列表接口  
     * @apiVersion 1.0.0
     * @apiDescription  小程序列表带分页
     * @apiHeader {String} HTTP_TOKEN       | 用户登陆获取的token 
     * @apiHeader {String} HTTP_MEMBERID    | 公众号用户id
     * @apiParam (输入参数：) {int}          [count]            | 每页显示数量
     * @apiParam (输入参数：) {int}          [page]             | 页数
     * @apiParam (输入参数：) {int}          [platform_type]    | 平台类型  0 字节   1微信   2 QQ  3 H5   4 app 默认0
     * @apiParam (输入参数：) {int}          [title]            | 模糊查询小程序
     * @apiReturn (返回的数据) {string}         status  | 数据状态
     * @apiReturn (返回的数据) {string}         msg     | 描述信息
     * @apiReturn (返回的数据) {object}         results | 返回的所有数据对象
     * @apiReturn (返回的数据) {array}          results.data | 小程序数组
     * @apiReturn (返回的数据) {int}            results.data.id | 小程序系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_id | 小程序id
     * @apiReturn (返回的数据) {string}            results.data.title       | 小程序名称
     * @apiReturn (返回的数据) {string}            results.data.image_url       | 小程序logo
     * @apiReturn (返回的数据) {string}            results.data.description       | 小程序描述
     * @apiReturn (返回的数据) {string}            results.data.system_type       | 小程序系统类型android|0,ios|1  双端逗号并接
     * @apiReturn (返回的数据) {string}            results.data.client_type       | 小程序抖音|0,头条|1,皮皮虾|2  只有字节平台有这个值
     * @apiReturn (返回的数据) {string}            results.data.total_prop       | 小程序总分成
     * @apiReturn (返回的数据) {string}            results.data.role_id       | 小程序可推权限 0/1 普通用户  2 vip用户  3 合伙人 向下兼容
     * @apiReturn (返回的数据) {string}            results.data.status       | 小程序状态 待发布|201,正常|200,不可用|0,广告佣金不足下架|101,封禁|100
     * @apiReturn (返回的数据) {string}            results.data.yesterday_top       | 小程序昨日最高收益
     * @apiReturn (返回的数据) {string}            results.data.program_modular_total       | 小程序模块数量
     * @apiReturn (返回的数据) {string}            results.data.program_modular       | 小程序模块数组
     * @apiReturn (返回的数据) {string}            results.data.program_modular.pm_id       | 小程序模块系统主键
     * @apiReturn (返回的数据) {string}            results.data.program_modular.img       | 小程序模块图片
     * @apiReturn (返回的数据) {string}            results.data.program_modular.title       | 小程序模块标题
     * @apiReturn (返回的数据) {string}            results.data.program_modular.subdescribe       | 小程序模块描述
     * @apiReturn (返回的数据) {object}            results.info       | 分数数据对象
     * @apiReturn (返回的数据) {object}            results.info.page       | 当前页数
     * @apiReturn (返回的数据) {object}            results.info.total_pages       | 总页数
     * @apiReturn (返回的数据) {object}            results.info.total_count       | 总条数
     * @apiReturn (返回的数据) {object}            results.info.total_results       | 当前显示数
     * @apiSuccessExample {json} 01 成功示例 后面跟json
     * {"status":"1","msg":"SUCCESS","results":{"data":[{"id":38,"program_id":"aaj7yt","title":"同年同月同日生查询","image_url":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","description":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！","system_type":"0,1","client_type":"0,1","total_prop":"0.95","role_id":1,"status":200,"yesterday_top":"4896","program_modular_total":1,"program_modular":[{"pm_id":190,"img":"https://sf1-ttcdn-tos.pstatp.com/obj/developer/app/tt96b5e008099fdbda/icon6728098","title":"同年同月同日生查询","subdescribe":"CPM广告收益，支持安卓苹果端发布，输入你得姓名可以查看全网与你同年同月同日出生的人，关联小程序重名查询，支持PC端自定义发布标题！"}]}]},"info":{"page":1,"total_pages":8,"total_count":15,"total_results":2}
}
     **/
    public function getProgramData() {
        list($start,$end)           =   get_limit(1);
        $platform_type              =   get_post("platform_type/d",0);
        $title                      =   get_post("title","");
        $field                      =   array(
            "id","program_id","title","image_url","description",  
            "system_type","client_type","total_prop","role_id",
            "status"
        );
        $where                      =   array();
        $where[]                    =   ["platform_type","=",$platform_type];
        if ($title) {
            $where[]                =   ["title","like","%".$title."%"];
        }
        // $where[]                    =   ["status","=",200];
        $where[]                    =   ["pid","=",0];
        $total                      =   Db::name("program")->where($where)->count();
        $data                       =   Db::name("program")->where($where)->field($field)->limit($start,$end)->order("sort desc")->select();
        foreach ($data as $key=>$value) {
            // 昨日最高收益 缓存到凌晨
            $value["yesterday_top"]     =   cache("yesterday_top_".$value["program_id"]);
		    if (!$value["yesterday_top"] or $value["yesterday_top"]=="") {
		        $value["yesterday_top"] =   (string)rand(999,5999);
		        $cacheTimt              =   (24 * 3600)- (time() - strtotime('today'));
		        cache("yesterday_top_".$value["program_id"],$value["yesterday_top"],$cacheTimt);
		    }
		    $value['program_modular_total'] =   $this->exeProgramData($value,1);
            $value['program_modular']       =   $this->exeProgramData($value);
            
            $data[$key]                     =   $value;
        }
        
        $info                       =   get_page($total,$data);
        
        
        return_json("1","SUCCESS",array(),$data,$info);
        
        
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /**
    *   @var   我的昨天收入
    **/
    private function exeYesterdayIncome() {
        $end                        =   strtotime("today");
        $start                      =   $end - (24 * 3600);
        $where                      =   array();
        $where[]                    =   ["member_id","=",$member_id];
        $where[]                    =   ["settlement_status","=",1];
        $where[]                    =   ["create_time",">",$start];
        $where[]                    =   ["create_time","<=",$end];
        return Db::name("programCommissionRecord")->where($where)->sum('amount');
    }
    /**
    *   @var   获取广告列表
    **/
    private function getAdData(){
        // 查询弹出广告
        $where                      =   array();
        $where[]                    =   ["ad_type","=",0];
        $where[]                    =   ["ad_status","=",1];
        $where[]                    =   ["ad_start_time","<",time()];
        $where[]                    =   ["ad_end_time",">",time()];
        $field                      =   array(
            "ad_id","ad_url","ad_img","ad_title"    
        );
        return Db::table("cd_ad")->where($where)->field($field)->cache($this->public_cache_time)->order("ad_sort desc")->select();
	}
	/**
	*   @var  获取banner数据
	**/
	private function exeBanner() {
	    $field                      =   array(
	        "image_url","url"    
	    );
	    $where                      =   array();
	    $where[]                    =   ["status","=",1];
	    $banner                     =   Db::name("banner")->where($where)->field($field)->cache($this->public_cache_time)->order("sort desc")->select();
		foreach ($banner as $key=>$value) {
		    $banner[$key]['url']    =   $this->exeHtml($value['url']);
		}
		
		return $banner;
	}
	
	
	
	/**
	*   @var   获取程序模块数据
	***/
	private function exeProgramData($program,$type = 0) {
	    $field                      =   array(
	        "pm_id","img","title","subdescribe"    
	    );   
	    $where                      =   array();
	    $where[]                    =   ["program_id","=",$program['id']];
	    $where[]                    =   ["status","=",200];
	    if ($type) {
	        return Db::name("programModular")->where($where)->count();
	    }
	    return Db::name("programModular")->where($where)->limit(5)->field($field)
	                    ->order("sort desc")->select();
	}
    
    
}