<?php

namespace common\models\user;

use Yii;

use dektrium\user\models\Token;

/**
 * ResendForm gets user email address and if user with given email is registered it sends new confirmation message
 * to him in case he did not validate his email.
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class FSMResendForm extends \dektrium\user\models\ResendForm {

    /**
     * Creates new confirmation token and sends it to the user.
     *
     * @return bool
     */
    public function resend() {
        if (!$this->validate()) {
            return false;
        }

        $user = $this->finder->findUserByEmail($this->email);

        if ($user instanceof FSMUser) {
            /** @var Token $token */
            $token = Yii::createObject([
                        'class' => Token::className(),
                        'user_id' => $user->id,
                        'type' => Token::TYPE_CONFIRMATION,
            ]);
            $token->save(false);
            $this->mailer->sendConfirmationMessage($user, $token);

            Yii::$app->session->setFlash(
                'info', Yii::t(
                    'user', 'A message has been sent to your email address. It contains a confirmation link that you must click to complete registration.'
                )
            );
        }

        return true;
    }

}
