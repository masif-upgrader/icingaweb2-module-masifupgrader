<?php

namespace Icinga\Module\Masifupgrader\Forms;

use Icinga\Application\Icinga;
use Icinga\Forms\ConfigForm;

class IntegrationForm extends ConfigForm
{
    public function init()
    {
        $this->setName('form_config_integration');
        $this->setTitle($this->translate('Monitoring integration'));
        $this->setSubmitLabel($this->translate('Save changes'));
    }

    public function createElements(array $formData)
    {
        $this->addElement('checkbox', 'integration_monitoring', [
            'label'         => $this->translate('Monitoring module'),
            'description'   => $this->translate('Integrate into the monitoring module (IDO)')
        ]);

        if (!Icinga::app()->getModuleManager()->hasEnabled('monitoring')) {
            $this->getElement('integration_monitoring')->disabled = true;
        }
    }
}
