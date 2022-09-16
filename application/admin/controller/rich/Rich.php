<?php
namespace app\admin\controller\rich;
use app\common\controller\Backend;
/**
 * 号码管理
 *
 * @icon fa fa-circle-o
 */
class Rich extends Backend
{
    /**
     * Rich模型对象
     * @var \app\admin\model\rich\Rich
     */
    protected $model = null;
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\rich\Rich;
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /*手动生成号码*/
    public function manual_mkBall(){
        $red = '';
        $blue = '';
        for ($i=0;$i<5;$i++) {
            $red_rand = $this->red_rand(01,33,6);
            $blue_rand = $this->blue_rand();
            $red .= '<font color="red">'.implode(' ',$red_rand).'</font>'.' '.'<font color="blue">'.$blue_rand.'</font>'.'<br/>';
            $blue .= $blue_rand.'<br/>';
            $original_number[$i] = implode('+', $red_rand).'+'.$blue_rand;
        }
        $lottery_date = $this->get_week_arr(date('Y-m-d'));
        $insert = [
            'red_num'           =>  $red,
            'blue_num'          =>  $blue,
            'type'              =>  1,
            'createtime'        =>  time(),
            'updatetime'        =>  time(),
            'status'            =>  1,
            'number_periods'    =>  '',
            'lottery_date'      =>  $lottery_date,
            'original_number'   =>  json_encode($original_number),
        ];
        $result = $this->model->insert($insert);
        if (!$result){
            $this->error('生成失败');
        }
        $this->success('生成成功');
    }

    /*自动生成号码*/
    public function mkBall(){
        $red = '';
        $blue = '';
        for ($i=0;$i<5;$i++) {
            $red_rand = $this->red_rand(01,33,6);
            $blue_rand = $this->blue_rand();
            $red .= '<font color="red">'.implode(' ',$red_rand).'</font>'.' '.'<font color="blue">'.$blue_rand.'</font>'.'<br/>';
            $blue .= $blue_rand.'<br/>';
            $original_number[$i] = implode('+', $red_rand).'+'.$blue_rand;
        }
        $lottery_date = $this->get_week_arr(date('Y-m-d'));
        $insert = [
            'red_num'           =>  $red,
            'blue_num'          =>  $blue,
            'type'              =>  1,
            'createtime'        =>  time(),
            'updatetime'        =>  time(),
            'status'            =>  1,
            'number_periods'    =>  '',
            'lottery_date'      =>  $lottery_date,
            'original_number'   =>  json_encode($original_number),
        ];
        $result = $this->model->insert($insert);
        if (!$result){
            echo '生成号码：失败';
        }
        echo '生成号码：成功';
    }

    /*下单*/
    public function change_status($ids)
    {
        $result = $this->model->where('id',$ids)->update(['is_bet'=>1]);
        if (!$result)
            $this->eroor('失败');
        $this->success();
    }

    /*查询号码*/
    public function prize()
    {
        //获取未计算奖金的号码
        $list =
            collection(
            $this->model->where('prize_number','not null')->where('prize_money',null)->field('id,prize_number,original_number')->select()
        )->toarray();
        foreach ($list as $key => $value) {
            $list[$key]['original_number'] = json_decode($value['original_number'],true);
            $prize_money = '';//每次循环初始化奖金为空
            foreach ($list[$key]['original_number'] as $k => $v) {
                //处理开奖号码
                $prize_number = str_replace(' ','+',$value['prize_number']);//替换字符串中的,为+
                $Lottery_res = $this->countSsqLottery($v, $prize_number);
                $prize_money .= $Lottery_res['lottery_level'].':'.$Lottery_res['win_ammount'].'元'.'<br/>';
            }
            //将$prize_money更新到数据库
            $result = $this->model->where('id',$value['id'])->update(['prize_money'=>$prize_money]);
            if (!$result){
                echo '计算奖金：失败';
            }
            echo '计算奖金：成功';
        }
    }


