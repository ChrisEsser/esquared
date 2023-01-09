<?php

class RenterController extends BaseController
{
    /** @var \User */
    private $user;

    public function beforeAction()
    {
        if (!Auth::loggedInUser()) HTTP::redirect('/login');

        /** @var User $user */
        $user = User::findOne(['user_id' => Auth::loggedInUser()]);
        if (!$user) {
            HTML::addAlert('Invalid User', 'danger');
            HTTP::redirect('/login');
        }

        $this->user = $user;

        $this->view->setVar('user', $user);
    }

    public function account()
    {
        $missing = [];
        if (isset($_SESSION['Form']['Account']['InvalidPost'])) {
            $this->user->first_name = $_SESSION['Form']['Account']['InvalidPost']['fields']['first_name'];
            $this->user->last_name = $_SESSION['Form']['Account']['InvalidPost']['fields']['last_name'];
            $this->user->email = $_SESSION['Form']['Account']['InvalidPost']['fields']['email'];
            $missing = $_SESSION['Form']['Account']['InvalidPost']['missing'];
            unset($_SESSION['Form']['Account']['InvalidPost']);
        }

        $this->view->setVar('missing', $missing);
    }

    public function saveAccount()
    {
        $this->render = false;

        $missing = [];
        if (empty($_POST['first_name'])) $missing[] = 'first_name';
        if (empty($_POST['last_name'])) $missing[] = 'last_name';
        if (empty($_POST['email'])) $missing[] = 'email';

        if (!empty($missing)) {
            $_SESSION['Form']['Account']['InvalidPost']['fields'] = $_POST;
            $_SESSION['Form']['Account']['InvalidPost']['missing'] = $missing;
            HTML::addAlert('One or more required fields were missing.', 'danger');
            HTTP::rewindQuick();
        }

        $this->user->first_name = $_POST['first_name'];
        $this->user->last_name = $_POST['last_name'];
        $this->user->email = $_POST['email'];
        $this->user->save();

        HTML::addAlert('Your account has been updated.', 'success');
        HTTP::rewindQuick();
    }

    public function savePassword()
    {
        $this->render = false;

        $return = [
            'result' => 'success',
            'message' => '',
        ];

        try {

            if (empty($_POST['password']) || empty($_POST['password_confirm'])) {
                throw new Exception('The password and password confirm fields must not be empty.');
            }

            if ($_POST['password'] !== $_POST['password_confirm']) {
                throw new Exception('The passwords do not match.');
            }

            $this->user->password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $this->user->save();

            HTML::addAlert('Your password has been updated', 'success');

        } catch (Exception $e) {
            $return = [
                'result' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        echo json_encode($return);
    }

    public function rentHistory()
    {
        $unit = $this->user->getUnit();
        if (!$unit) {
            HTTP::removePageFromHistory();
            HTML::addAlert('There are no rental units associated with your account.');
            HTTP::redirect('/');
        }

    }

    public function payRent()
    {
        HTTP::removePageFromHistory();
        $this->render_header = false;

        $rent = $this->user->getUnit()->rent;
        $rent = number_format($rent, 2) * 100;

        $cardTotal = ceil(($rent + 30) / (1 - 0.029));
        $cardFee = $cardTotal - $rent;

        $rent =  $this->user->getUnit()->rent;
        $cardTotal = round($cardTotal/100, 2);
        $cardFee = round($cardFee/100, 2);

        $this->view->setVar('rent', $rent);
        $this->view->setVar('cardTotal', $cardTotal);
        $this->view->setVar('cardFee', $cardFee);
    }

    public function payRentProcessCard()
    {
        $this->render = false;

        $missing = [];
        if (empty($_POST['card_name'])) $missing[] = 'card_name';
        if (empty($_POST['card_zip'])) $missing[] = 'card_zip';
        if (empty($_POST['stripeToken'])) $missing[] = 'stripeToken';

        if (!empty($missing)) {
            HTML::addAlert('Required fields were missing');
            HTTP::rewindQuick();
        }

        $stripe = new \Stripe\StripeClient($_ENV['STRIPE_SECRET']);

        // rent amount
        $rent = $this->user->getUnit()->rent;
        $rent = number_format($rent, 2) * 100;

        $total = ceil(($rent + 30) / (1 - 0.029));
        $fee = $total - $rent;

        try {
            $response = $stripe->charges->create([
                'amount'      => $total,
                'currency'    => 'usd',
                'source'      => $_POST['stripeToken'],
                'description' => 'E Squared Holdings | Rent Payment',
            ]);
        } catch (Exception $e) {
             HTML::addAlert('An error occurred processing payment. ' . $e->getMessage());
             HTTP::rewindQuick();
        }


        // generate a unique confirmation number
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $confirmationNumber = date('Ymd-', time()) . substr(str_shuffle($chars), 0, 5);

        $payment = new PaymentHistory();
        $payment->user_id = $this->user->user_id;
        $payment->unit_id = $this->user->getUnit()->unit_id;
        $payment->amount = $this->user->getUnit()->rent;
        $payment->fee = round($fee / 100, 2);
        $payment->method = 'Credit Card';
        $payment->type = 'Rent';
        $payment->description = 'Rent Payment - ' . date('m/d/y');
        $payment->payment_date = date('Y-m-d H:i:s', time());

        $payment->transaction_id = $response->id;
        $payment->confirmation_number = $confirmationNumber;
        $payment->save();

        HTTP::redirect('/confirmation/' . $confirmationNumber);

    }

    public function payRentConfirmation($params)
    {
        $confNum = ($params['confirmationNumber']) ?? '';
        $payment = PaymentHistory::findOne(['confirmation_number' => $confNum]);

        if (!$payment) throw new Exception404();

        $this->view->setVar('payment', $payment);
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
            $layout = new HomeLayout();
            $layout->action = $this->_action;
            $layout->user = $this->user;
            $layout->addTemplate($this->view);
            $layout->display();
        }
    }

}
