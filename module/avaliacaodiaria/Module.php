<?php
namespace Avaliacaodiaria;

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
                'OperadorAvaliacaoDiaria' => function($sm) {
                    $tableGateway = new TableGateway('tb_avaliacaodiaria_operador', $sm->get('db_adapter_main'));
                    $updates = new Model\Operador($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'AvaliacaoDiaria' => function($sm) {
                    $tableGateway = new TableGateway('tb_avaliacaodiaria', $sm->get('db_adapter_main'));
                    $updates = new Model\Avaliacaodiaria($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'PilhaAvaliacaoDiaria' => function($sm) {
                    $tableGateway = new TableGateway('tb_pilha_avaliacoes_avaliacaodiaria', $sm->get('db_adapter_main'));
                    $updates = new Model\PilhaAvaliacao($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'AvaliacaoDiariaGrafico' => function($sm) {
                    $tableGateway = new TableGateway('tb_avaliacaodiaria_grafico', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'AvaliacaoDiariaGraficoSub' => function($sm) {
                    $tableGateway = new TableGateway('tb_avaliacaodiaria_grafico_sub', $sm->get('db_adapter_main'));
                    $updates = new Model\Graficopersonalizadosub($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
            ),
            'invokables' => array(
                'ImageService' => 'Imagine\Gd\Imagine',
            ),
        );
    }
}
