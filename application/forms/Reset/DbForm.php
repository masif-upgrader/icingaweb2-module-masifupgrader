<?php

namespace Icinga\Module\Masifupgrader\Forms\Reset;

use Icinga\Module\Masifupgrader\Web\DbAwareFormTrait;
use Icinga\Web\Form;

class DbForm extends Form
{
    use DbAwareFormTrait;

    public function init()
    {
        $this->setName('form_reset_database');
        $this->setTitle($this->translate('Reset database'));
        $this->setSubmitLabel($this->translate('Reset'));
    }

    public function createElements(array $formData)
    {
        $tables = [
            'task'      => $this->translate('Tasks (%d)'),
            'package'   => $this->translate('Packages (%d)'),
            'agent'     => $this->translate('Agents (%d)')
        ];

        $items = [];

        $this->transaction(function() use($tables, &$items) {
            foreach ($tables as $table => $_) {
                $items[$table] = $this->fetchAll("SELECT COUNT(*) FROM $table")[0][0];
            }
        });

        foreach ($tables as $table => $label) {
            $this->addElement('checkbox', "{$table}s", ['label' => sprintf($label, $items[$table])]);
        }

        $this->getElement('tasks')->setOptions(['value' => '1', 'disabled' => true]);
    }

    public function onSuccess()
    {
        /** @var \Zend_Form_Element_Checkbox[] $elements */
        $elements = $this->getElements();

        $this->transaction(function() use($elements) {
            foreach (['task', 'package', 'agent'] as $table) {
                if ($elements["{$table}s"]->isChecked()) {
                    $this->execSql("DELETE FROM $table");
                }
            }
        });

        return true;
    }
}
