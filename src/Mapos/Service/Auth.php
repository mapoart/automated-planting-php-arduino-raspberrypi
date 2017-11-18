<?php

namespace Mapos\Service;

use Mapos\Service\Service;
use Mapos\Service\ServiceException;

/**
 * Auth Service class 
 *
 * @author   Marcin Polak <mapoart@gmail.com>
 */
class Auth
{

    private $service;
    private $userIdSessionName = 'User_ID';

    public function __construct()
    {
        $this->service = Service::getInstance();
    }

    public function logout()
    {
        $loginModel = $this->service->get('Model', 'Login');
        $loginModel->save(array('type' => 'logout', 'date' => now(), 'user_id' => $this->service->storage['user']['_id'], 'email' => $this->service->storage['user']['email']));

        ss($this->userIdSessionName, '');

        unset($_SESSION[$this->userIdSessionName]);
    }

    private function login($id)
    {
        ss($this->userIdSessionName, $id);
    }

    public function get()
    {
        return $this; //We return this class
    }

    public function isGroup($group)
    {
        $s = Service::getInstance();
        $userGroup = $s->storage['user']['group'];
        if ($group != $userGroup) {
            return false;
        }
        return true;
    }

    public function checkGroup($group)
    {
        //for now just take service
        $this->checkLoggedIn();
        $s = Service::getInstance();
        $userGroup = $s->storage['user']['group'];
        if ($group != $userGroup) {
            sm("error:Musisz być w grupie '$group' żeby mieć dostęp do tej części serwisu.");
            if (gs('mapos_redirect')) {
                ss('mapos_redirect', null);
            }
            su('panel.html');
        }
    }

    public function checkLoggedIn($message = 'error:Musisz być zalogowany, żeby mieć dostęp do tej strony.')
    {
        if (!gs('User_ID')) {
            sm($message);
            ss('mapos_redirect', u());
            su('login.html');
        }
    }

    public function check($email, $password)
    {
        $db = $this->service->get('Model', 'User');
        $r = $db->findOne(array('email' => $email));
        if ($r && password_verify($password, $r['ppswd'])) {
            //We store login
            $loginModel = $this->service->get('Model', 'Login');
            $loginModel->save(array('type' => 'login', 'date' => now(), 'user_id' => $r['_id'], 'email' => $email));
            $r = (array) $r['_id'];
            $this->login($r['$id']);
            $this->service->get('Shoping')->attachUser();
            return true;
        }
    }

}
