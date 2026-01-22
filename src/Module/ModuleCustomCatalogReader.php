<?php

namespace DVC\ContaoCustomCatalog\Module;

use Contao\Input;
use Contao\Module;
use Contao\System;
use DVC\ContaoCustomCatalog\Model\BranchModel;

class ModuleCustomCatalogReader extends Module
{
    protected $strTemplate = 'mod_dvc_cc_branch_reader';

    protected ?string $currentItem = null;

    public function generate(): string
    {
        // Determine item before rendering; if none, render nothing so the page remains regular
        $item = Input::get('auto_item') ?: Input::get('items');
        if ($item === null || $item === '') {
            try {
                $req = \Contao\System::getContainer()->get('request_stack')->getCurrentRequest();
                $path = $req?->getPathInfo() ?? '';
                $segments = array_values(array_filter(explode('/', trim($path, '/'))));
                global $objPage;
                $pageAlias = $objPage?->alias ?? null;
                if ($pageAlias) {
                    $idx = array_search($pageAlias, $segments, true);
                    if ($idx !== false && isset($segments[$idx+1])) {
                        $item = $segments[$idx+1];
                    }
                } elseif (isset($segments[1])) {
                    $item = $segments[1];
                }
            } catch (\Throwable) {}
        }

        if (!$item) {
            return '';
        }

        $this->currentItem = (string) $item;
        $buffer = parent::generate();
        if ($this->hasItem && $buffer !== '') {
            try {
                $req = \Contao\System::getContainer()->get('request_stack')->getCurrentRequest();
                if ($req) {
                    $req->attributes->set('_dvc_cc_reader_html', $buffer);
                }
            } catch (\Throwable) {}
        }
        return '';
    }

    protected bool $hasItem = false;

    protected function compile(): void
    {
        // Pass optional Google Maps API key from module to template
        $this->Template->mapsApiKey = (string) ($this->dvc_cc_maps_api_key ?? '');

        $item = $this->currentItem ?? (Input::get('auto_item') ?: Input::get('items'));
        $hadItemParam = ($item !== null && $item !== '');
        // Fallback: extract next URL segment after current page alias
        if ($item === null || $item === '') {
            try {
                $req = \Contao\System::getContainer()->get('request_stack')->getCurrentRequest();
                $path = $req?->getPathInfo() ?? '';
                $segments = array_values(array_filter(explode('/', trim($path, '/'))));
                global $objPage;
                $pageAlias = $objPage?->alias ?? null;
                if ($pageAlias) {
                    $idx = array_search($pageAlias, $segments, true);
                    if ($idx !== false && isset($segments[$idx+1])) {
                        $item = $segments[$idx+1];
                    }
                } elseif (isset($segments[1])) {
                    $item = $segments[1];
                }
                if (($item === null || $item === '') && !empty($segments)) {
                    $item = end($segments) ?: null;
                }
            } catch (\Throwable) {}
        }

        $entry = null;
        if ($item) {
            // Try by numeric ID
            if (ctype_digit((string)$item)) {
                $tmp = BranchModel::findByPk((int) $item);
                if ($tmp && (int)($tmp->published ?? 0) === 1) {
                    $entry = $tmp;
                }
            }
            // Try by alias
            if (null === $entry) {
                $tmp = BranchModel::findOneBy('alias', $item);
                if ($tmp && (int)($tmp->published ?? 0) === 1) {
                    $entry = $tmp;
                }
            }
            // Try by name
            if (null === $entry) {
                $tmp = BranchModel::findOneBy('name', $item);
                if ($tmp && (int)($tmp->published ?? 0) === 1) {
                    $entry = $tmp;
                }
            }
        }
        if (null === $entry) {
            $this->hasItem = false;
            if ($hadItemParam) {
                throw new \Contao\CoreBundle\Exception\PageNotFoundException('Custom catalog: branch not found');
            }
            return;
        }

        $this->hasItem = true;
        $this->Template->entries = [new \DVC\ContaoCustomCatalog\Template\EntryWrapper($entry)];
    }
}
