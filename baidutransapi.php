<?php
/***************************************************************************

 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 * 
**************************************************************************/

require_once('workflows.php');


define("CURL_TIMEOUT",   10); 
define("URL",            "http://api.fanyi.baidu.com/api/trans/vip/translate"); 
define("APP_ID",         "20160406000017907"); //替换为您的APPID
define("SEC_KEY",        "vyxl7B5OPSrLBSX0vHw4");//替换为您的密钥

class Translate{
//翻译入口
public function getTranslation($query)
{
    $workflows = new Workflows();

    $args = array(
        'q' => $query,
        'appid' => APP_ID,
        'salt' => rand(10000,99999),
        'from' => 'auto',
        'to' => 'zh',

    );
    $args['sign'] = self::buildSign($query, APP_ID, $args['salt'], SEC_KEY);
    $ret = self::call(URL, $args);
    $ret = json_decode($ret, true);


                foreach ($ret as $translation) {
                    // var_dump($translation);
                    // 
                    

                         if(is_array($translation)){


                               // print_r($translation);
                                
                             foreach ($translation as $translations) {


                                    $c_keys = array_keys( $translations ); 

                                    foreach( $c_keys as $key ):                     // For each of those keys
                if ( $key == 'dst' ):
                 $workflows->result($key,
                                    $translations[$key],
                                    $translations[$key],
                                    $query,
                                    "translate.png");
                endif;
            endforeach;

                 //                 foreach ($translations as $key=>$value) {
                                
                 //                echo "abc=======================";
                 //                if ($key == 'dst') {
                 //                    # code...
                 //                    $workflows->result($key,
                 //                    $value,
                 //                    $value,
                 //                    $query,
                 //                    "translate.png");
                 //                }

                    
                 // }
                 }
                        }
                     
                }
            
// foreach ($ret->trans_result as $translation):
// $result['src']  =   $translation->src;
// $result['dst']  =   $translation->dst;
// $wf->result(1, 'http://www.baidu.com',$result['dst'],$query,'translate.png','yes');
// endforeach;

       
// Export Results
echo $workflows->toxml();
}

//加密
public function buildSign($query, $appID, $salt, $secKey)
{/*{{{*/
    $str = $appID . $query . $salt . $secKey;
    $ret = md5($str);
    return $ret;
}/*}}}*/

//发起网络请求
public function call($url, $args=null, $method="post", $testflag = 0, $timeout = CURL_TIMEOUT, $headers=array())
{/*{{{*/
    $ret = false;
    $i = 0; 
    while($ret === false) 
    {
        if($i > 1)
            break;
        if($i > 0) 
        {
            sleep(1);
        }
        $ret = self::callOnce($url, $args, $method, false, $timeout, $headers);
        $i++;
    }
    return $ret;
}/*}}}*/

public function callOnce($url, $args=null, $method="post", $withCookie = false, $timeout = CURL_TIMEOUT, $headers=array())
{/*{{{*/
    $ch = curl_init();
    if($method == "post") 
    {
        $data = self::convert($args);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_POST, 1);
    }
    else 
    {
        $data = self::convert($args);
        if($data) 
        {
            if(stripos($url, "?") > 0) 
            {
                $url .= "&$data";
            }
            else 
            {
                $url .= "?$data";
            }
        }
    }
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if(!empty($headers)) 
    {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    if($withCookie)
    {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $_COOKIE);
    }
    $r = curl_exec($ch);
    curl_close($ch);
    return $r;
}/*}}}*/

public function convert(&$args)
{/*{{{*/
    $data = '';
    if (is_array($args))
    {
        foreach ($args as $key=>$val)
        {
            if (is_array($val))
            {
                foreach ($val as $k=>$v)
                {
                    $data .= $key.'['.$k.']='.rawurlencode($v).'&';
                }
            }
            else
            {
                $data .="$key=".rawurlencode($val)."&";
            }
        }
        return trim($data, "&");
    }
    return $args;
}/*}}}*/

}
?>