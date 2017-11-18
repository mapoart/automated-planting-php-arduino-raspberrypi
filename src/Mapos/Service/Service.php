<?php

namespace Mapos\Service;

/**
 * Service class 
 *
 * @author   Marcin Polak <mapoart@gmail.com>
 */
class Service
{

//    private $db;
    public $config = array(); // to keep the config
    public $id;
    public $validation_errors = '';
    public $storage = array();
    public $db_elements = array();
    public $file_uploads = array();
    public $segments = array();
    public $routing;

    /**
     * keep instance of the singleton class
     *
     * @var object
     * @access private
     */
    private static $oInstance = false;

    /**
     * keep services in array
     *
     * @var object
     * @access private
     */
    public $aServices;

    /**
     * keep executed/cached services in array
     *
     * @var object
     * @access private
     */
    private $aRunServices;

    public static function getInstance()
    {
        if (self::$oInstance == false) {
            self::$oInstance = new Service();
        }
        return self::$oInstance;
    }

    private function __construct()
    {
        //This takes a lot of time according to xhprof, so we make it once at service start.
        $this->currencyFormatter = new \NumberFormatter('pl_PL', \NumberFormatter::CURRENCY);
    }

    public function getRequestMethod()
    {
        //return filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_UNSAFE_RAW, FILTER_NULL_ON_FAILURE);
        $method = filter_var($_SERVER['REQUEST_METHOD']);
        return $method === 'POST' ? 'POST' : $method;
    }

    public function get($sName, $params = array())
    {
        $className = $sName;

        if (!is_object($params)) {
            if (is_array($params)) {
                $sName = implode(',', $params) . $sName;
            } else {
                $sName = $params . $sName;
            }

            if ($className !== 'Model' && isset($this->aRunServices[$sName])) {
                //We do not cache model class/
                //Its not prepared for that
                return $this->aRunServices[$sName];
            }
        }

        if (isset($this->aServices[$className])) {
            //somebody overwrite file based service
            //in services.php
            $serviceRun = $this->aServices[$className]($params);
            $this->aRunServices[$sName] = $serviceRun;
            return $serviceRun;
        }
        //If service has not been added in the service classes
        //Then
        $serviceClass = "\\Mapos\\Service\\" . $className;
        $service = new $serviceClass($params);
        $serviceRun = $service->get();
        $this->aRunServices[$sName] = $serviceRun;
        return $serviceRun;
    }

    /**
     * @param string $sKey Name of the key.
     * 
     * @param closure $function
     * 
     * 
     * @return anything   
     */
    public function add($sName, $function = null)
    {
        $this->aServices[$sName] = $function;
        return $this;
    }

    /**
     * @param string $sKey Name of the service to check if exists/ is loaded
     * 
     * @return anything DEPRACATED!!! REMOVE LATER!!
     */
    public function exists($sName)
    {
        return isset($this->aServices[$sName]);
    }

    public function o($name, $params = array(), $return = true)
    {
        $service = self::getInstance();
        extract(array_merge($params, $service->storage));
        $basePath = MAPOS_BASE_PATH . '_apps/_' . SITE_FOLDER . '/';

        $path = $basePath . CONTROLLER . '/pages/' . PAGE . '/' . $name . '.phtml';

        if (!file_exists($path)) {
            $path = $basePath . '/_objects/' . $name . '.phtml';
        }

        if (!$return) {
            include $path;
        } else {
            ob_start();
            include $path;
            $object = ob_get_contents();
            ob_end_clean();
            return $object;
        }
    }

    public function loadHelper($name)
    {
        $helperPath = 'src/Mapos/Helpers/';
        if (!isset($this->loaded['helpers'][$name])) {
            $this->loaded['helpers'][$name] = true;
            require MAPOS_BASE_PATH . $helperPath . $name;
            return $this;
        }
    }

    public function save()
    {
        $db = $this->get('Model', $this->storage['collection']);

        if ($this->id) {
            $db->update($his->id, $this->store_db);
        } else {
            $db->save($this->store_db);
        }
    }

