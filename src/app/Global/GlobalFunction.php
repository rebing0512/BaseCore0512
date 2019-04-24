<?php

/**
 * @param $request
 * @param array $oauth_server
 * @param int $appid
 * @return string|null
 *
 * 获取当前访问平台
 */
function MBCGetPlatform($request,array $oauth_server,int $appid = 1){
    $platform = null;
    if ($appid) {
        $ConfigOAuthServer = $oauth_server;
        $roles = isset($ConfigOAuthServer['roles'][$appid]) ? $ConfigOAuthServer['roles'][$appid] : false;
        if ($roles) {
            # 渠道：Andorid/iOS/weapp/weixin
            $platform = strtolower($roles['channel']);
            if ($platform == 'weixin') $platform = 'wechat';
        }
    }
    /**
     * 验证平台
     */
    # 如果有platform参数
    if (!$platform)
        $platform = preg_match('/mbcore/i', $request->server('HTTP_USER_AGENT')) && preg_match('/iphone|ios/i', $request->server('HTTP_USER_AGENT')) ? 'ios' : null;

    # 如果UA中有mbcore字样
    if (!$platform)
        $platform = preg_match('/mbcore/i', $request->server('HTTP_USER_AGENT', null)) ? 'android' : null;

    # 判定微信环境
    if (!$platform) {
        $platform = preg_match('/micromess/i', $request->server('HTTP_USER_AGENT', null)) ? 'wechat' : null;
        if ($request->get('platform', null) == 'miniProgram') {
            $platform = 'weapp';
        }
    }
    #/ 未来客户端
    if (!$platform)
        $platform = preg_match('/mbcclient/i', $request->server('HTTP_USER_AGENT', null)) ? 'client' : null;

    # 判定手机环境:android-wap
    if (!$platform)
        $platform = preg_match('/android/i', $request->server('HTTP_USER_AGENT', null)) ? 'android-wap' : null;
    # 判定手机环境:iphone-wap
    if (!$platform)
        $platform = preg_match('/iphone/i', $request->server('HTTP_USER_AGENT', null)) ? 'iphone-wap' : null;
    # 判定手机环境:ipad-wap
    if (!$platform)
        $platform = preg_match('/ipad/i', $request->server('HTTP_USER_AGENT', null)) ? 'ipad-wap' : null;

    # 否则为一般浏览器
    if (!$platform)
        $platform = 'pc';

    return $platform;
}

/**
 * @param array $arr
 * @param string $key
 * @return mixed
 *
 * 数据库查询数据更具指定字段去除重复数据
 */
function MBCUniqueArray(array $arr, string $key) {
    $tmp_arr = array();
    $tmp_array = array();
    foreach ($arr as $k => $v) {
        # 搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
        if (in_array($v[$key], $tmp_arr)) {
            unset($arr[$k]);
        } else {
            $tmp_arr[] = $v[$key];
            $tmp_array[] = $v;
        }
    }
    # sort 函数对数组进行排序
    sort($tmp_array);
    return $tmp_array;
}

/**
 * @param $msg
 * @param int $code
 * @param int $httpCode
 * @return mixed
 *
 * Return Success
 */
function returnSuccess($msg, int $code = 1, int $httpCode = 200)
{
    return response()->json([
        'code' => $code,
        'result' => $msg
    ], $httpCode, [], 271);
}

/**
 * @param $msg
 * @param int $code
 * @param int $httpCode
 * @return mixed
 *
 * Return Error
 */
function returnError($msg, int $code = 0,int$httpCode = 200)
{
    return returnSuccess([
        'msg' => $msg,
    ],$code,$httpCode);

}

/**
 * 安全base64_encode将可能会被浏览器破坏的符号替换成其他符号
 * @param string $data
 * @return string
 */
function MBCSafeEncode(string $data)
{
    return strtr(base64_encode($data),[
        '=' => null,
        '/' => '_',
        '+' => '-'
    ]);
}

/**
 * 安全解码 对应 safeEncode
 * @param string $data
 * @return string
 */
function MBCSafeDecode(string $data)
{
    return base64_decode(strtr($data,[
        '_' => '/',
        '-' => '+'
    ]));
}

/**
 * 简单加密
 * @param mixed $data 要加密的内容
 * @param string $key 密钥
 * @return string
 */
function MBCEncrypt($data, $key)
{
    $iv = openssl_random_pseudo_bytes (16);
    $data = openssl_encrypt(serialize($data),'rc4',$key,1,null);
    return safeEncode($data);
}

/**
 * 简单解密
 * @param $data
 * @param $key
 * @return mixed|string
 */
function MBCDecrypt($data,$key)
{
    $data = safeDecode($data);
    $iv = substr($data,0,16);
    $data = @unserialize(openssl_decrypt($data,'rc4',$key,1,null));
    return $data;
}

/**
 * @param $parameters
 * @param array $extra_params
 * @param string $size
 * @param int $mode
 * @return mixed|string
 *
 * Get Image Url
 */
