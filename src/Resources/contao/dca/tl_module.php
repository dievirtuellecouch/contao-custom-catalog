<?php

$GLOBALS['TL_DCA']['tl_module']['config']['onload_callback'][] = [\DVC\ContaoCustomCatalog\Dca\TlModule::class, 'onLoad'];

$GLOBALS['TL_DCA']['tl_module']['palettes']['dvc_cc_branch_search'] = '{title_legend},name,headline,type;{config_legend},jumpTo,dvc_cc_default_radius,dvc_cc_maps_api_key;{protected_legend:hide},protected;{expert_legend:hide},customTpl,guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['fields']['dvc_cc_reload_module_id'] = [
    'label' => ['Listen-Modul-ID', 'ID des zu aktualisierenden Listen-Moduls (z. B. 20 → mod::20).'],
    'inputType' => 'text',
    'eval' => ['rgxp' => 'digit', 'maxlength'=>10, 'tl_class'=>'w50'],
    'sql' => ['type'=>'integer', 'unsigned'=>true, 'default'=>0],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['dvc_cc_default_radius'] = [
    'label' => ['Standard-Radius (km)', 'Vorauswahl für den Suchradius.'],
    'inputType' => 'select',
    'options' => ['10','20','50','100'],
    'eval' => ['tl_class'=>'w50'],
    'sql' => ['type'=>'string','length'=>8,'default'=>'20'],
];

// Branches list module: jumpTo and optional custom SQL WHERE
$GLOBALS['TL_DCA']['tl_module']['palettes']['dvc_cc_branch_list'] = '{title_legend},name,headline,type;{config_legend},jumpTo,custom_sql_where;{protected_legend:hide},protected;{expert_legend:hide},customTpl,guests,cssID,space';

// Reader module: allow custom template
$GLOBALS['TL_DCA']['tl_module']['palettes']['dvc_cc_branch_reader'] = '{title_legend},name,headline,type;{config_legend},dvc_cc_maps_api_key;{protected_legend:hide},protected;{expert_legend:hide},customTpl,guests,cssID,space';

// Google Maps API Key (optional)
$GLOBALS['TL_DCA']['tl_module']['fields']['dvc_cc_maps_api_key'] = [
    'label' => ['Google Maps API Key', 'Optional: API-Schlüssel für Google Maps JavaScript API.'],
    'inputType' => 'text',
    'eval' => ['maxlength'=>255, 'tl_class'=>'w50'],
    'sql' => ['type'=>'string','length'=>255,'default'=>''],
];

// Target page for detail view
$GLOBALS['TL_DCA']['tl_module']['fields']['jumpTo'] = [
    'label' => ['Weiterleitungsseite', 'Zielseite für die Detailansicht.'],
    'inputType' => 'pageTree',
    'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
    'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
];

// Optional custom SQL WHERE for lists
$GLOBALS['TL_DCA']['tl_module']['fields']['custom_sql_where'] = [
    'label' => ['Custom SQL WHERE','Optionaler SQL-Filter (z. B. id IN (1,2))'],
    'inputType' => 'text',
    'eval' => ['maxlength'=>255,'tl_class'=>'w50 clr'],
    'sql' => ['type'=>'string','length'=>255,'default'=>''],
];

// Additional list module types for persons/products
$GLOBALS['TL_DCA']['tl_module']['palettes']['dvc_cc_product_list'] = '{title_legend},name,headline,type;{config_legend},custom_sql_where;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['dvc_cc_person_list']  = '{title_legend},name,headline,type;{config_legend},custom_sql_where;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

// Legacy custom catalog palettes (for records using older module types)
$GLOBALS['TL_DCA']['tl_module']['palettes']['customcataloglist']   = '{title_legend},name,headline,type;{config_legend},jumpTo;{protected_legend:hide},protected;{expert_legend:hide},customTpl,guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['customcatalogreader'] = '{title_legend},name,headline,type;{protected_legend:hide},protected;{expert_legend:hide},customTpl,guests,cssID,space';
