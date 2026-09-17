<?php

namespace DVC\ContaoCustomCatalog\Dca;

class TlModule
{
    /**
     * Make AJAX reload mandatory and hide the checkbox from the backend UI.
     */
    public function onLoad(): void
    {
        if (!isset($GLOBALS['TL_DCA']['tl_module'])) {
            return;
        }

        // Strip the field from palettes strings
        foreach ($GLOBALS['TL_DCA']['tl_module']['palettes'] as $name => $palette) {
            if (!in_array($name, ['dvc_cc_branch_search', 'dvc_cc_branch_list', 'dvc_cc_branch_reader'], true) || !\is_string($palette)) {
                continue;
            }
            $palette = str_replace(',allowAjaxReload', '', $palette);
            $palette = str_replace('allowAjaxReload,', '', $palette);
            $palette = preg_replace('/(^|;)\{expert_legend(?::hide)?\},?\s*$/', '$1', (string) $palette);
            $GLOBALS['TL_DCA']['tl_module']['palettes'][$name] = $palette;
        }
    }
}

