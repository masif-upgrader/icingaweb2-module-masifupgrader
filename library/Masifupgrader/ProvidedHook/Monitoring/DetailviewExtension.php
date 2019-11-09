<?php

namespace Icinga\Module\Masifupgrader\ProvidedHook\Monitoring;

use Icinga\Application\Config;
use Icinga\Module\Masifupgrader\Forms\Monitoring\PackagesForm;
use Icinga\Module\Masifupgrader\Web\DbAwareControllerTrait;
use Icinga\Module\Monitoring\Hook\DetailviewExtensionHook;
use Icinga\Module\Monitoring\Object\MonitoredObject;
use Icinga\Module\Monitoring\Object\Service;

class DetailviewExtension extends DetailviewExtensionHook
{
    use DbAwareControllerTrait;

    public function getHtmlForObject(MonitoredObject $object)
    {
        if ($object instanceof Service
            && Config::module('masifupgrader')->get('integration', 'monitoring', '0')
            && Config::module('masifupgrader')->get('services', $object->getName(), '0')
        ) {
            $this->init();

            $view = $this->getView();

            $form = new PackagesForm();
            $form->setDb($this->db)->setAgent($object->getHost()->getName())->handleRequest();

            return '<h2>' . $view->escape(mt('masifupgrader', 'Masif Upgrader')) . '</h2>' . $form->render($view);
        }

        return '';
    }
}
