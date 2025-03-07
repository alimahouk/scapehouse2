<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    function _initRouter()
    {
        // Don't touch --------
        $front = new Zend_Controller_Router_Rewrite();
        // $request = new Zend_Controller_Request_Http();
        $front = $front->getFrontController();
        $router = $front->getRouter();
        // $router->addDefaultRoutes();
        // -------------------------------------
        
        // /ali redirector to cover up for our links on current business cards.        
        /*$ali = new Zend_Controller_Router_Route("/ali", array("controller" => "index", "action" => "index"));
        $router->addRoute("ali", $ali);*/

        $scape = new Zend_Controller_Router_Route("/tipbox/tip/:id", array("module" => "tipbox", "controller" => "tip", "action" => "index"), array("id" => "\d+"));
        $router->addRoute("scape", $scape);
     
    }

    function _initAutoload()
    {
        $modelLoader = new Zend_Application_Module_Autoloader(
                        array(
                            "namespace" => "",
                            "basePath" => APPLICATION_PATH . "/modules/default"));

        $resourceLoader = new Zend_Loader_Autoloader_Resource(array(
                    'basePath' => APPLICATION_PATH . "/",
                    'namespace' => '',
                ));

        return $modelLoader;
    }

    function _initViewHelpers()
    {
        $front = new Zend_Controller_Router_Rewrite();
        $front = $front->getFrontController();
        $router = $front->getRouter();

        $this->bootstrap("layout");
        $layout = $this->getResource("layout");
    }

    protected function _initDb()
    {
        $this->bootstrap('multidb');
        $resource = $this->getPluginResource('multidb');
        $resource->init();

        Zend_Registry::set('tipbox', $resource->getDb('tipbox'));
        Zend_Registry::set('scapes', $resource->getDb('scapes'));
        Zend_Registry::set('theboard', $resource->getDb('theboard'));
    }
}

?>