    /**
     * Created by PhpStorm
     * 计算双色球中奖金额
     * @author: wt
     * Date : 2022/8/1
     * Time : 16:05
     * @param string $buy_str 购买结果，类似 4+15+20+25+26+30+8
     * @param string $open_result_str 开奖结果，类似 4+15+20+25+26+30+8
     * @return array
     */
    function countSsqLottery($buy_str, $open_result_str) {
        $result = [
            'win_ammount' => 0,//中奖金额
            'lottery_level' => ''//中奖等级描述
        ];
        $buy_list = explode('+', $buy_str);
        $buy_red_list = array_splice($buy_list, 0, 6);
        array_walk($buy_red_list, function (&$value) {$value = intval($value);     });
        $buy_blue_list = [intval(end($buy_list))];
        $open_result_list = explode('+', $open_result_str);
        $open_result_red_list = array_splice($open_result_list, 0, 6);
        array_walk($open_result_red_list, function (&$value) {$value = intval($value);     });
        $open_result_blue_list = [intval(end($open_result_list))];
        $red_lottery_num = count(array_intersect($buy_red_list, $open_result_red_list));
        $blue_lottery_num = count(array_intersect($buy_blue_list, $open_result_blue_list));
        if ($blue_lottery_num) {
            switch ($red_lottery_num) {
                case 0:
                case 1:
                case 2:
                    $result['win_ammount'] = 5;
                    $result['lottery_level'] = '六等奖';
                    break;
                case 3:
                    $result['win_ammount'] = 10;
                    $result['lottery_level'] = '五等奖';
                    break;
                case 4:
                    $result['win_ammount'] = 200;
                    $result['lottery_level'] = '四等奖';
                    break;
                case 5:
                    $result['win_ammount'] = 3000;
                    $result['lottery_level'] = '三等奖';
                    break;
                case 6:
                    $result['win_ammount'] = 5000000;
                    $result['lottery_level'] = '一等奖';
                    break;
            }
        } else {
            switch ($red_lottery_num) {
                case 0:
                case 1:
                case 2:
                case 3:
                    $result['win_ammount'] = 0;
                    $result['lottery_level'] = '六等奖';
                    break;
                case 4:
                    $result['win_ammount'] = 10;
                    $result['lottery_level'] = '五等奖';
                    break;
                case 5:
                    $result['win_ammount'] = 200;
                    $result['lottery_level'] = '四等奖';
                    break;
                case 6:
                    $result['win_ammount'] = 100000;
                    $result['lottery_level'] = '二等奖';
                    break;
            }
        }

        return $result;
    }

    /*获取python爬虫抓取的数据*/
    public function get_lottery_number()
    {
        $lottery_info = collection($this->model->where('prize_number','null')->whereOR('prize_number','')->select())->toarray();
        foreach ($lottery_info as $key => $value){
            $dir = ROOT_PATH.'ssq/'.$value['lottery_date']; //开奖号码路径
//            echo $dir.'<br/>';
            if (!is_dir($dir)){
                echo '还未开奖';
                continue;
            }
            $lottery = json_decode(file_get_contents($dir.'/ssq.txt'),true);//读取开奖信息
            if (!$lottery){
                echo '开奖号码不存在';
                continue;
            }
            $result = $this->model
                ->where('id',$value['id'])
                ->update([
                    'number_periods'=>$lottery['code'][0],
                    'prize_number'=>str_replace(',',' ',$lottery['number'][0]),//替换 ,为空格
                ]);
            if (!$result){
                echo '写入开奖号码失败';
                continue;
            }
            echo '写入开奖号码成功';
        }

    }

    /*红号*/
    function red_rand($min, $max, $num) {
        $count = 0;
        $return = array();
        while ($count < $num) {
            $red_num = mt_rand($min, $max);
            if (strlen($red_num) == 1)
                $red_num = '0'.$red_num;
            $return[] = $red_num;
            $return = array_flip(array_flip($return));
            $count = count($return);
        }
        shuffle($return);
        asort($return);
        return $return;
    }
    /*蓝号*/
    function blue_rand() {
        $blue_num = mt_rand(01,16);
        if (strlen($blue_num) == 1)
            $blue_num = '0'.$blue_num;
        return $blue_num;
    }

