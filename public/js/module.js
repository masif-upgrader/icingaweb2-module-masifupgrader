(function(Icinga, $) {
    Icinga.availableModules.masifupgrader = function(module) {
        module.on('click', '.invertable-checkboxes-trigger', onTriggerInvertableCheckboxes);
    };

    function onTriggerInvertableCheckboxes(event) {
        $(event.target)
            .closest('.invertable-checkboxes')
            .find('.invertable-checkbox input[type=checkbox]')
            .each(invertCheckbox);
    }

    function invertCheckbox(_, checkbox) {
        checkbox.checked = !checkbox.checked;
    }
})(Icinga, jQuery);
