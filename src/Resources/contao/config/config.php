<?php

// Register model mapping
$GLOBALS['TL_MODELS']['tl_cc_branch']  = DVC\ContaoCustomCatalog\Model\BranchModel::class;
$GLOBALS['TL_MODELS']['tl_cc_product'] = DVC\ContaoCustomCatalog\Model\ProductModel::class;
$GLOBALS['TL_MODELS']['tl_cc_person']  = DVC\ContaoCustomCatalog\Model\PersonModel::class;

// Frontend modules (group for module picker)
$GLOBALS['FE_MOD']['katalog']['dvc_cc_branch_search']  = DVC\ContaoCustomCatalog\Module\ModuleBranchSearch::class;
$GLOBALS['FE_MOD']['katalog']['dvc_cc_branch_list']    = DVC\ContaoCustomCatalog\Module\ModuleCustomCatalogList::class;
$GLOBALS['FE_MOD']['katalog']['dvc_cc_branch_reader']  = DVC\ContaoCustomCatalog\Module\ModuleCustomCatalogReader::class;

// Legacy module type aliases for compatibility with existing records (frontend only)
if (defined('TL_MODE') && TL_MODE === 'FE') {
    $GLOBALS['FE_MOD']['custom_catalog']['customcataloglist']   = DVC\ContaoCustomCatalog\Module\ModuleCustomCatalogList::class;
    $GLOBALS['FE_MOD']['custom_catalog']['customcatalogreader'] = DVC\ContaoCustomCatalog\Module\ModuleCustomCatalogReader::class;
}

// Backend modules (navigation) under Katalog group (as requested)
// Ensure the custom group "katalog" appears directly under the core "content" group
$keys = array_keys($GLOBALS['BE_MOD']);
$pos = array_search('content', $keys, true);
if ($pos !== false && !array_key_exists('katalog', $GLOBALS['BE_MOD'])) {
    if (function_exists('array_insert')) {
        array_insert($GLOBALS['BE_MOD'], $pos + 1, ['katalog' => []]);
    } else {
        $before = array_slice($GLOBALS['BE_MOD'], 0, $pos + 1, true);
        $after  = array_slice($GLOBALS['BE_MOD'], $pos + 1, null, true);
        $GLOBALS['BE_MOD'] = $before + ['katalog' => []] + $after;
    }
}

$GLOBALS['BE_MOD']['katalog']['cc_branches'] = [ 'tables' => ['tl_cc_branch'] ];
$GLOBALS['BE_MOD']['katalog']['cc_products'] = [ 'tables' => ['tl_cc_product'] ];
$GLOBALS['BE_MOD']['katalog']['cc_persons']  = [ 'tables' => ['tl_cc_person'] ];

// Backend config page for products (hidden in nav)
$GLOBALS['BE_MOD']['katalog']['dvc_cc_products_config'] = [
    'tables' => ['tl_dvc_cc_products_config'],
    'hideInNavigation' => true,
];

// Move the 'katalog' backend group to the very top of the backend navigation
try {
    $modules = $GLOBALS['BE_MOD'] ?? [];
    $katalog = $modules['katalog'] ?? [];
    unset($modules['katalog']);
    // Prepend katalog
    $GLOBALS['BE_MOD'] = ['katalog' => $katalog] + $modules;
} catch (\Throwable $e) {
    // noop
}

// Ensure wrapper_tags content elements aliases are available (if wrapper tags bundle is present)
if (!isset($GLOBALS['TL_CTE']['wrapper_tags']['wt_opening_tags']) && class_exists(\Zmyslny\WrapperTags\ContentElement\OpeningTagsElement::class)) {
    $GLOBALS['TL_CTE']['wrapper_tags']['wt_opening_tags'] = \Zmyslny\WrapperTags\ContentElement\OpeningTagsElement::class;
}
if (!isset($GLOBALS['TL_CTE']['wrapper_tags']['wt_closing_tags']) && class_exists(\Zmyslny\WrapperTags\ContentElement\ClosingTagsElement::class)) {
    $GLOBALS['TL_CTE']['wrapper_tags']['wt_closing_tags'] = \Zmyslny\WrapperTags\ContentElement\ClosingTagsElement::class;
}
if (!isset($GLOBALS['TL_CTE']['wrapper_tags']['wt_complete_tags']) && class_exists(\Zmyslny\WrapperTags\ContentElement\CompleteTagsElement::class)) {
    $GLOBALS['TL_CTE']['wrapper_tags']['wt_complete_tags'] = \Zmyslny\WrapperTags\ContentElement\CompleteTagsElement::class;
}
if (!isset($GLOBALS['TL_CTE']['wrapper_tags']['opening_tags']) && class_exists(\Zmyslny\WrapperTags\ContentElement\OpeningTagsElement::class)) {
    $GLOBALS['TL_CTE']['wrapper_tags']['opening_tags'] = \Zmyslny\WrapperTags\ContentElement\OpeningTagsElement::class;
}
if (!isset($GLOBALS['TL_CTE']['wrapper_tags']['closing_tags']) && class_exists(\Zmyslny\WrapperTags\ContentElement\ClosingTagsElement::class)) {
    $GLOBALS['TL_CTE']['wrapper_tags']['closing_tags'] = \Zmyslny\WrapperTags\ContentElement\ClosingTagsElement::class;
}
if (!isset($GLOBALS['TL_CTE']['wrapper_tags']['complete_tags']) && class_exists(\Zmyslny\WrapperTags\ContentElement\CompleteTagsElement::class)) {
    $GLOBALS['TL_CTE']['wrapper_tags']['complete_tags'] = \Zmyslny\WrapperTags\ContentElement\CompleteTagsElement::class;
}
// Mark wrapper types for Contao wrappers handling
if (!in_array('opening_tags', $GLOBALS['TL_WRAPPERS']['start'] ?? [])) {
    $GLOBALS['TL_WRAPPERS']['start'][] = 'opening_tags';
}
if (!in_array('wt_opening_tags', $GLOBALS['TL_WRAPPERS']['start'] ?? [])) {
    $GLOBALS['TL_WRAPPERS']['start'][] = 'wt_opening_tags';
}
if (!in_array('closing_tags', $GLOBALS['TL_WRAPPERS']['stop'] ?? [])) {
    $GLOBALS['TL_WRAPPERS']['stop'][] = 'closing_tags';
}
if (!in_array('wt_closing_tags', $GLOBALS['TL_WRAPPERS']['stop'] ?? [])) {
    $GLOBALS['TL_WRAPPERS']['stop'][] = 'wt_closing_tags';
}
if (!in_array('complete_tags', $GLOBALS['TL_WRAPPERS']['single'] ?? [])) {
    $GLOBALS['TL_WRAPPERS']['single'][] = 'complete_tags';
}
if (!in_array('wt_complete_tags', $GLOBALS['TL_WRAPPERS']['single'] ?? [])) {
    $GLOBALS['TL_WRAPPERS']['single'][] = 'wt_complete_tags';
}
