<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Authentication\AuthenticationService;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;

use Usuario\Form\Empresa as empresaForm;

class AjaxController extends BaseController
{
    public function cidadeAction()
    {   

        $params = $this->getRequest()->getPost();
        //instanciar form
        $formPessoaFisica = new empresaForm('formEmpresa', $this->getServiceLocator());
        $cidade = $formPessoaFisica->setCidadesByEstado($params->estado);
        
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array('cidades' => $cidade));
        return $view;
    }
}
