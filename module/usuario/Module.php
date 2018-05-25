<?php
namespace Usuario;

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

    // Add this method:
    public function getServiceConfig() {
        return array(
            'factories' => array(
                /* My Tables  */
                'Usuario' => function($sm) {
                    $tableGateway = new TableGateway('tb_usuario', $sm->get('db_adapter_main'));
                    $updates = new Model\Usuario($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'UsuarioTipo' => function($sm) {
                    $tableGateway = new TableGateway('tb_usuario_tipo', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'UsuarioRecurso' => function($sm) {
                    $tableGateway = new TableGateway('tb_usuario_recurso', $sm->get('db_adapter_main'));
                    $updates = new Model\UsuarioRecurso($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Empresa' => function($sm) {
                    $tableGateway = new TableGateway('tb_empresa', $sm->get('db_adapter_main'));
                    $updates = new Model\Empresa($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'EmpresaArquivo' => function($sm) {
                    $tableGateway = new TableGateway('tb_empresa_arquivos', $sm->get('db_adapter_main'));
                    $updates = new Model\EmpresaArquivos($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'AbasUsuario' => function($sm) {
                    $tableGateway = new TableGateway('tb_abas_usuario', $sm->get('db_adapter_main'));
                    $updates = new Model\AbasUsuario($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Abas' => function($sm) {
                    $tableGateway = new TableGateway('tb_abas', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'AuxiliarEmpresas' => function($sm) {
                    $tableGateway = new TableGateway('tb_usuario_auxiliar_empresas', $sm->get('db_adapter_main'));
                    $updates = new Model\AuxiliarEmpresas($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Medico' => function($sm) {
                    $tableGateway = new TableGateway('tb_medicos', $sm->get('db_adapter_main'));
                    $updates = new Model\Medico($tableGateway);
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
