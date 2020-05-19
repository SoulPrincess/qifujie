<?php
/**
 * Note:公共入口文件
 * User: LHP
 * Date: 2020/5/18
 * Time: 09:57
 */

namespace wechat\controllers;

use yii\web\Controller;
use wechat\models\UserModel;
use Yii;

class PublicController extends Controller
{
    public $user = [];//存储用户基本信息
    public $openid = '';
    public $session_key = '';
    public $is_login = 0;
    /**
     * @Notes:小程序登录入口
     * @param $action
     * @return bool
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     * @User:LHP
     * @Time: 2020/5/18
     */
    public function beforeAction($action)
    {
        $headers = Yii::$app->request->headers;
        $request = Yii::$app->request;
        if ($headers->has('X-Code')) {
            $params = Yii::$app->params['qd_wechat'];
            $_code = $headers->get('X-Code');
            $url='https://api.weixin.qq.com/sns/jscode2session?appid=' .$params['appid'] . '&secret=' . $params['secret']  . '&js_code=' . $_code . '&grant_type=authorization_code';
            $ret = file_get_contents($url);
            $ret = json_decode($ret, true);
            if (empty($ret['openid'])) {
                $this->_response([], -1, '授权失败');
            }
            $openId = $ret['openid'];
            $this->session_key = (isset($ret['session_key']) && !empty($ret['session_key'])) ? $ret['session_key'] : '';
        }else{
            $openId = $request->post('openid');
            //   if(empty($openId)){
            //     $this->_response([], 10001);
            // }
        }
            $this->openid = $openId;
            $user_model=new UserModel();
            $user_data=$user_model->getUserDetail(['openid'=>$this->openid]);
            if (!empty($user_data['id'])) {
                $this->user = $user_data;
                $this->is_login = 1;
            }

        $controllerName = Yii::$app->controller->id;
        $actionName = Yii::$app->controller->action->id;
     

        //需要登录才能用的一些功能
        $actionArr = [
            'companyenter'//服务商入驻
        ];
        if (in_array($actionName, $actionArr)) {
            if (!$this->is_login) {
                $this->_response([], 42013,'您还未登录，请先登录');
            }
        }
        return parent::beforeAction($action); // TODO: Change the autogenerated stub

    }
    /*获取小程序用户私密信息
     * @User:LHP
     * @Time: 2020/5/11
    * */
    public function actionGetuser(){
        if ($this->is_post()) {
            $request = Yii::$app->request;
            $save['openid'] = $this->openid;
            $save['session_key'] = $this->session_key;
            $save['nickName'] = $request->post('nickName');
            $save['gender'] =$request->post('gender');//性别 0：未知、1：男、2：女
            $save['city'] = $request->post('city');
            $save['province'] = $request->post('province');//省份
            $save['country'] = $request->post('country');//国家
            $save['avatarUrl'] = $request->post('avatarUrl');//用户头像
            $save['language'] = $request->post('language');//语言
            $user_model=new UserModel();
            $db = $user_model ->addUser($save);
            if($db !== false){
                $this->_response([], 200);
            }else{
                $this->_response([], 100);
            }
        } else {
            $this->_response([], '-1');
        }
    }

    /**
     * @Notes:小程序数据返回
     * @param string $msg
     * @param array $data
     * @param int $code
     * @User:lhp
     * @Time: 2020-5-13
     */
    public function _response($data = [], $code = 0, $msg = '', $option = JSON_UNESCAPED_UNICODE)
    {
        $data['openid'] = $this->openid;
        $data['is_login'] = $this->is_login;
        $data['user'] = $this->user;
        $result = array(
            'data' => $data,
            'code' => $code,
            'msg' => $msg ? $msg : Yii::$app->params['params'][$code]
        );
        die(json_encode($result, $option));
    }
    /**
     * @Notes:验证post请求
     * @return bool|mixed
     * @User:LHP
     * @Time: 2020/3/24
     */
    protected function is_post()
    {
        return Yii::$app->request->isPost;
    }

    //检验是否为空
    public function verifyEmpty($result = [], $key = '', $default = '')
    {
        if (!empty($result[$key])) {
            if (is_array($result[$key])) {
                return $result[$key];
            }
            return trim($result[$key]);
        }
        return $default;
    }

    /**
     * Notes:验证get请求
     * @return bool|mixed
     * @User:LHP
     * @Time: 2020/4/29
     */
    protected function is_get()
    {
        return Yii::$app->request->isGet;
    }
    /**
     * Notes:随机生成唯一的编号
     * @return bool|mixed
     * @User:LHP
     * @Time: 2020/4/29
     */
    protected function nonceStr()
    {
        static $seed = array(0,1,2,3,4,5,6,7,8,9);
        $str = date('mds');
        for($i=0;$i<8;$i++) {
            $rand = rand(0,count($seed)-1);
            $temp = $seed[$rand];
            $str .= $temp;
            unset($seed[$rand]);
            $seed = array_values($seed);
        }
        return $str;
    }
}