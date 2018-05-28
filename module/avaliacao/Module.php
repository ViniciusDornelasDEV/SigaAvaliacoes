<?php
namespace Avaliacao;

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
                'Status' => function($sm) {
                    $tableGateway = new TableGateway('tb_status', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Agendamento' => function($sm) {
                    $tableGateway = new TableGateway('tb_agendamento', $sm->get('db_adapter_main'));
                    $updates = new Model\Agendamento($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Comercial' => function($sm) {
                    $tableGateway = new TableGateway('tb_comercial', $sm->get('db_adapter_main'));
                    $updates = new Model\Comercial($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Processo' => function($sm) {
                    $tableGateway = new TableGateway('tb_processo', $sm->get('db_adapter_main'));
                    $updates = new Model\Processo($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Qualidade' => function($sm) {
                    $tableGateway = new TableGateway('tb_qualidade', $sm->get('db_adapter_main'));
                    $updates = new Model\Qualidade($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Seguranca' => function($sm) {
                    $tableGateway = new TableGateway('tb_seguranca', $sm->get('db_adapter_main'));
                    $updates = new Model\Seguranca($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Ata' => function($sm) {
                    $tableGateway = new TableGateway('tb_ata', $sm->get('db_adapter_main'));
                    $updates = new Model\Ata($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Campo' => function($sm) {
                    $tableGateway = new TableGateway('tb_campo', $sm->get('db_adapter_main'));
                    $updates = new Model\Campo($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'CampoEmpresa' => function($sm) {
                    $tableGateway = new TableGateway('tb_campo_empresa', $sm->get('db_adapter_main'));
                    $updates = new Model\Campoempresa($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'CampoCategoria' => function($sm) {
                    $tableGateway = new TableGateway('tb_campo_categoria', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Aba' => function($sm) {
                    $tableGateway = new TableGateway('tb_abas', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'AvaliacaoQualitativa' => function($sm) {
                    $tableGateway = new TableGateway('tb_medico_avaliacao_qualitativa', $sm->get('db_adapter_main'));
                    $updates = new Model\Avaliacaoqualitativa($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'AvaliacaoPid' => function($sm) {
                    $tableGateway = new TableGateway('tb_medico_avaliacao_pid', $sm->get('db_adapter_main'));
                    $updates = new Model\Avaliacaopid($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'CampoMedicoOpcoes' => function($sm) {
                    $tableGateway = new TableGateway('tb_campo_medico_opcoes', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },

                'Agendamento2' => function($sm) {
                    $tableGateway = new TableGateway('tb_agendamento_2018', $sm->get('db_adapter_main'));
                    $updates = new Model\Agendamento($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Comercial2' => function($sm) {
                    $tableGateway = new TableGateway('tb_comercial_2018', $sm->get('db_adapter_main'));
                    $updates = new Model\Comercial($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Processo2' => function($sm) {
                    $tableGateway = new TableGateway('tb_processo_2018', $sm->get('db_adapter_main'));
                    $updates = new Model\Processo($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Qualidade2' => function($sm) {
                    $tableGateway = new TableGateway('tb_qualidade_2018', $sm->get('db_adapter_main'));
                    $updates = new Model\Qualidade($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Seguranca2' => function($sm) {
                    $tableGateway = new TableGateway('tb_seguranca_2018', $sm->get('db_adapter_main'));
                    $updates = new Model\Seguranca($tableGateway);
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