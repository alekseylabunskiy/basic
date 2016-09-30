<?php
namespace app\modules\user\models;

use app\modules\user\models\User;
use yii\base\Model;
use Yii;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
    private $_user = false;

    public $email;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist',
                'targetClass' => '\app\modules\user\models\User',
                'filter' => ['status' => User::STATUS_ACTIVE],
                'message' => 'Пользователя с таким адресом не существует.'
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'email' => Yii::t('app', 'USER_EMAIL'),
        ];
    }
    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return boolean whether the email was send
     */
    public function sendEmail()
    {
        if ($user = $this->getUser()) {
            $user->generatePasswordResetToken();
            if ($user->save()) {
                return Yii::$app->mailer->compose(['text' => '@app/modules/user/mails/passwordReset'], ['user' => $user])
                    ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
                    ->setTo($this->email)
                    ->setSubject('Password reset for ' . Yii::$app->name)
                    ->send();
            }
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findOne([
                'email' => $this->email,
                'status' => User::STATUS_ACTIVE,
            ]);
        }
        return $this->_user;
    }
}