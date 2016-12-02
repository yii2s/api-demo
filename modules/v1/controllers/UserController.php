<?php

namespace app\modules\v1\controllers;

use Yii;
use yii\rest\Controller;
use app\modules\v1\models\User;
use yii\helpers\Json;
use yii\data\ArrayDataProvider;
use yii\data\ActiveDataProvider;
use yii\db\Query;

use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\web\ServerErrorHttpException;

use yii\helpers\ArrayHelper;
use yii\filters\Cors;

class UserController extends Controller
{
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
//        $behaviors['authenticator'] = [
//            'class' => HttpBearerAuth::className(),
//        ];

        return ArrayHelper::merge(
            [['class' => Cors::className(),],], $behaviors);
    }

    public function actionIndex()
    {
        $condition = Yii::$app->request->get();

        $query = (new Query())
            ->select(['user_id', 'phone', 'password', 'access_token'])
            ->where($condition);

        return new ActiveDataProvider([
            'query' => $query,
        ]);
    }

    /**
     * @api {get} /users/:id 获取用户
     * @apiName get-user
     * @apiGroup user
     * @apiVersion 1.0.0
     *
     * @apiParam (获取用户) {String} id 用户ID.
     *
     * @apiSuccess (获取用户_response) {Number} user_id 用户ID.
     * @apiSuccess (获取用户_response) {String} phone  手机号.
     * @apiSuccess (获取用户_response) {String} password  密码.
     * @apiSuccess (获取用户_response) {String} name  姓名.
     * @apiSuccess (获取用户_response) {String} nick_name  昵称.
     * @apiSuccess (获取用户_response) {String} email  电子邮箱.
     * @apiSuccess (获取用户_response) {Number} gender  性别.
     * @apiSuccess (获取用户_response) {Number} qq  QQ.
     * @apiSuccess (获取用户_response) {String} avatar  头像.
     * @apiSuccess (获取用户_response) {String} birthday  生日.
     * @apiSuccess (获取用户_response) {String} access_token  token.
     *
     */
    /**
     * @apiDefine user
     *
     * 用户
     */

    public function actionView($id)
    {
        $model = new User([
            'scenario' => 'view',
        ]);

        $condition = [
            'user_id' => $id
        ];

        $data = $model::find()
            ->select(['user_id', 'phone', 'password', 'name', 'nick_name', 'email', 'gender', 'qq', 'avatar'
                , 'birthday_android', 'birthday', 'access_token'])
            ->where($condition)
            ->one();

        return $data;
    }

    /**
     * @api {post} /users 注册用户
     * @apiName create-user
     * @apiGroup user
     * @apiVersion 1.0.0
     *
     * @apiParam (注册用户) {String} phone 手机号.
     * @apiParam (注册用户) {String} password 密码.
     *
     * @apiSuccess (注册用户_response) {Number} user_id 用户ID.
     * @apiSuccess (注册用户_response) {String} password  密码.
     * @apiSuccess (注册用户_response) {String} phone  手机号.
     * @apiSuccess (注册用户_response) {String} create_time  创建时间.
     * @apiSuccess (注册用户_response) {String} update_time  修改时间.
     * @apiSuccess (注册用户_response) {String} access_token  token.
     *
     */
    /**
     * @apiDefine user
     *
     * 用户
     */

    public function actionCreate()
    {
        $model = new User([
            'scenario' => 'create',
        ]);

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $model->validate();
        if ($model->hasErrors()) {
            throw new UnprocessableEntityHttpException(Json::encode($model->errors));
        }

        if (!$model->save(false)) {
            throw new ServerErrorHttpException('Failed to save the object for unknown reason.');
        }

        return $model;
    }

    /**
     * @api {put} /users/:id 修改用户信息
     * @apiName update-user
     * @apiGroup user
     * @apiVersion 1.0.0
     *
     * @apiParam (修改密码) {Number} id 用户ID.
     * @apiParam (修改密码) {String} scenario 场景,此处值=updatePassword.
     * @apiParam (修改密码) {String} phone 手机号.
     * @apiParam (修改密码) {String} password 密码.
     * @apiParam (修改个人信息) {Number} id Users unique ID.
     * @apiParam (修改个人信息) {Number} id Users unique ID.
     * @apiParam (修改个人信息) {Number} id Users unique ID.
     *
     * @apiSuccess (修改密码_response) {Number} user_id 用户ID.
     * @apiSuccess (修改密码_response) {String} access_token  token.
     * @apiSuccess (修改密码_response) {String} password  密码.
     *
     */
    /**
     * @apiDefine user
     *
     * 用户
     */

    public function actionUpdate($id)
    {
        $request = Yii::$app->request;
        $scenario = $request->getBodyParam('scenario');

        if (!$scenario || $id == null) {
            throw new UnprocessableEntityHttpException('参数不全');
        }


        $model = User::findOne($id);

        if ($model == null) {
            throw new NotFoundHttpException('用户不存在');
        }

        $model->setScenario($scenario);
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $model->validate();

        if ($model->hasErrors()) {
            throw new UnprocessableEntityHttpException(Json::encode($model->errors));
        }

        if ($model->update(false) === false && !$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        return $model;
    }
}
