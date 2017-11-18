<?php

namespace Mapos\Service;

use Mapos\Service\Service;
use Mapos\Service\ServiceException;

/**
 * Auth Service class 
 *
 * @author   Marcin Polak <mapoart@gmail.com>
 */
class Mail
{

    private $to;
    private $subject;
    private $message;
    private $emailFrom;
    private $priority;
    private $service;

    public function __construct()
    {
        $this->service = Service::getInstance();
    }

    public function setTo($email)
    {
        $this->to = $email;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function setEmailFrom($emailFrom)
    {
        $this->emailFrom = $emailFrom;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    public function get()
    {
        return $this; //We return this class
    }

    public function clearEmailAddress($email)
    {
        return str_replace(array('@', 'http://', '/'), array('_at_', '', ''), $email);
    }

    public function send()
    {
//        $headers = "MIME-Version: 1.0" . "\r\n";
//
//        $headers .= "Content-type: text/html; charset=utf-8" . "\r\n";
//        $headers .= "Content-Transfer-Encoding: 8bit\r\n";
//        $headers .= "From: " . $this->emailFrom . "\r\n" .
//            "Reply-To: " . $this->emailFrom . "\r\n" .
//            "X-Mailer: PHP/" . phpversion();

        $headers = "Mime-Version: 1.0\n";
        $headers .= "Content-type: text/html; charset=utf-8" . "\r\n";
        $headers .= "From: " . EMAIL_FROM;

        mb_internal_encoding("UTF-8");

        //$headers = mb_encode_mimeheader($headers);

        $to = EMAIL_DEBUG ? EMAIL_DEBUG : $this->to;
        $subject = EMAIL_DEBUG ? ('to(' . $this->to . '):' . $this->subject) : $this->subject;

        $subject = $this->clearEmailAddress($subject);

        //$subject = "=?utf-8?B?" . base64_encode($subject) . "?=";

        $this->message = htmlspecialchars_decode($this->message, ENT_QUOTES);

        $this->storeDB(array(
            'to' => $to,
            'subject' => $subject,
            'message' => $this->message,
            'headers' => $headers
        ));

        if (mb_send_mail($to, $subject, $this->message, $headers)) {

            if (defined('INFO_EMAILS') && INFO_EMAILS) {
                //We send copies to selected emails
                $emails = explode(',', INFO_EMAILS);
                foreach ($emails as $to):
                    mb_send_mail($to, "KOPIA: " . $subject, $this->message, $headers);
                endforeach;
            }

            return true;
        }
    }

    public function storeDB($data)
    {
        $service = gi();
        $mail = $service->get('Model', 'Mail');
        $mail->save($data);
    }

}