    public function storage()
    {
        //We validate and save attributes from body.phtml
        $this->load('body.phtml');

        if (isset($this->fields)) {
            //Server validation goes here
            //var_dump($this->fields);
            foreach ($this->fields as $req => $v) {
                if ($v['field_type'] === 'paragraph') {
                    //ad-hoc :P
                    continue;
                }

                $validator = (new \Mapos\Validator\Validator(gp($req)))
                        ->setFieldName(@$v['label'])->setParams(@$v['params'])->setFromCssClasses(@$v['css_classes']);
                if (!$validator->validate()) {
                    $error = $validator->getHTMLErrors();
                    $this->storage[$v['id'] . '_error'] = $error;
                    $this->validation_errors.= $error;
                }
//                var_dump($v['store_db_field']   );
//                exit;
            }

            //var_dump($this->validation_errors);
        }

        if (isset($this->fields) && $this->fields) {

            foreach ($this->fields as $req => $v) {
                if ($v['field_type'] == 'file') {
                    //file is done after the id is created
                    $this->uploadsReady[$req] = $v;
                    continue;
                }
                if (isset($v['store_db'])) {

                    $fieldClass = "\\Mapos\\Web\\Form\\Field" . ucfirst($v['field_type']);
                    $class = (new $fieldClass($v))->save(); //We storage values by field.
                }
                if (isset($v['store_db_field'])) {
                    $this->store_db[$v['store_db_field']] = v($v['value']);
                }
            }
        }

        if (!$this->validation_errors && isset($this->store_db)) {
//                $updated_fields = array();Mapos/
//                $updated_fields = $model_name->Cmp_result($this->storage, $this->id);
            $updated_fields = 1; //later to implement
            if ($updated_fields):
                //if $updated  fields has been attach then there was an update of the form.
                // Cmp_result showing just updated records.

                if (!isset($this->storage['model'])) {
                    throw new \RuntimeException('Set $model variable at pageName.html/set/var.php or pre.php');
                }

                if (!isset($db)) {
                    $db = $this->get('Model', $this->storage['model']);
                }

                if ($this->id) {
                    try {
                        $_id = new \MongoId($this->id);
                    } catch (\MongoException $ex) {
                        $_id = $this->id;
                    }
                }

                if ($this->id) {
                    $this->store_db['updated_by'] = gs('User_ID');
                    $this->store_db['updated_date'] = now();
                    if ($_id instanceof MongoId) {
                        $status = $db->update($_id, $this->store_db);
                    } else {
                        //we update _id field so we need to add and remove old document.



                        $db->addWhere('_id', $_id);
                        $oldData = $db->findOne();
                        $this->store_db = array_merge($oldData, $this->store_db);
                        $this->store_db['updated_by'] = gs('User_ID');
                        $this->store_db['updated_date'] = now();
                        $db->save($this->store_db);
                        //We do not want to delete the same record!!!!
                        if ($_id != $this->store_db['_id']) {
                            $db->delete($_id);
                        }
                    }

                    //sm("ok:Record '$this->id' has been updated.");
                } else {
                    $this->store_db['updated_by'] = gs('User_ID');
                    $this->store_db['updated_date'] = now();
                    $this->store_db['added_by'] = gs('User_ID');
                    $this->store_db['added_date'] = now();

                    if (!isset($this->store_db['_id']) && gp('_id')) {
                        $this->store_db['_id'] = gp('_id');
                    }

                    if (!$this->id = $db->save($this->store_db)) {
                        sm("error:Record has NOT been inserted. Database error.");
                    }
                }

                //Uploads can be done also after save and get id, so we do that here.
                if (isset($this->uploadsReady)) {
                    foreach ($this->uploadsReady as $req => $v) {
                        if (isset($v['store_db'])) {
                            $fieldClass = "\\Mapos\\Web\\Form\\Field" . ucfirst($v['field_type']);
                            $class = (new $fieldClass($v))->save(); //We storage values by field.
                            $data = array($v['id'] => $this->store_db[$v['id']]);
                            $status = $db->update($this->id, $data);
                        }
                    }
                }

                if (function_exists('event_success')):
                    event_success($this->id);
                endif;
            else:
                mpLog('normal', 'No need to update database. No db fields has changed.');
            endif;

            if (isset($validation_errors) && $validation_errors):
                if (isset($_POST)):
                    $this->db_elements = $_POST; //We copy all forms elements, so they will display
                endif;
            endif;
        }else {
            if (isset($_POST)):
                $this->db_elements = $_POST; //We copy all forms elements, so they will display
            endif;
        }
    }

    public function getPagePath()
    {
        $page = '/';
        if (defined('PAGE')) {
            $page = PAGE . '/';
        }
        return MAPOS_BASE_PATH . APP_FOLDER . CONTROLLER . '/pages/' . $page;
    }

    public function isPost()
    {
        return $this->getRequestMethod() == 'POST';
    }

    public function load($file, $folder = null, $return = true)
    {
        if (!$folder) {
            $folder = $this->getPagePath();
            // echo $folder.'<BR />';
        }


        $path = $folder . $file;

        if (file_exists($path)) {
            //opcache_reset();
            //opcache_compile_file($path);//Cacheing the files
            //var_dump(opcache_get_status());
            //TODO: LATER TO THINK ABOUT ONE EXTRACTION?
            //NO TIME FOR NOW
//            $extension = pathinfo($path,PATHINFO_EXTENSION);
//            
//            if($extension =='phtml'){
//                
//            }

            extract(array('service' => $this));

            if ($return) {

                ob_start();
                extract($this->storage);
                require $path;
                $c = ob_get_contents();
                ob_end_clean();
                return $c;
            } else {
                extract($this->storage);
                require $path;
            }
            return true;
        } else {
            //$this->error('<b style=color:red>' . $folder . '</b><b style=color:blue>' . $file . '</b> not found');
        }
    }

    //Call method $name and return the instance from factory.
    //Class Factory must be created.
    public function gi($name)
    {
        $name = explode('.', $name);
        //var_dump($name);
        $package = $name[0];
        $name = $name[1];
        $ucPackage = ucfirst($package);
        $factoryName = "Mapos\\" . $ucPackage . '\\Factory';
        $f = new $factoryName();
        return $f->gi($name);
    }

}