function getImageUrl($parameters,array $extra_params,string $size='0,1000',int $mode = 1) {
    $baseUrl  = $extra_params['baseUrl'];
    $storage_url  = $extra_params['storage_url'];
    $storage_local_tag  = $extra_params['storage_local_tag'];
    $storage_local_group  = $extra_params['storage_local_group']??null;
    $storage_get_url_api  = $extra_params['storage_get_url_api'];
    if(is_array($parameters)){
        $pictureHash = isset($parameters['pictureHash'])?$parameters['pictureHash']:"";
        $size = isset($parameters['size'])?$parameters['size']:'0,1000';
        $is_old = isset($parameters['is_old'])?$parameters['is_old']:0;
        $type = isset($parameters['type'])?$parameters['type']:"";
        $extraPath = isset($parameters['extraPath'])?'/'.$parameters['extraPath']:"";
        if($is_old && $type!=''){
            $file_group = isset($parameters['storage_local_group'])?$parameters['storage_local_group']:$storage_local_group;
            if($file_group) $file_group = '/'.trim($file_group,'/');
            $filepath = $storage_local_tag[$type];
            if($baseUrl!="" && $file_group!=""  && $filepath!="" ){
                if (!empty($pictureHash)) {
                    $fileUrl = trim($baseUrl,'/').$file_group.$filepath.$extraPath.'/'.trim($pictureHash,'/')."?".$size;
                    return $fileUrl;
                } else {
                    return $storage_url.'/image/no_image.jpg';
                }
            }
        }
    }else{
        $pictureHash = $parameters;
    }
    $pattern = '@(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|([\s()<>]+|(\([\s()<>]+))*\))+(?:([\s()<>]+|(\([\s()<>]+))*\)|[^\s`!(){};:\'".,<>?«»“”‘’]))@';
    if(preg_match($pattern, $pictureHash)){
        return $pictureHash;
    }
    return $pictureHash?$storage_url.$storage_get_url_api.'/'.$pictureHash.'?mode='.$mode.'&size='.$size.'&t=1&redirect=1':$storage_url.'/image/no_image.jpg';
}


/**
 * @param $userHash
 * @param $storage_url
 * @param null $size
 * @return string
 *
 * 获取用户头像Url
 */
function getUserAvatar($userHash,$storage_url,$size=null)
{
    $pattern = '@(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|([\s()<>]+|(\([\s()<>]+))*\))+(?:([\s()<>]+|(\([\s()<>]+))*\)|[^\s`!(){};:\'".,<>?«»“”‘’]))@';
    if(preg_match($pattern, $userHash)){
        return $userHash;
    }
    return $userHash?$storage_url.'/api/avatar/'.$userHash.'?size='.$size.'&t=1&redirect=1':$storage_url.'/image/no_image.jpg';

}

/**
 * @param $tel
 * @param null $onlyMob
 * @return array
 *
 * 电话号验证
 */
function MBCPhoneNumVerify($tel,$onlyMob=null)
{
    $isMob = "/^1[3-5,4,7,8]{1}[0-9]{9}$/";
    $isTel="/^([0-9]{3,4}-)?[0-9]{7,8}$/";
    $special = '/^(4|8)00(-\d{3,4}){2}$/';//'/^(4|8)00(\d{4,8})$/';
    $data3 = substr($tel, 0,3);
    $data2 = substr($tel, 0,2);
    $msg = 'success';
    $code = 1;
    if(in_array($data2,['14'])){
        if($data3 != '147'){
            $msg = $data3.'号段不存在';
            $code = 0;

            return [
                'code' => $code,
                'msg'=>$msg
            ];
        }
    }
    if($onlyMob){//只验证手机号，不验证座机和400|800的号码
        if (preg_match($isMob, $tel)) {
            return [
                'code' => $code,
                'msg' => $msg
            ];
        } else {
            $msg = '手机号码格式不正确';
            $code = 0;

            return [
                'code' => $code,
                'msg' => $msg
            ];
        }
    }else {// 手机、座机、以及400|800号码的验证
        if (preg_match($isMob, $tel)) {
            return [
                'code' => $code,
                'msg' => $msg
            ];
        } elseif (preg_match($special, $tel)) {
            return [
                'code' => $code,
                'msg' => $msg
            ];
        } elseif (preg_match($isTel, $tel)) {
            return [
                'code' => $code,
                'msg' => $msg
            ];
        } else {
            $msg = '手机或电话号码格式不正确。如果是固定电话，必须形如(010-87876787 或者 400-000-0000)!';
            $code = 0;

            return [
                'code' => $code,
                'msg' => $msg
            ];
        }
    }
}

/**
 * @param $arrays
 * @param $sort_key
 * @param int $sort_order
 * @param int $sort_type
 * @return bool
 *
 * 多维数组自定义排序
 */
function MBCArrSort($arrays,$sort_key,$sort_order=SORT_ASC,$sort_type=SORT_NUMERIC ){
    if(is_array($arrays)){
        foreach ($arrays as $array){
            if(is_array($array)){
                $key_arrays[] = $array[$sort_key];
            }else{
                return false;
            }
        }
    }else{
        return false;
    }
    array_multisort($key_arrays,$sort_order,$sort_type,$arrays);
    return $arrays;
}

