<?php

class LoginController extends BaseController
{
    public function beforeAction()
    {

    }

    public function login()
    {
        if (isset($_GET['redirect'])) {
            $_SESSION['frame']['HTTP']['redirect'] = $_GET['redirect'];
        }
    }

    public function process()
    {
        $this->render = false;

        $remember = (bool)(isset($_POST['remember']) && !empty($_POST['remember']));

        $redirect = '';
        if (isset($_SESSION['frame']['HTTP']['redirect'])) {
            $redirect = $_SESSION['frame']['HTTP']['redirect'];
            unset($_SESSION['frame']['HTTP']['redirect']);
        }

        if (Auth::processLogin($_POST['email'], $_POST['password'], $remember)) {
            if ($redirect) HTTP::redirect($redirect);
            else HTTP::rewind();
        } else {
            HTML::addAlert('Invalid Login Attempt', 'danger');
            HTTP::redirect('/login');
        }
    }

    public function loginRedirectFix()
    {
        $this->render = false;
        HTTP::redirect('/');
    }

    public function logout()
    {
        $this->render = false;
        Auth::logout();

        Http::redirect('/');
    }

    public function resetPassword()
    {
        $this->render_header = false;

    }

    public function resetPasswordProcess()
    {
        $this->render = false;

        if (empty($_POST['email'])) {
            HTML::addAlert('Email is a required field', 'danger');
            HTTP::rewindQuick();
        }

        /** @var User $user */
        $user = User::findOne(['email' => $_POST['email']]);
        if ($user) {

            $tokenString = bin2hex(random_bytes(16));

            $token = new ResetToken();
            $token->user_id = $user->user_id;
            $token->token = $tokenString;
            $token->created = date('Y-m-d H:i:s', time());
            $token->save();

            $mailer = new Mailer();
            $mailer->subject = 'E Squared Holdings | Password Recovery';
            $mailer->to = $user->email;

            $linkHref = $_ENV['BASE_PATH'] . '/login/password-recovery?token=' .$tokenString;

            $body = '<p>Hello ' . $user->first_name . ', </p>';
            $body .= '<p>Use the following link to reset your password: </p>';
            $body .= '<p><a href="' . $linkHref . '">Click here to reset your password</a></p>';
            $mailer->html = $body;

            if (!$mailer->send()) {
                HTML::addAlert('Error sending password recovery email.', 'danger');
                HTTP::redirect('/login/reset-password');
            }

        }

        HTML::addAlert('If your email exists in our system, you will receive an email with further instructions.');
        HTTP::redirect('/');
    }

    public function passwordRecovery()
    {
        $this->render_header = false;

        $tokenString = ($_GET['token']) ?? '';

        /** @var \ResetToken $token */
        $token = ResetToken::findOne(['token' => $tokenString]);

        $result = $this->checkResetToken($token);
        if ($result !== true) {
            HTML::addAlert($result, 'alert');
            HTTP::redirect('/login');
        }

        $_SESSION['login']['password']['token'] = $tokenString;
    }

    public function passwordRecoveryProcess()
    {
        $this->render = false;

        $tokenString = ($_SESSION['login']['password']['token']) ?? '';
        unset($_SESSION['login']['password']['token']);

        /** @var \ResetToken $token */
        $token = ResetToken::findOne(['token' => $tokenString]);

        $result = $this->checkResetToken($token, false);
        if ($result !== true) {
            HTML::addAlert($result, 'alert');
            HTTP::redirect('/login');
        }

        if (empty($_POST['password']) || empty($_POST['password_confirm'])) {
            HTML::addAlert('The password and password confirm fields must not be empty.', 'alert');
            HTTP::rewindQuick();
        }

        if ($_POST['password'] !== $_POST['password_confirm']) {
            HTML::addAlert('The passwords do not match.', 'alert');
            HTTP::rewindQuick();
        }

        /** @var User $user */
        $user = User::findOne(['user_id' => $token->user_id]);

        if (!$user) {
            HTML::addAlert('Invalid user');
            HTTP::redirect('/login');
        }

        $user->password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $user->save();

        $token->delete();

        HTML::addAlert('Your password has been updated', 'success');
        HTTP::redirect('/');
    }

    public function afterAction()
    {
        if ($this->render) {
            $layout = new AjaxLayout();
            $layout->action = $this->_action;
            $layout->addTemplate($this->view);
            $layout->display();
        }
    }

    private function checkResetToken($token, $checkExpire = true)
    {
        try {

            if (!$token) throw new Exception('Invalid Token');

            if ($checkExpire && time() > strtotime($token->created) + 3600) throw new Exception('The toke has expired. Password recovery tokens are good for 1 hour.');

            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

}
