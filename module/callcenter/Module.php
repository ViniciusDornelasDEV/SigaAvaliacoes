<?php
namespace Callcenter;

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
                'Operador' => function($sm) {
                    $tableGateway = new TableGateway('tb_callcenter_operador', $sm->get('db_adapter_main'));
                    $updates = new Model\Operador($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'AvaliacaoCallCenter' => function($sm) {
                    $tableGateway = new TableGateway('tb_callcenter', $sm->get('db_adapter_main'));
                    $updates = new Model\Avaliacaocallcenter($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'PilhaAvaliacaoCallCenter' => function($sm) {
                    $tableGateway = new TableGateway('tb_pilha_avaliacoes_callcenter', $sm->get('db_adapter_main'));
                    $updates = new Model\PilhaAvaliacao($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'CallcenterGrafico' => function($sm) {
                    $tableGateway = new TableGateway('tb_callcenter_grafico', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'CallcenterGraficoSub' => function($sm) {
                    $tableGateway = new TableGateway('tb_callcenter_grafico_sub', $sm->get('db_adapter_main'));
                    $updates = new Model\Graficopersonalizadosub($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'MetaAgendamento' => function($sm) {
                    $tableGateway = new TableGateway('tb_callcenter_meta_agendamento', $sm->get('db_adapter_main'));
                    $updates = new Model\Meta($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'MetaAgendamentoMensal' => function($sm) {
                    $tableGateway = new TableGateway('tb_callcenter_meta_agendamento_mensal', $sm->get('db_adapter_main'));
                    $updates = new Model\MetaMensal($tableGateway);
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
