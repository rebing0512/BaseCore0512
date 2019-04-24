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
