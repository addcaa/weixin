<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
class Wxcontroller extends Controller
{
    public function valid(){
        echo $_GET['echostr'];
    }
    public function index(){
        $content=file_get_contents("php://input");
        $time=date('Y-m-d H:i:s');
        $str=$time.$content."\n";
        file_put_contents("logs/wx_event.log",$str,FILE_APPEND);
        $data=simplexml_load_string($content);
        // dd($data);
        //     var_dump($data);
        //     print_r($data);
        //     echo 'ToUserName:'.$data->ToUserName;echo "</br>"; //公众号id
        //     echo 'FromUserName:'.$data->FromUserName;echo "</br>"; //用户openid
        //     echo 'CreateTime:'.$data->CreateTime; echo "</br>";//时间
        //     echo 'Event:'. $data->Event; echo "</br>";//消息类型
        //     echo 'EventKey:'.$data->EventKey;echo "</br>";
        //     // 获取openid
        // exit;
        // dd($data['FromUserName']);
        $openid=$data->FromUserName;
        // echo $openid;die;
        $wx_id=$data->ToUserName;
        // dd($user_info->nickname);
        $event = $data->Event;
        if($event=='subscribe'){
            $user_info=DB::table('user')->where(['openid'=>$openid])->first();
            if($user_info){
                echo '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName>
                <FromUserName><![CDATA['.$wx_id.']]></FromUserName>
                <CreateTime>'.time().'</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
               <Content>![CDATA['.'欢迎回来'.$user_info->nickname.']]</Content>
                </xml>
                ';
            }else{
                $u=$this->getUserInfo($openid);
                $info=[
                        'openid'=>$openid,
                        'nickname'=>$u['nickname'],
                        'sex'=>$u['sex'],
                        'headimgurl'=>$u['headimgurl'],
                        'subscribe_time'=>$u['subscribe_time'],
                ];
                // dd($info);
                $arr=DB::table('user')->insert($info);
                echo '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName>
                <FromUserName><![CDATA['.$wx_id.']]></FromUserName>
                <CreateTime>'.time().'</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
               <Content>![CDATA['.'欢迎关注'.$u['nickname'].']]</Content>
                </xml>
                ';

            }

        }
    }
    /**获取微信 AccessToren */
    public function AccessToren(){
        $url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WX_APPID').'&secret='.env('WX_APPSECRET');
        $response=file_get_contents($url);
        $key='wx_access_token';
        $token=Redis::get($key);
        if($token){
            // echo "redis";
        }else{

            $arr=json_decode($response,true);
            Redis::set($key,$arr['access_token']);
            Redis::expire($key,3600);
            $token=$arr['access_token'];
        }
        return $token;
    }
    public  function test(){
        // Redis::set('11','44');
        // dd(Redis::get('11'));
        $access_token=$this->AccessToren();
        echo $access_token;
    }
    public function getUserInfo($openid){
        $url='https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->AccessToren().'&openid='.$openid.'&lang=zh_CN';
        //dd($url);
        $data=file_get_contents($url);
        $u=json_decode($data,true);
        return $u;
    }
    /**创建公众号菜单 */
    public function createMenu(){
        //菜单接口
        // echo "111";die;
        $url='https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->AccessToren();
        //接口数据
        // echo $url;die;
        $post_arr=[

            'button'=>[

                [
                    "type"=>"click",
                    "name"=>"今日歌曲",

                    "key"=>"V1001_TODAY_MUSIC"
                ],
                [
                    "name"=>"小企鹅",
                    "sub_button"=>[
                        [
                            "type"=>"view",
                            "name"=>"搜索",
                            "url"=>"http://1809cuifangfang.comcto.com/"
                        ],
                    ],
                    "key"=>"V1002_TODAY_MUSIC"
                ],
            ]
        ];
        $json_str=json_encode($post_arr,JSON_UNESCAPED_UNICODE);
        // dd($json_str);
        //发送请求
        $clinet=new client();
        $response=$clinet->request('POST',$url,[
            'body'=>$json_str
        ]);
        //处理响应
        $res_str=$response->getBody();
        echo $res_str;
    }
    public function a(){
        echo "罪行";
    }
}
