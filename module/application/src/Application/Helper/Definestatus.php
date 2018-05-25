<?php

namespace Application\Helper;

use Zend\View\Helper\AbstractHelper;

class Definestatus extends AbstractHelper
{
    protected $count = 0;

    public function __invoke($status) {
		switch ($status) {
		    case 1:
		        return 'Não Implementado';
		        break;
		    case 2:
		        return 'Em Implantação';
		        break;
		    case 03:
		        return 'Funcionando Parcialmente';
		        break;
		    case 4:
		        return 'Funcionando Perfeitamente';
		        break;
		    case 05:
		        return 'Não se aplica';
		        break;
		}
		        
    }
}