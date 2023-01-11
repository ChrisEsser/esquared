<?php

class UserController extends BaseController
{
    public function beforeAction()
    {
        if (!Auth::isAdmin()) {
            HTML::addAlert('Unauthorized access');
            HTTP::redirect('/');
        }
    }

    public function users($params)
    {
        $users = User::find();
        $this->view->setVar('users', $users);
    }

    public function edit($params)
    {
        HTTP::removePageFromHistory();
        $this->render_header = false;

        $userId = ($params['userId']) ?? 0;

        $user = ($userId)
            ? User::findOne(['user_id' => $userId])
            : new User();

        $units = Unit::find();

        $this->view->setVar('user', $user);
        $this->view->setVar('units', $units);
    }

    public function save()
    {
        $this->render = false;

        $return = [
            'result' => 'success',
            'message' => '',
        ];

        $missing = [];

        try {

            $userId = ($_POST['user']) ?? 0;

            $user = ($userId)
                ? User::findOne(['user_id' => $userId])
                : new User();

            $newUser = empty($user->user_id);

            if (empty($_POST['first_name'])) $missing[] = 'first_name';
            if (empty($_POST['last_name'])) $missing[] = 'last_name';
            if (empty($_POST['email'])) $missing[] = 'email';

            if (!empty($missing)) throw new Exception('Some required fields were missing');

            if ((!empty($_POST['password']) || !empty($_POST['password_confirm'])) && $_POST['password'] != $_POST['password_confirm']) {
                throw new Exception('The passwords do not match');
            }

            $admin = intval($_POST['admin'] ?? 0);

            $user->first_name = $_POST['first_name'];
            $user->last_name = $_POST['last_name'];
            $user->email = $_POST['email'];
            $user->admin = $admin;
            $user->unit_id = intval($_POST['unit_id']);
            if (!empty($_POST['password'])) {
                $user->password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
            $user->save();

            // if this a new user, lets fire off a password rest email as kind of an invite into the system
            if ($newUser) {

                $tokenString = bin2hex(random_bytes(16));

                $token = new ResetToken();
                $token->user_id = $user->user_id;
                $token->token = $tokenString;
                $token->created = date('Y-m-d H:i:s', time());
                $token->save();

                $mailer = new Mailer();
                $mailer->subject = 'E Squared Holdings | Website Invitation';
                $mailer->to = $user->email;

                $linkHref = $_ENV['BASE_PATH'] . '/login/password-recovery?token=' .$tokenString;

                $body = '<p>Hello ' . $user->first_name . ', </p>';
                $body .= '<p>Use the following link to set your password: </p>';
                $body .= '<p><a href="' . $linkHref . '">Click here to reset your password</a></p>';
                $mailer->html = $body;

                if (!$mailer->send()) {
                    throw new Exception('The account was created but there was an error sending invitation email.');
                }

            }

        } catch (Exception $e) {
            $return = [
                'result' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        echo json_encode($return);
    }

    public function delete($params)
    {
        $this->render = false;

        $userId = ($params['userId']) ?? 0;

        $user = User::findOne(['user_id' => $userId]);
        $user->delete();

        HTTP::rewindQuick();
    }

    public function afterAction()
    {
        if (!$this->render_header) {
            $layout = new AjaxLayout();
            $layout->action = $this->_action;
            $layout->addTemplate($this->view);
            $layout->display();
        }
        else if ($this->render) {
            $layout = new PlainLayout();
            $layout->action = $this->_action;
            $layout->addTemplate($this->view);
            $layout->display();
        }
    }

}