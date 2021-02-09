<?php
/*
本文件由青卡大佬制作，由cangkuai阉割
本文件不加密，欢迎监督
*/
include("./includes/common.php");
$act=isset($_GET['act'])?daddslashes($_GET['act']):null;
$xc=isset($_GET['xc'])?daddslashes($_GET['xc']):null;

@header('Content-Type: application/json; charset=UTF-8');


//以下为彩虹代刷调用接口
switch($act){
	
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
}	

?>
