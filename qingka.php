<?php
/*
此文件适用于
彩虹代刷助手（青卡）
小储云商城助手（青卡）
请将此文件放到网站根目录下面

青卡插件授权站qkcj.qingkapu.cn

本文件更新于2020年3月20日，仅支持彩虹6.0以上版本

本人承诺，本文件安全无后门，因此不作加密，做到问心无愧，请各用户监督，自查
也请各级代理商不要随意修改本文件，谢谢
*/
include("./includes/common.php");
$act=isset($_GET['act'])?daddslashes($_GET['act']):null;
$xc=isset($_GET['xc'])?daddslashes($_GET['xc']):null;

@header('Content-Type: application/json; charset=UTF-8');

$key=daddslashes($_GET['key']);
if($key!=$conf['apikey'])exit('{"code":-4,"msg":"API对接密钥错误，请在后台设置密钥"}');

//以下为彩虹代刷调用接口
switch($act){
	case 'version':
	$result=array("code"=>1,"version"=>"1.5");
	exit(json_encode($result));
	break;
	//查询所有分类
	case 'getclass':
	$classhide = explode(',',$siterow['class']);
	$rs=$DB->query("SELECT * FROM pre_class ORDER BY sort ASC");
	$data = array();
	while($res = $rs->fetch()){
		if($is_fenzhan && in_array($res['cid'], $classhide))continue;
		$data[]=$res;
	}
	$result=array("code"=>0,"msg"=>"succ","data"=>$data);
	exit(json_encode($result));
	break;
	//查询所有商品
case 'gettool':
	$rs=$DB->query("SELECT * FROM `pre_tools` WHERE `active` = 1 ORDER BY `cid` ASC,`sort` ASC");
	$data = array();
	while($res = $rs->fetch()){
		if(isset($_SESSION['gift_id']) && isset($_SESSION['gift_tid']) && $_SESSION['gift_tid']==$res['tid']){
			$price=$conf["cjmoney"]?$conf["cjmoney"]:0;
		}elseif(isset($price_obj)){
			$price_obj->setToolInfo($res['tid'],$res);
			if($price_obj->getToolDel($res['tid'])==1)continue;
			$price=$price_obj->getToolPrice($res['tid']);
		}else $price=$res['price'];
		if($res['is_curl']==4){
			$isfaka = 1;
			$res['input'] = getFakaInput();
		}else{
			$isfaka = 0;
		}
		$data[]=array('tid'=>$res['tid'],'sort'=>$res['sort'],'name'=>$res['name'],'value'=>$res['value'],'price'=>$price,'input'=>$res['input'],'inputs'=>$res['inputs'],'desc'=>$res['desc'],'alert'=>$res['alert'],'shopimg'=>$res['shopimg'],'repeat'=>$res['repeat'],'multi'=>$res['multi'],'close'=>$res['close'],'prices'=>$res['prices'],'min'=>$res['min'],'max'=>$res['max'],'isfaka'=>$isfaka);
	}
	$result=array("code"=>0,"msg"=>"succ","data"=>$data,"info"=>$info);
	exit(json_encode($result));
	break;
	
    //查询所有商品2 .直接输出卖价
	case 'gettool2':
	$price_obj = new \lib\Price(1);//感谢彩虹的操作类        BY青卡3076420006
	$rs=$DB->query("SELECT * FROM `pre_tools` WHERE `active` = 1 ORDER BY `cid` ASC,`sort` ASC");
	while($res = $rs->fetch()){
			$price_obj->setToolInfo($res['tid'],$res);
			$price = $price_obj->getToolPrice($res['tid']);
		    $data[]=array('cid'=>$res['cid'],'tid'=>$res['tid'],'sort'=>$res['sort'],'name'=>$res['name'],'price'=>$price,'close'=>$res['close'],'prid'=>$res['prid'],'is_curl'=>$res['is_curl']);
	}
	$result['data'] = $data;
	exit(json_encode($result));
	break;

	//查询今日资料
	case 'getcount':
	$thtime=date("Y-m-d").' 00:00:00';
	$count1=$DB->getColumn("SELECT count(*) from pre_orders");
	$count2=$DB->getColumn("SELECT count(*) from pre_orders where status=1");
	$count3=$DB->getColumn("SELECT count(*) from pre_orders where status=0");
	$count4=$DB->getColumn("SELECT count(*) from pre_orders where addtime>='$thtime'");
	$count5=$DB->getColumn("SELECT sum(money) from pre_pay where tid!=-1 and addtime>='$thtime' and status=1");

	$strtotime=strtotime($conf['build']);//获取开始统计的日期的时间戳
	$now=time();//当前的时间戳
	$yxts=ceil(($now-$strtotime)/86400);//取相差值然后除于24小时(86400秒)

	$count6=$DB->getColumn("SELECT count(*) from pre_site");
	$count7=$DB->getColumn("SELECT count(*) from pre_site where addtime>='$thtime'");
	$count8=$DB->getColumn("SELECT sum(point) from pre_points where action='提成' and addtime>='$thtime'");

	$count11=$DB->getColumn("SELECT sum(realmoney) FROM `pre_tixian` WHERE `status` = 0");

	$count12=$DB->getColumn("SELECT sum(money) FROM `pre_pay` WHERE `type` = 'qqpay' AND `addtime` > '$thtime' AND `status` = 1");
	$count13=$DB->getColumn("SELECT sum(money) FROM `pre_pay` WHERE `type` = 'wxpay' AND `addtime` > '$thtime' AND `status` = 1");
	$count14=$DB->getColumn("SELECT sum(money) FROM `pre_pay` WHERE `type` = 'alipay' AND `addtime` > '$thtime' AND `status` = 1");

	$result=array("code"=>0,"yxts"=>$yxts,"count1"=>$count1,"count2"=>$count2,"count3"=>$count3,"count4"=>$count4,"count5"=>round($count5,2),"count6"=>$count6,"count7"=>$count7,"count8"=>round($count8,2),"count9"=>round($count9,2),"count10"=>round($count10,2),"count11"=>round($count11,2),"count12"=>round($count12,2),"count13"=>round($count13,2),"count14"=>round($count14,2),"chart"=>getDatePoint());
	exit(json_encode($result));
break;
  
    //查询最新订单
    case 'cxorder':
        $time=daddslashes($_GET['time']);
	    $roww=$DB->query("select * from pre_pay where `addtime`>'$time' && `status`='1' ");
	    while($row=$roww->fetch()){
	    	$data[]=array(
	    	   'trade_no'=>$row['trade_no'],
	    	   'name'=>$row['name'],
	    	   'type'=>$row['type'],
	    	   'money'=>$row['money'],
	    	   'addtime'=>$row['addtime'],
	    	   'ip'=>$row['ip'],
	    	   'num'=>$row['num']
	    	);
	    }
        $result=array('data'=>$data);
    	exit(json_encode($result));
	break;
	//查询最新分站提现
	case 'fz_tixian':
	 $time=daddslashes($_GET['time']);
	$roww=$DB->query("select * from pre_tixian where `addtime`>'$time' && `status`='0' ");
	 while($row=$roww->fetch()){
	    	$data[]=array(
	    	   'id'=>$row['id'],
	    	   'zid'=>$row['zid'],
	    	   'money'=>$row['money'],
	    	   'realmoney'=>$row['realmoney'],
	    	   'addtime'=>$row['addtime'],
	    	   'pay_type'=>$row['pay_type'],
	    	   'pay_account'=>$row['pay_account'],
	    	   'pay_name'=>$row['pay_name']        
	    	);
	    }
        $result=array('data'=>$data);
    	exit(json_encode($result));
	break;
	//查询工单
	case 'workorder':
    $time=daddslashes($_GET['time']);
	$roww=$DB->query("select * from pre_workorder where `addtime`>'$time' ");
	 while($row=$roww->fetch()){
	    	$data[]=array(
	    	   'id'=>$row['id'],
	    	   'zid'=>$row['zid'],
	    	   'type'=>$row['type'],
	    	   'orderid'=>$row['orderid'],
	    	   'content'=>$row['content'],
	    	   'addtime'=>$row['addtime']     
	    	);
	    }
        $result=array('data'=>$data);
    	exit(json_encode($result));
	break;
	//查询分站
	case 'queryfz':
	$time=daddslashes($_GET['time']);
	$roww=$DB->query("select * from pre_site where `addtime`>'$time' ");
	 while($row=$DB->fetch($roww)){
	    	$data[]=array(
	    	   'zid'=>$row['zid'],
	    	   'power'=>$row['power'],
	    	   'domain'=>$row['domain'],
	    	   'user'=>$row['user'],
	    	   'sitename'=>$row['sitename'],
	    	   'qq'=>$row['qq'],
	    	   'addtime'=>$row['addtime'],
	    	   'endtime'=>$row['endtime']     
	    	);
	    }
        $result=array('data'=>$data);
    	exit(json_encode($result));
	break;
	
	//分站加款
	case 'fzjk':
	$key=daddslashes($_GET['key']);
	$uid=intval($_GET['uid']);
	$money=$_GET['money'];
	if(!$key)exit('{"code":-5,"msg":"各项不能为空"}'); 
	if($key!=$conf['apikey'])exit('{"code":-4,"msg":"API对接密钥错误，请在后台设置密钥"}');
	$row=$DB->getRow("select * from pre_site where zid='$uid' limit 1 ");
	$rmb=$money+$row['rmb'];
	$is=$DB->query("update `pre_site` set `rmb`='{$rmb}' where zid='$uid' limit 1 ");
	if($is){
		exit('{"code":1,"msg":"加款成功"}'); 
	}else{
		exit('{"code":-1,"msg":"加款失败"}'); 
	}	
	break;
	
	//防红接口
	case 'create_url':
	$force = trim(daddslashes($_GET['force']));
    $url = trim(daddslashes($_GET['longurl']));
	if($force==1){
		$turl = fanghongdwz($url,true);
	}else{
		$turl = fanghongdwz($url);
	}
	if($turl == $url){
		$result = array('code'=>-1, 'msg'=>'生成失败，请更换接口');
	}elseif(strpos($turl,'/')){
		$result = array('code'=>0, 'msg'=>'succ', 'url'=>$turl);
	}else{
		$result = array('code'=>-1, 'msg'=>'生成失败：'.$turl);
	}
	exit(json_encode($result));
    break;

}	


