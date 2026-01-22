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

        // Remove selector/subpalette for allowAjaxReload
        if (!empty($GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'])) {
            $GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'] = array_values(array_filter(
                (array) $GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'],
                static fn($f) => $f !== 'allowAjaxReload'
            ));
        }
        unset($GLOBALS['TL_DCA']['tl_module']['subpalettes']['allowAjaxReload']);

        // Hide the field in backend forms if present
        if (isset($GLOBALS['TL_DCA']['tl_module']['fields']['allowAjaxReload'])) {
            $GLOBALS['TL_DCA']['tl_module']['fields']['allowAjaxReload']['eval']['tl_class'] = 'invisible';
        }

        // Strip the field from palettes strings
        foreach ($GLOBALS['TL_DCA']['tl_module']['palettes'] as $name => $palette) {
            if (!\is_string($palette)) {
                continue;
            }
            $palette = str_replace(',allowAjaxReload', '', $palette);
            $palette = str_replace('allowAjaxReload,', '', $palette);
            $palette = preg_replace('/(^|;)\{expert_legend(?::hide)?\},?\s*$/', '$1', (string) $palette);
            $GLOBALS['TL_DCA']['tl_module']['palettes'][$name] = $palette;
        }
    }
}

