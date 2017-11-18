<?php

namespace Mapos;

use Mapos\Service\Service;
use Mapos\Config\Config;
use Mapos\Web\View\View;

/**
 *
 * @author      Marcin Polak <mapoart@gmail.com>
 * @copyright   2014 Marcin Polak
 * @link        http://www.marcinpolak.eu/mapos.html
 * @version     1.0
 * @package     Mapos
 *
 */
class Mapos
{

    private $page;
    private $defaultController;
    private $controller;
    private $requestMethod;
//    private $baseDir;
    private $pageSuffix;
    private $baseUrl;
    private $storage;

//    private $loaded;

    public function __construct()
    {
        $this->service = gi();

        //$this->service->config = Config::load();
    }

    public function setRequestMethod($requestMethod)
    {
        $this->requestMethod = $requestMethod;
    }

    public function setDefaultController($controller)
    {
        $this->defaultController = $controller;
    }

    public function setPageSuffix($suffix)
    {
        $this->pageSuffix = $suffix;
        return $this;
    }

    public function setSegments($segments)
    {
        $this->service->segments = $segments;
    }

    public function getSegments()
    {
        return $this->service->segments;
    }

    public function setBaseUrl($url)
    {
        $this->baseUrl = $url;
        return $this;
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function getTemplatesFolder()
    {
        $folder = $this->getPagePath();
        $array = explode('/', $folder);
        $folder = array_slice($array, 0, sizeof($array) - 3);
        return implode('/', $folder) . '/';
    }

    public function getControllerFolder()
    {
        return $this->getTemplatesFolder() . 'controller/';
    }

    public function getParam($name, $params = array(), $default = null)
    {
        return isset($params[$name]) ? $params[$name] : $default;
    }

    public function isPost()
    {
        return $this->service->getRequestMethod() == 'POST';
    }

    public function getPagePath()
    {
        return $this->service->getPagePath();
    }

    public function prepare()
    {
        $segment0 = $this->getParam(0, $this->service->segments);

        //pages will be taken from _apps/_hydro/{$controller}/pages
        if (strpos($segment0, $this->pageSuffix) !== false) {
            //We loads page from default controller
            $this->page = $segment0;
            $this->controller = $this->defaultController;
        } else {
            $this->page = $this->getParam(1, $this->service->segments, 'index.html');
            $this->controller = $segment0 ? $segment0 : $this->defaultController;
            $this->service->segments = array_slice($this->service->segments, 2);

            //We associate rest params.
            $counter = 0;
            $r = array();

            for ($i = 0; $i < count($this->service->segments); $i = $i + 2):
                $ssegment = &$this->service->segments[$i + 1];
                $r[$this->service->segments[$i]] = isset($ssegment) ? $ssegment : null;
                $counter++;
            endfor;
        }

        define('CONTROLLER', $this->controller);


        //It was a temporary variable
        unset($segment0);
    }

    public function run()
    {
        $this->prepare();

        //We make routing for eg multilanguage
        if (isset($this->service->routing[$this->page])) {
            $this->page = $this->service->routing[$this->page];
        }

        $folder = $this->getTemplatesFolder() . '/pages/' . $this->page;


        if (is_dir($folder)) {
            define('PAGE', $this->page);
        } else {
            define('PAGE', '404.html');
        }

        $this->service->load('set/vars.php');

//We load much faster
        if (isset($static) && $static) {
            $this->service->load('body.phtml');
            return true;
        }

        $this->service->id = gu('id');

        $this->parseId();

        $this->service->page = $this->page;
        $this->service->segments = $this->service->segments;

        echo $this->service->load('../../controller.php');

        echo $this->service->load('pre.php');


        //Service->id changed so we load new data in
        //Later to make a function to do that more clever!!!!!
        //Below is because somebody set $service->id in controller or pre!!!
        $this->parseId();

        $this->service->page = $this->page;
        $this->service->segments = $this->service->segments;


        if ($this->isPost()) {

            $this->service->storage['isPost'] = true;
            $this->service->storage['mapos_base_url'] = $this->baseUrl;

            //$this->service->storage['service'] = $this->service;
            $this->service->storage();

            if (!$this->service->validation_errors) {
                $this->service->load('post.php');
            }
        }

        $this->service->storage['mapos_base_url'] = $this->baseUrl;



        if (!isset($this->service->storage['page_body'])) {
            //You can create eg:  $service->storage['page_body'] = $service->load('success.phtml', NULL, true);
            //it will load not body.phtml but success.html / just different content!
            $this->service->storage['page_body'] = $this->service->load('body.phtml', null, true);
        }


        //We assume that action is always on the second segment for a moment.

        if (strpos(gseg(2), 'action.') !== false) {
            $this->service->load(gseg(2) . '.php');
            $this->error('Action file ends here. Please use exit or redirect($pageName) in the ' . gseg(2) . ' file.');
            return true;
        }

        $view = new View();
        //$view->setCache(new Cache\CacheStrategyRedis());
        $layoutUrl = $this->getTemplatesFolder() . 'layout.phtml';
        echo $view->load($layoutUrl, $this->service->storage);
    }

    public function error($txt)
    {
        echo 'MAPOS INFORMATION: ' . $txt;
    }

    private function parseId()
    {
        if ($this->service->id) {
            try {
                $id = new \MongoId($this->service->id);
            } catch (\MongoException $ex) {
                $id = $this->service->id;
            }

            $this->service->storage['_id'] = $id; //MongoDB ID

            if (!isset($db)) {
                $db = $this->service->get('Model', $this->service->storage['model']);
            }
            //We get item for display 
            $this->service->db_elements = $db->findOne(array('_id' => $id));
        }
    }

    //
}
