<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
class UserController extends Controller{
    public $redis_h_u_key ='str:h:u';
    public function login(Request $request){
        $user_name=$request->input('u');
        $pass=$request->input('p');

//        print_r($user_name);
//        print_r($pass);
        if(1){
            $uid=1000;
            $str=time()+$uid+mt_rand(1000,9999);
            //echo $str;
            $token=substr(md5($str),10,20);
            //echo $token;
            $key=$this->redis_h_u_key.$uid;
            Redis::hSet($key,'token',$token);
            Redis::expire($key,3600*24*7);
            echo $token;
        }else{

        }
    }
    public function info(Request $request){
        // echo 1111;
        $u=$request->input('u');
        if($u){
            $token=str_random(10);
            $data=[
                'errno'=>4001,
                'msg'=>$token
            ];
        }else{
            $data=[
                'errno'=>5200,
                'msg'=>'HTTP_TOKEN'
            ];

        }
        $res=json_encode($data);
        print_r($res);
    }

    //个人中心
    public function uCenter(){
        $uid=$_GET['uid'];
        //print_r($_SERVER);die;
        if(empty($_SERVER['HTTP_TOKEN'])){
            $response=[
                'errno'=>50000,
                'msg'=>'Token Require!!',
            ];

        }else{
            //验证token是否有效  是否过期 是否伪造
            $key=$this->redis_h_u_key.$uid;
            $token=Redis::hGet($key,'token');
            //print_r($token);
            if($token==$_SERVER['HTTP_TOKEN']){
                $response=[
                    'errno'=>0,
                    'msg'=>'ok',
                    'data'=>[
                        'aaa'=>'11',
                        'bbb'=>'22',
                    ]
                ];
            }else{
                $response='验证错误';
            }
        }
        //print_r($response);
        return $response;
    }


    //防刷
    public function order(){
        $uri=$_SERVER['REQUEST_URI'];
        //echo $uri;
        $uri_hash=substr(md5($uri),0,10);
        //echo $uri_hash;
        $ip=$_SERVER['REMOTE_ADDR'];
        $redis_keys='str:'.$uri_hash.':'.$ip;
        //echo $redis_keys;
        $num=Redis::incr($redis_keys);
        //echo $num;
        Redis::expire($redis_keys,60);//过期时间
        if($num>10){  //非法请求
            $response=[
                'errno' =>40003,
                'msg'   => 'Invalid Request!!!',
            ];
            Redis::expire($redis_keys,600);//
            //记录非法ip
            $redis_invalid_ip='s:invalid:ip';
            Redis::sAdd($redis_invalid_ip,$ip);
        }else{
            $response=[
                'errno' =>0,
                'msg'   =>'ok',
                'data'  =>[
                    'aaa'=>'bbb'
                ]
            ];
        }
        return $response;
    }
}