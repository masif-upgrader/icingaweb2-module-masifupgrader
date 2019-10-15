<?php

namespace Icinga\Module\Masifupgrader\Controllers;

use Icinga\Module\Masifupgrader\Forms\Reset\DbForm;
use Icinga\Module\Masifupgrader\Web\DbAwareControllerTrait;
use Icinga\Web\Controller;
use Icinga\Web\Url;
use Icinga\Web\Widget\Tabs;

class ResetController extends Controller
{
    use DbAwareControllerTrait;

    public function indexAction()
    {
        $this->view->form = $form = new DbForm();
        $form->setDb($this->db)
            ->handleRequest();

        $this->view->tabs = (new Tabs)->add('reset', [
            'label'     => $this->translate('Reset'),
            'title'     => $this->translate('Reset database'),
            'icon'      => 'rewind',
            'url'       => Url::fromRequest(),
            'active'    => true
        ]);
    }
}
