<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php')
);

return [
    'id' => 'wechat',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'language'=>'zh-CN',
    'timeZone'=>'Asia/Shanghai',
    'controllerNamespace' => 'wechat\controllers',
    'components' => [
        'user' => [
           'class' => yii\web\User::className(),
            'identityClass' => wechat\models\User::className(),
            'enableSession' => false,
        ],
        'log' => [//此项具体详细配置，请访问http://wiki.feehi.com/index.php?title=Yii2_log
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => yii\log\FileTarget::className(),//当触发levels配置的错误级别时，保存到日志文件
                    'levels' => ['error', 'warning'],
                    'logFile' => '@runtime/logs/'.date('Y/m/d') . '.log',
                ],
                [
                    'class' => yii\log\EmailTarget::className(),//当触发levels配置的错误级别时，发送到此些邮箱（请改成自己的邮箱）
                    'levels' => ['error', 'warning'],
                    'except' => [//以下配置，除了匹配数组中的分类信息都会触发邮件提醒（黑名单）
                        'yii\web\HttpException:404',
                        'yii\web\HttpException:403',
                        'yii\debug\Module::checkAccess',
                    ],
                    'message' => [
                        'to' => ['admin@feehi.com', 'liufee@126.com'],
                        'subject' => '来自 Feehi CMS wechat的新日志消息',
                    ],
                ],
            ],
        ],
        'cache' => [
            'class' => yii\caching\DummyCache::className(),
            'keyPrefix' => 'wechat',       // 唯一键前缀
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'site/index',
                'login' => 'site/login',
                'v1/login' => 'v1/site/login',
                'register' => 'site/register',
                'v1/register' => 'v1/site/register',
                'v1' => 'v1/site/index',
                [
                    'class' => yii\rest\UrlRule::className(),
                    'controller' => ['user', 'public','cities','company', 'paid','navigationbar','options'],//通过/users,/user/1,/paid/info访问
                    /*'extraPatterns' => ['GET search' => 'search'],*/
                ],
                [
                    'class' => yii\rest\UrlRule::className(),//v1版本路由，通过/v1/users,/v1/user/1,/v1/paid/info...访问
                    'controller' => ['v1/site', 'v1/user', 'v1/cities','v1/company', 'v1/paid','v1/navigationbar','v1/options'],
                ],
                '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
                '<version:v\d+>/<controller:\w+>/<action:\w+>'=>'<version>/<controller>/<action>',
            ],
        ],
        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'text/json' => 'yii\web\JsonParser',
            ],
            'enableCsrfValidation' => false,
            'enableCookieValidation' => false,
        ],
        'response' => [
            'as format' => [
                'class' => wechat\behaviors\ResponseFormatBehavior::className(),
                'defaultResponseFormat' => yii\web\Response::FORMAT_JSON
            ]
        ],
    ],
    'modules' => [
        'v1' => [
            'class' => wechat\modules\v1\Module::className(),
        ],
    ],
    'params' => $params,
];
