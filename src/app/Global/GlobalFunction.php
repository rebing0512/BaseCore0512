<?php

/**
 * @return string
 *
 * Test
 */
function HotIce_global_function(){
    return 'Welcome HotIce Global Function';
}


/**
 * @param $request
 * @param $oauth_server
 * @param int $appid
 * @return string|null
 *
 * 获取当前访问平台
 */
function getPlatform($request,$oauth_server,$appid = 1){
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
 * @param $arr
 * @param $key
 * @return mixed
 *
 * 数据库查询数据更具指定字段去除重复数据
 */
function unique_array($arr, $key) {
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
function returnSuccess($msg, $code = 1, $httpCode = 200)
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
function returnError($msg, $code = 0, $httpCode = 200)
{
    return returnSuccess([
        'msg' => $msg,
    ],$code,$httpCode);

}