/**
 * @return string
 * 唯一订单编号
 */
function GenerateOrderNumber(){
    return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
}

/**
 * @param $s
 * @return bool|string
 *
 * 取得汉字拼音首字母
 */
function MBCPinyinToFirst($s) {
    $ascii = ord($s[0]);
    if($ascii > 0xE0) {
        $s = iconv('UTF-8', 'GB2312//IGNORE', $s[0].$s[1].$s[2]);
    }elseif($ascii < 0x80) {
        if($ascii >= 65 && $ascii <= 90) {
            return strtolower($s[0]);
        }elseif($ascii >= 97 && $ascii <= 122) {
            return $s[0];
        }else{
            return false;
        }
    }

    if(strlen($s) < 2) {
        return false;
    }

    $asc = ord($s[0]) * 256 + ord($s[1]) - 65536;

    if($asc>=-20319 && $asc<=-20284) return 'a';
    if($asc>=-20283 && $asc<=-19776) return 'b';
    if($asc>=-19775 && $asc<=-19219) return 'c';
    if($asc>=-19218 && $asc<=-18711) return 'd';
    if($asc>=-18710 && $asc<=-18527) return 'e';
    if($asc>=-18526 && $asc<=-18240) return 'f';
    if($asc>=-18239 && $asc<=-17923) return 'g';
    if($asc>=-17922 && $asc<=-17418) return 'h';
    if($asc>=-17417 && $asc<=-16475) return 'j';
    if($asc>=-16474 && $asc<=-16213) return 'k';
    if($asc>=-16212 && $asc<=-15641) return 'l';
    if($asc>=-15640 && $asc<=-15166) return 'm';
    if($asc>=-15165 && $asc<=-14923) return 'n';
    if($asc>=-14922 && $asc<=-14915) return 'o';
    if($asc>=-14914 && $asc<=-14631) return 'p';
    if($asc>=-14630 && $asc<=-14150) return 'q';
    if($asc>=-14149 && $asc<=-14091) return 'r';
    if($asc>=-14090 && $asc<=-13319) return 's';
    if($asc>=-13318 && $asc<=-12839) return 't';
    if($asc>=-12838 && $asc<=-12557) return 'w';
    if($asc>=-12556 && $asc<=-11848) return 'x';
    if($asc>=-11847 && $asc<=-11056) return 'y';
    if($asc>=-11055 && $asc<=-10247) return 'z';
    return false;
}
/**
 * @param $zh
 * @return string
 *
 * 获取整条字符串汉字拼音首字母
 */
function MBCFirstSpelling($zh){
    $ret = "";
    $s1 = iconv("UTF-8","gb2312", $zh);
    $s2 = iconv("gb2312","UTF-8", $s1);
    if($s2 == $zh){$zh = $s1;}
    for($i = 0; $i < strlen($zh); $i++){
        $s1 = substr($zh,$i,1);
        $p = ord($s1);
        if($p > 160){
            $s2 = substr($zh,$i++,2);
            $ret .= MBCPinyinToFirst($s2);
        }else{
            $ret .= $s1;
        }
    }
    return $ret;
}
/**
 * 两个位置的距离计算
 * @param float $longitude1
 * @param float $latitude1
 * @param float $longitude2
 * @param float $latitude2
 * @param int $unit
 * @param int $decimal
 * @return float
 */
function MBCGetDistance($longitude1, $latitude1, $longitude2, $latitude2, $unit=2, $decimal=2){

    $EARTH_RADIUS = 6370.996; // 地球半径系数
    //$PI = 3.1415926;
    $PI = M_PI;

    $radLat1 = $latitude1 * $PI / 180.0;
    $radLat2 = $latitude2 * $PI / 180.0;

    $radLng1 = $longitude1 * $PI / 180.0;
    $radLng2 = $longitude2 * $PI /180.0;

    $a = $radLat1 - $radLat2;
    $b = $radLng1 - $radLng2;

    $distance = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
    $distance = $distance * $EARTH_RADIUS * 1000;

    if($unit==2){
        $distance = $distance / 1000;
    }

    return round($distance, $decimal);
}

/**
 * @param $str
 * @return string
 *
 * Unicode编码方法【将中文转为Unicode字符】
 */
function unicodeEncode($str){
    # split word
    preg_match_all('/./u',$str,$matches);
    $unicodeStr = "";
    foreach($matches[0] as $m){
        # 拼接
        $unicodeStr .= "&#".base_convert(bin2hex(iconv('UTF-8',"UCS-4",$m)),16,10);
    }
    return $unicodeStr;
}

/**
 * @param $unicode_str
 * @return string
 *
 * unicode解码方法【将的unicode字符转换成中文】
 */
function unicodeDecode($unicode_str){
    $json = '{"str":"'.$unicode_str.'"}';
    $arr = json_decode($json,true);
    if(empty($arr)) return '';
    return $arr['str'];
}



