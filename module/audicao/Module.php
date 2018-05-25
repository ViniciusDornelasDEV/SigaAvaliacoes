<?php
namespace Audicao;

use Zend\Db\TableGateway\TableGateway;
use Application\Model\BaseTable;
use Zend\Mvc\MvcEvent;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig() {
        return array(
            'factories' => array(
                /* My Tables  */
                
            ),
            'invokables' => array(
                'ImageService' => 'Imagine\Gd\Imagine',
            ),
        );
    }
}
?>