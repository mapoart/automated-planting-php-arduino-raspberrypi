<?php

namespace Mapos\Web\View;

use Mapos\Cache\CacheInterface;

/**
 * View class 
 *
 * @author   Marcin Polak <mapoart@gmail.com>
 */
class View
{

    private $oCache;

    public function __construct()
    {
        
    }

    public function setCache(CacheInterface $cache)
    {
        $this->oCache = $cache;
    }

    public function load($filename, $params = array())
    {
        if ($this->oCache) {
            //We assume this system still has one session?
            $md5 = md5($filename . serialize($params) . serialize($_SESSION));
            if ($content = $this->oCache->get($md5)) {
                echo $content;
                return;
            }
        }

        extract($params);
        ob_start();

        $start_file = str_replace('.phtml', '.start.phtml', $filename);
        if (file_exists($start_file)) {
            require $start_file;
        }

        if (file_exists($filename)) {
            require($filename);
        } else {
            //sm('error:Strona nie została znaleziona.');
            //su('404.html');
        }

        $end_file = str_replace('.phtml', '.end.phtml', $filename);
        if (file_exists($end_file)) {
            require $end_file;
        }

        $content = ob_get_contents();

        //remove unnecessary spaces.

        $content = $this->pack($this->sanitize_output($content));


        if ($this->oCache) {
            $this->oCache->set($md5, $content);
        }

        ob_end_clean();
        //disable sniffing
        //below is done by .htaccess file!
        //header("X-Content-Type-Options: nosniff");
        //disable external iframe loading
        //this also below is done by .htaccess file!!!
        //header('X-Frame-Options: SAMEORIGIN');
//        header("Content-Security-Policy: default-src 'self'; script-src 'self';"); // FF 23+ Chrome 25+ Safari 7+ Opera 19+
//        header("X-Content-Security-Policy: default-src 'self'; script-src 'self';"); // IE 10+
        // Adds the HTTP Strict Transport Security (HSTS) (remember it for 1 year)
        $isHttps = !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off';
        if ($isHttps) {
            header('Strict-Transport-Security: max-age=31536000'); // FF 4 Chrome 4.0.211 Opera 12
        }

        echo $content;
    }

    function pack($content)
    {
        $content = str_replace(array(chr(10), array(13)), '', $content);
        $match = preg_replace('/>\s+/', '>', $content);
        if ($match) {
            $content = $match;
            unset($match);
        }
        return $content;
    }

    function sanitize_output($buffer)
    {

        $search = array(
            '/\>[^\S ]+/s', // strip whitespaces after tags, except space
            '/[^\S ]+\</s', // strip whitespaces before tags, except space
            '/(\s)+/s'       // shorten multiple whitespace sequences
        );

        $replace = array(
            '>',
            '<',
            '\\1'
        );

        $buffer = preg_replace($search, $replace, $buffer);

        return $buffer;
    }

}

///var/www/shop/vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer to change chmod 0777!!  
        
//        $config = \HTMLPurifier_Config::createDefault();
//        $config->set('Cache.SerializerPath', MAPOS_BASE_PATH.APP_FOLDER.'cache/HTMLPurifier');
//        $config->set('Cache.DefinitionImpl', null); // TODO: remove this later!
        //$config->set('HTML.DefinitionID', 'test');
//        $config->set('HTML.DefinitionRev', 1);
//       $config->set('HTML.AllowedElements', array('html','head', 'body', 'style', 'div', 'p','a'));    
//
//    if ($def = $config->maybeGetRawHTMLDefinition()) {
//        $def->addElement('html', 'Block', 'Inline', 'Common', array());
//        $def->addElement('head', 'Block', 'Inline', 'Common', array());
//        $def->addElement('style', 'Block', 'Inline', 'Common', array());
//        $def->addElement('body', 'Block', 'Inline', 'Common', array());
//
//    }
        
        //$purifier = new \HTMLPurifier($config);
         
        //$content = $purifier->purify($content);