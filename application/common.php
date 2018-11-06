<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

function curl_request($url, $post = '', $header = '')
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);

    if (!empty($header)) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    }
    if ($post) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
    }

    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($curl);
    if (curl_errno($curl)) {
        $data =  curl_error($curl);
    }
    curl_close($curl);
    return $data;
}

/**
 * 检测 token 是否有效
 * @param $token
 * @return bool
 */
function checkToken($token)
{
   $userInfo = \think\facade\Cache::get($token);
   if (empty($userInfo)) {
       return false;
   } else {
       $userInfo = json_decode($userInfo, true);
       $cacheToken = \think\facade\Cache::get($userInfo['id']);
       if ($cacheToken != $token) {
           return false;
       } else {
           return true;
       }
   }
}

/**
 * 根据 token 获取用户信息
 * @param $token
 * @return bool|mixed
 */
function getUserInfoByToken($token)
{
    $userInfo = \think\facade\Cache::get($token);
    if (empty($userInfo)) {
        return false;
    } else {
        return json_decode($userInfo, true);
    }
}

/**
 * 检测 id 类的数组是否合法
 * @param $ids
 * @return bool
 */
function checkValidIds($ids)
{
    if (!is_array($ids) || count($ids) == 0) {
        return false;
    }

    $beginCount = count($ids);
    $result = array_filter($ids, function ($arg) {
        if (is_numeric($arg) && $arg > 0) {
            return true;
        } else {
            return false;
        }
    });

    if ($beginCount === count($result)) {
        return true;
    } else {
        return false;
    }
}

/**
 * @param $ids
 * @return bool|string
 */
function createIdStr($ids)
{
    $str = "";
    foreach ($ids as $value) {
        $str .= '[' . $value . ']|';
    }
    if (is_array($ids) && count($ids) > 0) {
        $str = substr($str, 0, -1);
    }
    return $str;
}

function pushArray(&$toArray, $fromArray)
{
    foreach ($fromArray as $item) {
        $toArray[] =  $item;
    }
}

/**
 * 数字转字母 （类似于Excel列标）
 * @param Int $index 索引值
 * @param Int $start 字母起始值
 * @return String 返回字母
 */
function intToChar($index, $start = 65) {
    $str = '';
    if (floor($index / 26) > 0) {
        $str .= intToChar(floor($index / 26)-1);
    }
    return $str . chr($index % 26 + $start);
}