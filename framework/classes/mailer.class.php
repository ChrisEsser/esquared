<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {

    public $mailer;
    public $to;
    public $from;
    public $subject;
    public $html;
    public $text;
    public $cc;
    public $bcc;
    public $attachments;
    public $error;

    public function __construct()
    {
        $this->mailer = new PHPMailer();
        $this->setSMTP();
    }

    private function setSMTP()
    {
        if ($_ENV['MAIL_HOST']) {

            $this->mailer->isSMTP();
            $this->mailer->Host = $_ENV['MAIL_HOST'];
            $this->mailer->Port = $_ENV['MAIL_PORT'];
            $this->mailer->Username = $_ENV['MAIL_USER'];
            $this->mailer->Password = $_ENV['MAIL_PASSWORD'];

            $this->mailer->SMTPDebug = 0;
            $this->mailer->Debugoutput = 'html';
            $this->mailer->SMTPSecure = 'tls';
            $this->mailer->SMTPAuth = true;
            $this->mailer->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
        }
    }

    public function send()
    {
        try {

            if (!empty($this->from)) {
                $this->mailer->setFrom($this->from);
            } else {
                $this->mailer->setFrom($_ENV['MAIL_FROM'], 'E Squared Holdings, LLC');
            }

            if ($_ENV['DEVELOPMENT_ENVIRONMENT'] == 'false') {

                if (is_array($this->to)) {
                    foreach ($this->to as $address) {
                        $this->mailer->addAddress($address);
                    }
                } else if (is_string($this->to)) {
                    $this->mailer->addAddress($this->to);
                }

            } else {

                // if its dev enviro just send the email to me for testing
                $this->mailer->addAddress('chris@esquaredholdings.com');

            }

            if (!empty($this->cc)) {
                if (is_array($this->cc)) {
                    foreach ($this->cc as $address) {
                        $this->mailer->addCC($address);
                    }
                } else if (is_string($this->cc)) {
                    $this->mailer->addCC($this->cc);
                }
            }

            if (!empty($this->bcc)) {
                if (is_array($this->bcc)) {
                    foreach ($this->bcc as $address) {
                        $this->mailer->addBCC($address);
                    }
                } else if (is_string($this->bcc)) {
                    $this->mailer->addBCC($this->bcc);
                }
            }

            $this->mailer->isHTML(true);
            $this->mailer->Subject = $this->subject;
            $this->mailer->Body = $this->html;

            if (!empty($this->text)) {
                 $this->mailer->AltBody = $this->text;
            }

            return (!$this->mailer->send()) ? false : true;

        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function addAttachment($path)
    {
        try {
            return $this->mailer->addAttachment($path);
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

}