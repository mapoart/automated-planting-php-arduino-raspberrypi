<?php

namespace Mapos\Web;

/**
 * Web class 
 *
 * @author   Marcin Polak <mapoart@gmail.com>
 */
class Web
{

    private $forms;
    private $fields;
    //if set no pre.php, post.php or similar will be loaded
    private $static = false;

    public function __construct($pageDir)
    {
        $this->pageDir = $pageDir;
    }

    public function run()
    {
        $vars_path = $this->pageDir . 'set/vars.php';
        if (file_exists($vars_path)) {
            require $vars_path;
        }

        if (isset($collection)) {
            $service->storage['collection'] = $collection;
        }

        //We load much faster
        if (isset($static) && $static) {
            $this->load('body.phtml');
            return true;
        }
    }

    private function getId()
    {
        $this->id = gu('id');
        $service = gi();
        $service->id = gu('id');

        if ($this->id) {
            try {
                $_id = new MongoId($service->id);
            } catch (MongoException $ex) {
                $_id = $service->id;
            }
            $service->storage['_id'] = $_id;

            if (!isset($db)) {
                $db = $service->get('DB'); //Later do return $this;lazy load, now is ok
            }

            $c = $db->selectCollection($collection);
            $service->db_elements = $c->findOne(array('_id' => $_id));
        }
    }

//    public function run()
//    {
//        //We load much faster
//        if (isset($this->static) && $this->static) {
//            $this->load('body.phtml');
//            return true;
//        }
//
//        $path = $this->pageDir;
//        if (!is_dir($path)) {
//            //Page folder does not exists, so we load 404.html page.
//            $this->setPageName('404.html');
//            //We load pre.php for 404 page
//            $path = $this->getFolder() . 'pre.php';
//            if (file_exists($path)) {
//                require_once ($path);
//            }
//        } else {
//            //Here we load global controller controller.
//            $this->load('../../controller.php');
//
//            extract($service->storage); //We update vars
//
//            if (file_exists($path . 'pre.php')) {
//                require $path . 'pre.php';
//            }
//        }
//    }
}
