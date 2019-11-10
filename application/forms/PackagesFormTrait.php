<?php

namespace Icinga\Module\Masifupgrader\Forms;

trait PackagesFormTrait
{
    /**
     * @param string $tasksTableAlias
     * @param string $packagesTableAlias
     * @param string $filter
     *
     * @return array
     */
    protected function filterTasksByActions($tasksTableAlias, $packagesTableAlias, $filter)
    {
        $t = $tasksTableAlias;
        $p = $packagesTableAlias;
        $params = [];
        $packageFilters = [];

        foreach ($filter as $package => $actions) {
            $params[] = $package;
            $actionFilters = [];

            foreach ($actions as $action => $toVersions) {
                $params[] = $action;

                $toVersionHasNull = false;
                $toVersionsNotNull = 0;

                foreach ($toVersions as $toVersion => $_) {
                    if ($toVersion === '') {
                        $toVersionHasNull = true;
                    } else {
                        ++$toVersionsNotNull;
                        $params[] = $toVersion;
                    }
                }

                $toVersionFilters = [];

                if ($toVersionHasNull) {
                    $toVersionFilters[] = "$t.to_version IS NULL";
                }

                if ($toVersionsNotNull) {
                    $toVersionFilters[] = "$t.to_version IN (" . implode(',', array_fill(0, $toVersionsNotNull, '?')) . ')';
                }

                $actionFilters[] = "($t.action=? AND (" . implode(' OR ', $toVersionFilters) . '))';
            }

            $packageFilters[] = "($t.package=(SELECT $p.id FROM package $p WHERE $p.name=?) AND (" . implode(' OR ', $actionFilters) . '))';
        }

        return [implode(' OR ', $packageFilters), $params];
    }
}
