<?php

namespace Icinga\Module\Masifupgrader\Web;

trait InvertableCheckboxesTrait
{
    /**
     * @var string
     */
    private static $invertableCheckboxesJS = "$(this).closest('.invertable-checkboxes').find('.invertable-checkbox input[type=checkbox]').each(function(_,c){c.checked=!c.checked;})";
}