//请各用户检查以及监督，杜绝后门，诚信经营
//by青卡  QQ：3076420006	


//以下为小储云商城插件接口
switch($xc){

	//获取分类
    case 'getclass': 
        $re = $DB->query("SELECT cid,name,state FROM sky_class");
        $array = [];
        while ($res = $DB->fetch($re)) {
            $count = $DB->count("SELECT count(*) FROM `sky_goods` WHERE `cid` = '{$res['cid']}' AND `state` = '1' ");
            $res['number'] = $count;
            $array[] = $res;
        }
        echo json_encode([
            'code' => 0,
            'msg' => 'succ',
            'data' => $array
        ]);
        break;
        
	//获取所有商品
    case 'gettool': 
	    $re = $DB->query("SELECT gid,name,price,state FROM `sky_goods`");
	    $array = [];
	    while ($res = $DB->fetch($re)) {
	        $atl = $DB->count("SELECT count(*) FROM `sky_buylog` WHERE `gid` = '{$res['gid']}' AND `date`> '$times' ");
	        $res['quota'] = $res['quota'] - $atl;
	        $array[] = $res;
	    }
	    echo json_encode([
	        'code' => 0,
	        'msg' => 'succ',
	        'data' => $array
	    ]);
    break;
    //获取文章
	case 'getnotice':
	$time=$_GET['time'];
	$roww=$DB->query("select * from sky_notice where `date`>'$time' ");
	 while($row=$DB->fetch($roww)){
	    	$data[]=array(
	    	   'id'=>$row['id'],
	    	   'title'=>$row['title'],
	    	   'content'=>$row['content'],
	    	   'browse'=>$row['browse'],
	    	   'date'=>$row['date'],  
	    	);
	    }
        $result=array('data'=>$data);
    	exit(json_encode($result));
	break;
    //查询最新订单..真是叼毛，妈的
    case 'getorder':
        $time=$_GET['time'];
   
	  
	    $roww=$DB->query("select id,trade_no,uid,input,ip,gid,price,payment,addtitm from sky_order where `addtitm`>'$time'");
	    while($row=$DB->fetch($roww)){
	    	$data[]=$row;
	    }
        $result=array('data'=>$data);
    	exit(json_encode($result));
	break;
	//订单查询
	case 'queryorder':
        $qq=$_GET['qq'];
        $qq='["'.$qq.'"]';
	    $roww=$DB->query("select id,trade_no,uid,input,ip,gid,price,payment,addtitm  from sky_order where `input`='$qq' ");
	    while($row=$DB->fetch($roww)){
	    	$data[]=$row;
	    }
        $result=array('code'=>1,'data'=>$data);
    	exit(json_encode($result));
	break;
	//查询最新注册用户
	case 'getuser':
     $key=$_GET['key'];
     if($key=='qingka'){
       $roww=$DB->query("select id,ip,name,recent_time from sky_user");
        while($row=$DB->fetch($roww)){
	    	$data[]=$row;
	    }
        $result=array('code'=>1,'data'=>$data);
    	exit(json_encode($result));
     }

	
	break;	
	//取数据统计
	case 'getcount':
    /* 屁用。还要登录。卧槽
	$index_data = admin::index_data();
	$data[]=array(
	   'jrdd'=>$index_data['statistics_1'],//今日订单
	   'zrdd'=>$index_data['statistics_1_z'],//昨日订单
	   'jrtr'=>$index_data['statistics_2'],//今日投入成本
	   'zrtr'=>$index_data['statistics_2_z'],//昨天投入成本
	   'jrqd'=>$index_data['statistics_3'],//今日签到
	   'zrqd'=>$index_data['statistics_3_z'],//昨日签到
	   'yhzs'=>$index_data['statistics_4'],//用户总数
	   'jrxz'=>$index_data['statistics_4_z'],//今日新增用户
	   'jrsk'=>$index_data['statistics_5'],//今日收款
	   'ztsk'=>$index_data['statistics_5_z'],//昨天收款
	); 
	*/ 
	     //还是自己写吧	
	    $thtime=date("Y-m-d").' 00:00:00';//今天时间0点
		$qbdd=$DB->count("SELECT count(*) from sky_order");//全部订单
		$jrdd=$DB->count("SELECT count(*) from sky_order where finishtime>='$thtime' ");//今日订单
		$yhzs=$DB->count("SELECT count(*) from sky_user");//用户总数
		$yhxz=$DB->count("SELECT count(*) from sky_user where recent_time>='$thtime' ");//今日新增用户
		$jrqd=$DB->count("SELECT count(*) from sky_journal where name='每日签到' and date>='$thtime' ");//今日签到
		
		$row=$DB->query("select count(*) from sky_pay where state=1 and addtime>='$thtime'");
		while($roww=$DB->fetch($row)){
			$jrsk+=$roww['money'];//今日累计收款
		}
		
	    $data[]=array(
	      'qbdd'=>$qbdd,
	      'jrdd'=>$jrdd,
	      'yhzs'=>$yhzs,
	      'yhxz'=>$yhxz,
	      'jrqd'=>$jrqd,
	      'jrsk'=>$jrsk,
	    );
	
	    $result=array('code'=>1,'data'=>$data);
    	exit(json_encode($result));
	break;
}


?>
