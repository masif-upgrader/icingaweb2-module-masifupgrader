<?php

namespace Icinga\Module\Masifupgrader\Controllers;

use Icinga\Application\Config;
use Icinga\Module\Masifupgrader\Forms\BackendForm;
use Icinga\Module\Masifupgrader\Forms\IntegrationForm;
use Icinga\Module\Masifupgrader\Forms\ServicesForm;
use Icinga\Web\Controller;

class ConfigController extends Controller
{
    public function backendAction()
    {
        $this->assertPermission('config/modules');

        $this->view->form = $form = new BackendForm();
        $form->setIniConfig(Config::module('masifupgrader'))
            ->handleRequest();

        $this->view->tabs = $this->Module()->getConfigTabs()->activate('backend');
    }

    public function integrationAction()
    {
        $this->assertPermission('config/modules');

        $cfg = Config::module('masifupgrader');

        $this->view->form1 = $form1 = new IntegrationForm();
        $form1->setIniConfig($cfg)
            ->handleRequest();

        if ($cfg->get('integration', 'monitoring', '0')) {
            $this->view->form2 = $form2 = new ServicesForm();
            $form2->setIniConfig($cfg)
                ->handleRequest();
        } else {
            $this->view->form2 = null;
        }

        $this->view->tabs = $this->Module()->getConfigTabs()->activate('integration');
    }
}
