<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\Storage;
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
        $MediaId=$data->MediaId;
        $openid=$data->FromUserName;
        $wx_id=$data->ToUserName;
        $createTime=$data->CreateTime;
        $event = $data->Event;
        $MsgType=$data->MsgType;
        $content=$data->Content;
        $MsgId=$data->MsgId;
        $PicUrl=$data->PicUrl;
        $u=$this->getUserInfo($openid);
        $access=$this->AccessToren();
        $client= new Client;
        if($MsgType=="text"){
            // 下载用户文本
            $info=[
                'openid'=>$u['openid'],
                'm_name'=>$u['nickname'],
                'm_sex'=>$u['sex'],
                'm_headimg'=>$u['headimgurl'],
                'm_time'=>$createTime,
                'm_text'=> $content
            ];
            $arr=DB::table('message')->insert($info);
            echo '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName>
            <FromUserName><![CDATA['.$wx_id.']]></FromUserName>
            <CreateTime>'.time().'</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content>['.'不要失去信心，只要坚持不懈，就终会有成果的'.']</Content>
            </xml>
            ';
        }else if($MsgType=="image"){
            //获取临时素材
            $url="https://api.weixin.qq.com/cgi-bin/media/get?access_token=$access&media_id=$MediaId";
            // 下载用户图片
            // echo $url;die;
            $response=$client->get(new Uri($url));
            // var_dump($response);die;

            $headers=$response->getHeaders();//获取 相应 头信息
            // print_r($headers);die;
            $file_info=$headers['Content-disposition'][0]; //获取文件名
            // echo $file_info;die;
            $file_name=rtrim(substr($file_info,-20),'"');
            $new_file_name=substr(md5(time().mt_rand()),10,8).'_'.$file_name;
            // echo $new_file_name;die;
            // file_put_contents("/wwwroot/1809a/public/image/$new_file_name",FILE_APPEND);
            $res=Storage::put($new_file_name,$response->getBody());
            // echo $res;die;
            $info=[
                'openid'=>$u['openid'],
                'm_name'=>$u['nickname'],
                'm_sex'=>$u['sex'],
                'm_headimg'=>$u['headimgurl'],
                'm_time'=>$createTime,
                'm_image'=>"wwwroot/weixin/storage/app".$new_file_name
            ];

            $arr=DB::table('message')->insert($info);
        }else if($MsgType=="voice"){
            //获取语音
            $url="https://api.weixin.qq.com/cgi-bin/media/get?access_token=$access&media_id=$MediaId";
            $voictime=date('Y-m-d H:i:s');
            $serfil=file_get_contents($url);
            file_put_contents("/wwwroot/1809a/public/voice/$voictime.mp3",$serfil,FILE_APPEND);
        }else if($MsgType=="video"){
            //视频接收
            $url="https://api.weixin.qq.com/cgi-bin/media/get?access_token=$access&media_id=$MediaId";
            $videotime=date('Y-m-d H:i:s');
            $resvideo=file_get_contents($url);
            file_put_contents("/wwwroot/weixin/video/$videotime.mp4",$resvideo,FILE_APPEND);
        }
        //判断登录
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

    /**获取微信 access_token */
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
    /**用户信息 */
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
}
