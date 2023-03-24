<?php

use Stripe\Customer;
use Stripe\Stripe;

class RenterController extends BaseController
{
    /** @var \User */
    private $user;
    private $paymentDetails;

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
        $paymentDetails = (!empty($user->payment_details))
            ? unserialize(base64_decode($user->payment_details))
            : [];

        $this->paymentDetails = $paymentDetails;

        $this->view->setVar('user', $user);
        $this->view->setVar('paymentDetails', $paymentDetails);
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
        HTML::downForMaintenance();
        exit;

        $unit = $this->user->getUnit();
        if (!$unit) {
            HTTP::removePageFromHistory();
            HTML::addAlert('There are no rental units associated with your account.');
            HTTP::redirect('/');
        }

        $this->view->setVar('unit', $unit);
    }

    public function payRent()
    {
        HTML::downForMaintenance();
        exit;

        HTTP::removePageFromHistory();
        $this->render_header = false;

        $rent = $this->user->getUnit()->rent;
        $rent = number_format($rent, 2) * 100;

        $achFee = round(min($rent * 0.008, 500));
        $achTotal = $achFee + $rent;

        $cardTotal = round($rent / (1 - 0.029));
        $cardFee = $cardTotal - $rent;

        $rent =  $this->user->getUnit()->rent;
        $achTotal = $achTotal/100;
        $cardTotal = $cardTotal/100;
        $cardFee = $cardFee/100;
        $achFee = $achFee/100;

        $this->view->setVar('rent', $rent);
        $this->view->setVar('cardTotal', $cardTotal);
        $this->view->setVar('achTotal', $achTotal);
        $this->view->setVar('cardFee', $cardFee);
        $this->view->setVar('achFee', $achFee);
    }

    public function payRentProcessCard()
    {
        HTML::downForMaintenance();
        exit;

        $this->render = false;

        $missing = [];
        if (empty($_POST['card_name'])) $missing[] = 'card_name';
        if (empty($_POST['card_zip'])) $missing[] = 'card_zip';
        if (empty($_POST['stripeToken'])) $missing[] = 'stripeToken';

        if (!empty($missing)) {
            HTML::addAlert('Required fields were missing', 'danger');
            HTTP::rewindQuick();
        }

        // rent amount
        $rent = $this->user->getUnit()->rent;
        $rent = number_format($rent, 2) * 100;

        $total = round($rent / (1 - 0.029));
        $fee = $total - $rent;

        try {

            $stripe = new \Stripe\StripeClient($_ENV['STRIPE_SECRET']);

            $charge = $stripe->charges->create([
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
        $payment->fee = $fee / 100;
        $payment->method = 'Credit Card';
        $payment->type = 'Rent';
        $payment->description = 'Rent Payment - ' . date('m/d/y');
        $payment->payment_date = gmdate('Y-m-d H:i:s', time());

        $payment->transaction_id = $charge->id;
        $payment->confirmation_number = $confirmationNumber;
        $payment->save();

        // send email notification
        $mailer = new Mailer();
        $mailer->to = $this->user->email;
        $mailer->from = 'info@esquaredholdings.com';
        $mailer->subject = 'E Squared Holdings | Payment Confirmation';

        $html = '<h2>Payment Confirmation</h2>';
        $html .= '<p>Thank you! Your payment was successful</p>';
        $html .= '<p><strong>$' . number_format($total/100, 2) . '</strong> -- E Squared Holdings, LLC | Rent PAyment</p>';
        $mailer->html = $html;
        $mailer->send();

        // send email notification
        if (!$this->sendPaymentConfirmationEmail($total)) {
            HTML::addAlert('The payment succeeded but an error occurred sending the confirmation email', 'danger');
        }
        $this->sendPaymentAdminNotificationEmail($total, 'Credit Card');

        HTTP::redirect('/confirmation/' . $confirmationNumber);
    }

    public function payRentProcessAch()
    {
        HTML::downForMaintenance();
        exit;

        $this->render = false;

        if ($this->paymentDetails['stripe_ach_verified'] != 2) {
            HTML::addAlert('Invalid Bank Details');
            HTTP::rewindQuick();
        }

        $stripe = new \Stripe\StripeClient($_ENV['STRIPE_SECRET']);

        $rent = $this->user->getUnit()->rent;
        $rent = number_format($rent, 2) * 100;

        $fee = round(min($rent * 0.008, 500));
        $total = $rent + $fee;

        try {

            $stripe = new \Stripe\StripeClient($_ENV['STRIPE_SECRET']);

            $charge = $stripe->charges->create([
                'amount' => $total,
                'currency' => 'usd',
                'customer' => $this->paymentDetails['stripe_customer_id'],
                'description' => 'E Squared Holdings | Rent Payment',
            ]);

        } catch (Exception $e) {
            HTML::addAlert($e->getMessage());
            HTTP::rewindQuick();
        }

        // generate a unique confirmation number
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $confirmationNumber = date('Ymd-', time()) . substr(str_shuffle($chars), 0, 5);

        $payment = new PaymentHistory();
        $payment->user_id = $this->user->user_id;
        $payment->unit_id = $this->user->getUnit()->unit_id;
        $payment->amount = $this->user->getUnit()->rent;
        $payment->fee = $fee / 100;
        $payment->method = 'ACH Transfer';
        $payment->type = 'Rent';
        $payment->description = 'Rent Payment - ' . date('m/d/y');
        $payment->payment_date = gmdate('Y-m-d H:i:s', time());

        $payment->transaction_id = $charge->id;
        $payment->confirmation_number = $confirmationNumber;
        $payment->save();

        // send email notification
        if (!$this->sendPaymentConfirmationEmail($total)) {
            HTML::addAlert('The payment succeeded but an error occurred sending the confirmation email', 'danger');
        }
        $this->sendPaymentAdminNotificationEmail($total, 'ACH');

        HTTP::redirect('/confirmation/' . $confirmationNumber);
    }

    public function payRentConfirmation($params)
    {
        HTML::downForMaintenance();
        exit;

        $confNum = ($params['confirmationNumber']) ?? '';
        $payment = PaymentHistory::findOne(['confirmation_number' => $confNum]);

        if (!$payment) throw new Exception404();

        $this->view->setVar('payment', $payment);
    }

    public function managePayment()
    {
        HTML::downForMaintenance();
        exit;

        HTTP::removePageFromHistory();
        $this->render_header = false;
    }

    public function achSetupProcess()
    {
        HTML::downForMaintenance();
        exit;

        $this->render = false;

        $return = [
            'result' => 'success',
            'message' => '',
        ];

        try {

            if (empty($_POST['ach_type'])) throw new Exception('Invalid Request: no type');

            \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET']);

            if ($_POST['ach_type'] == 'setup') {

                $missing = [];
                if (empty($_POST['account_name'])) $missing[] = 'account_name';
                if (empty($_POST['account_type'])) $missing[] = 'account_type';
                if (empty($_POST['account_number'])) $missing[] = 'account_number';
                if (empty($_POST['routing_number'])) $missing[] = 'routing_number';

                if (!empty($missing)) throw new Exception('Required fields were missing');

                if (empty($_POST['token'])) throw new Exception('Account token missing');

                $customer = \Stripe\Customer::create([
                    'description' => $_POST['account_name'],
                    'source' => $_POST['token'],
                ]);

                $paymentDetails = [
                    'stripe_customer_id' => $customer->id,
                    'stripe_default_source' => $customer->default_source,
                    'stripe_ach_verified' => 1,
                ];

                $this->user->payment_details = base64_encode(serialize($paymentDetails));
                $this->user->save();

            } else if ($_POST['ach_type'] == 'verify') {

                $deposit0 = round($_POST['deposit_0'] * 100);
                $deposit1 = round($_POST['deposit_1'] * 100);

                $account = \Stripe\Customer::retrieveSource(
                    $this->paymentDetails['stripe_customer_id'],
                    $this->paymentDetails['stripe_default_source']
                );

                $response = $account->verify([
                    'amounts' => [$deposit0, $deposit1]
                ]);

                if ($response['status'] != 'verified') {
                    throw new Exception('Unable to verify. Please check that the amounts are correct.');
                }

                $newPaymentDetails = $this->paymentDetails;
                $newPaymentDetails['stripe_ach_verified'] = 2;

                $this->user->payment_details = base64_encode(serialize($newPaymentDetails));
                $this->user->save();

            } else throw new Exception('Invalid Request: invalid type');

        } catch (Exception $e) {
            $return = [
                'result' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        echo json_encode($return);

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

    public function removeAch()
    {
        HTTP::removePageFromHistory();

        $this->user->payment_details = '';
        $this->user->save();

        HTTP::rewindQuick();
    }

    private function sendPaymentConfirmationEmail($total)
    {
        $hStyle = 'color: #414552; font-family: -apple-system,\'SF Pro Display\',\'Segoe UI\',Roboto,\'Helvetica Neue\',Ubuntu,sans-serif;font-size: 28px;line-height: 36px;font-weight: 700!important;';
        $pStyle = 'color: #414552!important;font-family: -apple-system,\'SF Pro Display\',\'SF Pro Text\',\'Helvetica\',sans-serif;font-weight: 400;font-size: 16px;line-height: 24px;';

        $mailer = new Mailer();
        $mailer->to = $this->user->email;
        $mailer->from = 'info@esquaredholdings.com';
        $mailer->subject = 'E Squared Holdings | Payment Confirmation';

        $html = '<h2 style="' . $hStyle . '">Payment Confirmation</h2>';
        $html .= '<p style="' . $pStyle . '">Thank you! Your payment was successful</p>';
        $html .= '<p style="' . $pStyle . '"><strong>$' . number_format($total/100, 2) . '</strong> -- E Squared Holdings, LLC | Rent Payment</p>';
        $mailer->html = $html;
        return $mailer->send();
    }

    private function sendPaymentAdminNotificationEmail($total, $type)
    {
        $hStyle = 'color: #414552; font-family: -apple-system,\'SF Pro Display\',\'Segoe UI\',Roboto,\'Helvetica Neue\',Ubuntu,sans-serif;font-size: 28px;line-height: 36px;font-weight: 700!important;';
        $pStyle = 'color: #414552!important;font-family: -apple-system,\'SF Pro Display\',\'SF Pro Text\',\'Helvetica\',sans-serif;font-weight: 400;font-size: 16px;line-height: 24px;';

        $mailer = new Mailer();
        $mailer->to = ['cody@esquaredholdings.com', 'chris@esquaredholdings.com'];
        $mailer->from = 'info@esquaredholdings.com';
        $mailer->subject = 'E Squared Holdings | Payment Notification';

        $html = '<h2 style="' . $hStyle . '">Payment Notification</h2>';
        $html .= '<p style="' . $pStyle . '">A payment was received from ' . $this->user->first_name . ' ' . $this->user->last_name . ' via ' . $type . '</p>';
        $html .= '<p style="' . $pStyle . '"><strong>$' . number_format($total/100, 2) . '</strong> - <a href="' . $_ENV['BASE_PATH'] . '/property/' . $this->user->getUnit()->getProperty()->property_id . '">' . $this->user->getUnit()->getProperty()->name . ' | ' . $this->user->getUnit()->name . '</a></p>';
        $mailer->html = $html;
        return $mailer->send();
    }

}
