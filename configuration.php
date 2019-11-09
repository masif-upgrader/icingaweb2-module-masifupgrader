<?php

/** @var \Icinga\Application\Modules\Module $this */

$this->provideConfigTab('backend', [
    'url'   => 'config/backend',
    'label' => $this->translate('Backend'),
    'title' => $this->translate('Database backend')
]);

$this->provideConfigTab('integration', [
    'url'   => 'config/integration',
    'label' => $this->translate('Integration'),
    'title' => $this->translate('Monitoring integration')
]);

$section = $this->menuSection(N_('Masif Upgrader'), [
    'icon'  => 'reschedule'
]);

$section->add(N_('Pending updates'), [
    'icon'  => 'reschedule',
    'url'   => 'masifupgrader/pending'
]);

$section->add(N_('Reset'), [
    'icon'  => 'rewind',
    'url'   => 'masifupgrader/reset'
]);
