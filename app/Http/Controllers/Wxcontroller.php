<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\Storage;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

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
        // echo "SUCCESS";
        $data=simplexml_load_string($content);
        // print_r($data);die;
        $MediaId=$data->MediaId;
        $openid=$data->FromUserName;
        // echo $openid;die;
        $wx_id=$data->ToUserName;
        $createTime=$data->CreateTime;
        $event = $data->Event;
        $MsgType=$data->MsgType;
        $content=$data->Content;
        $MsgId=$data->MsgId;
        $PicUrl=$data->PicUrl;
        $u=$this->getUserInfo($openid);
        // print_r($u);die;
        $client= new Client;
        $access=$this->test();
        if($MsgType=="text"){
            // 下载用户文本
            // echo $content;die;
            if(strpos($content,'+天气')){
                $city=explode('+',$content)[0];
                // echo "$city";
                $url="https://free-api.heweather.net/s6/weather/now?key=HE1904161049361666&location=$city";
                // echo $url;die;
                $arr=json_decode(file_get_contents($url),true);
                // print_r($arr);
                if($arr['HeWeather6'][0]['status']=="unknown location"){
                    echo '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName>
                    <FromUserName><![CDATA['.$wx_id.']]></FromUserName>
                    <CreateTime>'.time().'</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content>['.'城市名称不正确'.']</Content>
                    </xml>
                    ';
                }else{
                    $tmp=$arr['HeWeather6'][0]['now']['tmp'];//温度
                    $cond_txt=$arr['HeWeather6'][0]['now']['cond_txt'];//fen'li
                    $wind_sc=$arr['HeWeather6'][0]['now']['wind_sc'];//风力
                    $hum=$arr['HeWeather6'][0]['now']['hum']; // 湿度
                    $wind_dir=$arr['HeWeather6'][0]['now']['wind_dir'];// 风向
                    $res="$cond_txt 温度:$tmp 风力:$wind_sc 湿度:$hum 风向:$wind_dir";
                    echo '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName>
                    <FromUserName><![CDATA['.$wx_id.']]></FromUserName>
                    <CreateTime>'.time().'</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content>['.$res.']</Content>
                    </xml>
                    ';
                }
            }
            // $info=[
            //     'openid'=>$openid,
            //     'm_name'=>$u['nickname'],
            //     'm_sex'=>$u['sex'],
            //     'm_headimg'=>$u['headimgurl'],
            //     'm_time'=>$createTime,
            //     'm_text'=> $content
            // ];
            // dd($info);
            // $arr=DB::table('message')->insert($info);
            // dd($arr);
            if($content=="最新商品"){
                $goods_info=DB::table('goods')->where(['goods_static'=>1])->first();
                // foreach($goods_info as $k=>$v){
                    $goods_name=$goods_info->goods_name;
                    $goods_img=$goods_info->goods_img;
                    $url="http://mmbiz.qpic.cn/mmbiz_jpg/Hiak941wazMV8NXcT7cfIL1NMBf26bia8GOib2v1vO2qwQZgvR1vj9NibdFS7RBseaPPDRYpsqhJzTHBgpft2INGfA/0?wx_fmt=jpeg";
                    $desc="图片消息";
                    $surl="http://1809cuifangfang.comcto.com/weixin/goodslist";

                    echo '<xml>
                    <ToUserName><![CDATA['.$openid.']]></ToUserName>
                    <FromUserName><![CDATA['.$wx_id.']]></FromUserName>
                    <CreateTime>'.time().'</CreateTime>
                    <MsgType><![CDATA[news]]></MsgType>
                    <ArticleCount>1</ArticleCount>
                    <Articles>
                      <item>
                        <Title><![CDATA['.$goods_name.']]></Title>
                        <Description><![CDATA['.$desc.']]></Description>
                        <PicUrl><![CDATA['.$url.']]></PicUrl>
                        <Url><![CDATA['.$surl.']]></Url>
                      </item>
                    </Articles>
                  </xml>';
                // }

            }

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
            $res=Storage::put($new_file_name,$response->getBody());//保存
            // echo $res;die;
            $info=[
                'openid'=>$openid,
                'm_name'=>$u['nickname'],
                'm_sex'=>$u['sex'],
                'm_headimg'=>$u['headimgurl'],
                'm_time'=>$createTime,
                'm_image'=>"wwwroot/weixin/storage/app".$new_file_name
            ];
            $arr=DB::table('message')->insert($info);
            if($arr){
                echo "成功";
            }else{
                echo "失败";
            }
        }else if($MsgType=="voice"){
            //获取语音
            $url="https://api.weixin.qq.com/cgi-bin/media/get?access_token=$access&media_id=$MediaId";
            $response=$client->get(new Uri($url));
            // var_dump($response);die;
            $headers=$response->getHeaders();//获取 相应 头信息
            // print_r($headers);die;
            $file_info=$headers['Content-disposition'][0]; //获取文件名
            // echo $file_info;die;
            $voice_name=rtrim(substr($file_info,-20),'"');
            $res=Storage::put($voice_name,$response->getBody());
            $info=[
                'openid'=>$openid,
                'm_name'=>$u['nickname'],
                'm_sex'=>$u['sex'],
                'm_headimg'=>$u['headimgurl'],
                'm_time'=>$createTime,
                'm_voice'=>"wwwroot/weixin/storage/app".$voice_name
            ];
            $arr=DB::table('message')->insert($info);
            if($arr){
                echo "成功";
            }else{
                echo "失败";
            }
        }else if($MsgType=="video"){
            //视频接收
            $url="https://api.weixin.qq.com/cgi-bin/media/get?access_token=$access&media_id=$MediaId";
            $videotime=date('Y-m-d H:i:s');
            $resvideo=file_get_contents($url);
            file_put_contents("/wwwroot/1890a/video/$videotime.mp4",$resvideo,FILE_APPEND);
        }
        //判断登录
        if($event=='subscribe'){
            $user_info=DB::table('user')->where(['openid'=>$openid])->first();
            if($user_info){
            $res=DB::table('user')->where(['openid'=>$openid])->update(['is_server'=>1]);
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
        }else if($event=="unsubscribe"){
            // echo "取关";
            $res=DB::table('user')->where(['openid'=>$openid])->update(['is_server'=>2]);
            // echo $res;die;
        }
    }
    /**获取微信 access_token */
    public function AccessToren(){
        $key='wx_access_token';
        $token=Redis::get($key);
        if($token){
            //echo "redis";
        }else{
            $url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WX_APPID').'&secret='.env('WX_APPSECRET');
            $response=file_get_contents($url);
            // echo $response;die;
            $arr=json_decode($response,true);
            Redis::set($key,$arr['access_token']);
            Redis::expire($key,7200);
            $token=$arr['access_token'];
        }
        return $token;
    }
    public  function test(){
        $access_token=$this->AccessToren();
        return $access_token;
    }
    /**用户信息 */
    public function getUserInfo($openid){
        $url='https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->test().'&openid='.$openid.'&lang=zh_CN';
        // dd($url);
        $data=file_get_contents($url);
        $u=json_decode($data,true);
        return $u;
    }
    /**
     * 创建公众号菜单
    */
    public function createMenu(){
        //菜单接口
        $url='https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->test();
        //接口数据
        // echo $url;die;
        $post_arr=[
            'button'=>[
                [
                    "type"=>"click",
                    "name"=>"嘿嘿",

                    "key"=>"V1001_TODAY_MUSIC"
                ],
                [
                    "name"=>"O(∩_∩)O",
                    "sub_button"=>[
                        [
                            "type"=>"view",
                            "name"=>"点击带你飞",
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
        echo $res_str;die;
    }
    /**群发
     *
     *$openid_arr openid
     *$content 文本
    */
    public function sendmse($openid_arr,$content){
        $msg=[
            'touser'=>$openid_arr,
            'msgtype'=>"text",
            'text'=>[
                'content'=>$content
            ],
        ];
        $access=$this->test();
        $data=json_encode($msg,JSON_UNESCAPED_UNICODE);
        $url="https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=$access";
        $client= new Client();
        $response=$client->request('post',$url,[
            'body'=>$data,
        ]);
        return $response->getBody();
    }
    public function send(){
        $arr=DB::table('user')->where(['is_server'=>1])->get()->toArray();
        // print_r($arr);die;
        $openid_arr=array_column($arr,'openid');
        //  print_r($openid_arr);die;
        $msg="成功就是把复杂的问题简单化，然后狠狠去做";
        $response=$this->sendmse($openid_arr,$msg);
        echo $response;
    }
    public function goodslist(Request $request){
        $wxconfig=$request->signPackage;
        // dd($wxconfig);
        return view('/weixin/goodslist',['wxconfig'=>$wxconfig]);
    }
}
