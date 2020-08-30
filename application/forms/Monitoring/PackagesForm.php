<?php

namespace Icinga\Module\Masifupgrader\Forms\Monitoring;

use Icinga\Module\Masifupgrader\Forms\PackagesFormTrait;
use Icinga\Module\Masifupgrader\Web\DbAwareFormTrait;
use Icinga\Module\Masifupgrader\Web\InvertableCheckboxesTrait;
use Icinga\Web\Form;
use Zend_Form_Element_Checkbox;
use Zend_Form_Element_Submit;
use Zend_View_Interface;

class PackagesForm extends Form
{
    use DbAwareFormTrait;
    use InvertableCheckboxesTrait;
    use PackagesFormTrait;

    /**
     * @var string
     */
    protected $agent;

    /**
     * @var array
     */
    protected $tasks;

    /**
     * @return array
     */
    protected function getTasks()
    {
        if ($this->tasks === null) {
            $rawTasks = $this->fetchAll(
                <<<EOQ
SELECT (SELECT p.name FROM package p WHERE p.id=t.package) AS package, t.action, t.to_version
FROM task t
WHERE t.agent=(SELECT a.id FROM agent a WHERE a.name=?) AND t.approved=0
ORDER BY (SELECT p.name FROM package p WHERE p.id=t.package) ASC
EOQ
                ,
                [$this->agent]
            );

            $this->tasks = [];

            foreach ($rawTasks as list($package, $action, $toVersion)) {
                $this->tasks[$package][$action][$toVersion] = null;
            }
        }

        return $this->tasks;
    }

    public function init()
    {
        $this->setName('form_monitoring_packages');
        $this->setSubmitLabel($this->translate('Approve selection'));
    }

    public function createElements(array $formData)
    {
        foreach ($this->getTasks() as $package => $actions) {
            foreach ($actions as $action => $toVersions) {
                foreach ($toVersions as $toVersion => $_) {
                    $checkboxName = implode('_', [bin2hex($package), $action, bin2hex($toVersion)]);
                    $this->addElement(new Zend_Form_Element_Checkbox($checkboxName));
                }
            }
        }
    }

    public function render(Zend_View_Interface $view = null)
    {
        $this->create();

        foreach ($this->getElements() as $element) {
            if ($element instanceof Zend_Form_Element_Checkbox || $element instanceof Zend_Form_Element_Submit) {
                $element->setDecorators([
                    'Zend_Form_Decorator_ViewHelper' => $element->getDecorators()['Zend_Form_Decorator_ViewHelper']
                ]);
            }
        }

        if ($view === null) {
            $view = $this->getView();
        }

        $t1header1 = $this->translate('Package');
        $t1header2 = $this->translate('Action');
        $t1header3 = $this->translate('Target version');
        $invertTrigger = $this->translate('(invert selection)');

        $result = "<form id='{$this->getName()}' name='{$this->getName()}' enctype='{$this->getEncType()}' "
            . "method='{$this->getMethod()}' action='{$this->getAction()}' data-base-target='_self'>"
            . $this->getElement($this->getUidElementName())->render($view)
            . $this->getElement($this->getTokenElementName())->render($view)
            . "<table class='common-table invertable-checkboxes'><thead><tr><th>{$view->escape($t1header1)}</th>"
            . "<th>{$view->escape($t1header2)}</th>"
            . "<th colspan='2'>{$view->escape($t1header3)} <a href='#' class='invertable-checkboxes-trigger' onclick=\""
            . self::$invertableCheckboxesJS
            . "\">{$view->escape($invertTrigger)}</a></th></tr></thead><tbody>";

        $rows = [];
        $currentRow = 0;

        $actionLabels = [
            'install'   => $this->translate('Install'),
            'update'    => $this->translate('Update'),
            'configure' => $this->translate('Configure'),
            'remove'    => $this->translate('Remove'),
            'purge'     => $this->translate('Purge')
        ];

        $actionsOrder = array_flip(array_keys($actionLabels));

        foreach ($this->getTasks() as $package => $actions) {
            $packageRows = 0;
            $packageOnRow = $currentRow;

            uksort($actions, function($lhs, $rhs) use($actionsOrder) {
                return $actionsOrder[$lhs] - $actionsOrder[$rhs];
            });

            foreach ($actions as $action => $toVersions) {
                $actionOnRow = $currentRow;

                krsort($toVersions, SORT_NATURAL);

                foreach ($toVersions as $toVersion => $_) {
                    if ($toVersion === '') {
                        $toVersion = $this->translate('N/A');
                    }

                    $approve = $this->getElement(implode('_', [bin2hex($package), $action, bin2hex($toVersion)]));

                    $rows[] = [
                        null,
                        null,
                        "<td colspan='2' class='invertable-checkbox'>{$approve->render($view)}<label for='{$view->escape($approve->getId())}'>&emsp;{$view->escape($toVersion)}</label></td>"
                    ];

                    ++$currentRow;
                }

                $actionRows = count($toVersions);
                $packageRows += $actionRows;
                $rows[$actionOnRow][1] = "<td rowspan='$actionRows'>{$view->escape($actionLabels[$action])}</td>";
            }

            $rows[$packageOnRow][0] = "<td rowspan='$packageRows'>{$view->escape($package)}</td>";
        }

        foreach ($rows as $row) {
            $result .= '<tr>' . implode('', $row) . '</tr>';
        }

        return "$result<tr><td colspan='4'>{$this->getElement('btn_submit')->render($view)}</td></tr></tbody></table></form>";
    }

    public function onSuccess()
    {
        $taskFilter = [];

        foreach ($this->getTasks() as $package => $actions) {
            foreach ($actions as $action => $toVersions) {
                foreach ($toVersions as $toVersion => $_) {
                    /** @var Zend_Form_Element_Checkbox $checkbox */
                    $checkbox = $this->getElement(implode('_', [bin2hex($package), $action, bin2hex($toVersion)]));

                    if ($checkbox->isChecked()) {
                        $taskFilter[$package][$action][$toVersion] = null;
                    }
                }
            }
        }

        if (empty($taskFilter)) {
            return false;
        }

        list($packageFilters, $packageFilterParams) = $this->filterTasksByActions('t', 'p2', $taskFilter);

        $filter = "t.approved=0 AND t.agent=(SELECT a.id FROM agent a WHERE a.name=?) AND ($packageFilters)";
        $params = array_merge([$this->agent], $packageFilterParams);

        $this->transaction(function() use($filter, $params) {
            $this->execSql("UPDATE task t SET t.approved=1 WHERE $filter", $params);
        });

        return true;
    }

    /**
     * @param string $agent
     *
     * @return PackagesForm
     */
    public function setAgent($agent)
    {
        $this->agent = $agent;
        return $this;
    }
}
