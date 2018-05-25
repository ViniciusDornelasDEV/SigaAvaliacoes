<?php
namespace Remuneracaomedica;

use Zend\Db\TableGateway\TableGateway;
use Application\Model\BaseTable;

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
                'MedicosSimulador' => function($sm) {
                    $tableGateway = new TableGateway('tb_medicos_simulador', $sm->get('db_adapter_main'));
                    $updates = new Model\Simulador($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                /*'AvaliacaoDiariaGrafico' => function($sm) {
                    $tableGateway = new TableGateway('tb_avaliacaodiaria_grafico', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },*/

            ),
            'invokables' => array(
                'ImageService' => 'Imagine\Gd\Imagine',
            ),
        );
    }
}
