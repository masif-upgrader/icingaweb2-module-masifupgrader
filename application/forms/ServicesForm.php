<?php

namespace Icinga\Module\Masifupgrader\Forms;

use Icinga\Forms\ConfigForm;
use Icinga\Module\Monitoring\Backend\MonitoringBackend;
use PDO;

class ServicesForm extends ConfigForm
{
    public function init()
    {
        $this->setName('form_config_services');
        $this->setTitle($this->translate('Services'));
        $this->setSubmitLabel($this->translate('Save changes'));
    }

    public function createElements(array $formData)
    {
        /** @var PDO $pdo */
        $pdo = MonitoringBackend::instance()->getResource()->getDbAdapter()->getConnection();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare('SELECT DISTINCT name2 FROM icinga_objects WHERE objecttype_id = 2 ORDER BY name2');
        $stmt->execute();

        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $service) {
            $this->addElement('checkbox', "services_$service", [
                'label'         => $this->translate($service),
                'description'   => sprintf($this->translate('Integrate into services named "%s"'), $service)
            ]);
        }
    }
}