    /*生成开奖日期*/
    public function get_week_arr($today_time='')
    {
        //获取今天是周几，0为周日
        $this_week_num = date('w');
        $timestamp = time();
        //如果获取到的日期是周日，需要把时间戳换成上一周的时间戳
        //英语国家 一周的开始时间是周日
        if ($this_week_num == 0) {
            $timestamp = $timestamp - 86400;
        }

        $this_week_arr = [
            [
                'is_sign' => 0,
                'this_week' => 1,
                'week_name' => '星期一',
                'week_time' => strtotime(date('Y-m-d', strtotime("this week Monday", $timestamp))),
                'week_date' => date('Y-m-d', strtotime("this week Monday", $timestamp)),
            ],
            [
                'is_sign' => 0,
                'this_week' => 2,
                'week_name' => '星期二',
                'week_time' => strtotime(date('Y-m-d', strtotime("this week Tuesday", $timestamp))),
                'week_date' => date('Y-m-d', strtotime("this week Tuesday", $timestamp)),
            ],
            [
                'is_sign' => 0,
                'this_week' => 3,
                'week_name' => '星期三',
                'week_time' => strtotime(date('Y-m-d', strtotime("this week Wednesday", $timestamp))),
                'week_date' => date('Y-m-d', strtotime("this week Wednesday", $timestamp)),
            ],
            [
                'is_sign' => 0,
                'this_week' => 4,
                'week_name' => '星期四',
                'week_time' => strtotime(date('Y-m-d', strtotime("this week Thursday", $timestamp))),
                'week_date' => date('Y-m-d', strtotime("this week Thursday", $timestamp)),
            ],
            [
                'is_sign' => 0,
                'this_week' => 5,
                'week_name' => '星期五',
                'week_time' => strtotime(date('Y-m-d', strtotime("this week Friday", $timestamp))),
                'week_date' => date('Y-m-d', strtotime("this week Friday", $timestamp)),
            ],
            [
                'is_sign' => 0,
                'this_week' => 6,
                'week_name' => '星期六',
                'week_time' => strtotime(date('Y-m-d', strtotime("this week Saturday", $timestamp))),
                'week_date' => date('Y-m-d', strtotime("this week Saturday", $timestamp)),
            ],
            [
                'is_sign' => 0,
                'this_week' => 7,
                'week_name' => '星期天',
                'week_time' => strtotime(date('Y-m-d', strtotime("this week Sunday", $timestamp))),
                'week_date' => date('Y-m-d', strtotime("this week Sunday", $timestamp)),
            ],
        ];

        $week_date = '';
        if ($this_week_arr[0]['week_date']==$today_time || $this_week_arr[1]['week_date']==$today_time){
            $week_date = $this_week_arr[1]['week_date'];
        }
        if ($this_week_arr[2]['week_date']==$today_time || $this_week_arr[3]['week_date']==$today_time){
            $week_date = $this_week_arr[3]['week_date'];
        }
        if ($this_week_arr[4]['week_date']==$today_time || $this_week_arr[5]['week_date']==$today_time || $this_week_arr[6]['week_date']==$today_time){
            $week_date = $this_week_arr[6]['week_date'];
        }

        return $week_date;
    }

    public function demo()
    {
        $Url = "http://www.cwl.gov.cn/cwl_admin/front/cwlkj/search/kjxx/findDrawNotice?name=ssq&issueCount=1";
        $result = $this->postHttps($Url);
//        var_dump($result);die;
        file_put_contents('./curlssql.txt', json($result));
    }

    function postHttps($url)
    {
        //增加的User-Agent
        $headers['User-Agent'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36';
        $headerArr = array();
        foreach( $headers as $n => $v ) {
            $headerArr[] = $n .':' . $v;
        }
        //去除空格
        $urls = str_replace(' ', '+', $url);
        $ch = curl_init();
        //请求头json格式
        $header[] = "Content-type:application/json";
        curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        //设置SSL验证
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        //是否检测服务器的域名与证书上的是否一致
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        //设置链接
        curl_setopt($ch,CURLOPT_URL,$urls);
        //设置是否返回信息
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
        //post
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $postArr);
        //设置header
        curl_setopt ($ch,CURLOPT_HTTPHEADER,$headerArr);
        //是否出错
        if(!curl_exec($ch)){
            echo "CURL Error:".curl_error($ch);
        }
        //接收返回信息
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }



}
