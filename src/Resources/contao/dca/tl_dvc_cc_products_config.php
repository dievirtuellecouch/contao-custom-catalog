<?php

use Contao\DC_Table;
use Contao\System;

$GLOBALS['TL_DCA']['tl_dvc_cc_products_config'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'sql' => [ 'keys' => [ 'id' => 'primary' ] ],
        'onload_callback' => [
            function() {
                // ensure singleton row exists
                $db = System::getContainer()->get('database_connection');
                $id = $db->fetchOne('SELECT id FROM tl_dvc_cc_products_config LIMIT 1');
                if (!$id) {
                    $db->insert('tl_dvc_cc_products_config', [
                        'tstamp' => time(),
                        'title' => 'Produkte',
                        'useTitleAsName' => '',
                        'list_fields' => serialize(['title']),
                        'list_order' => 'title DESC',
                        'showMenu' => '1',
                    ]);
                }
            },
        ],
    ],
    'list' => [
        'sorting' => [ 'mode' => 1, 'fields' => ['title'] ],
        'label' => [ 'fields' => ['title'], 'showColumns' => true ],
        'operations' => [ 'edit','show' ],
    ],
    'palettes' => [
        'default' => '{title_legend},title,useTitleAsName;{list_legend},list_fields,list_order;{integration_legend},showMenu',
    ],
    'fields' => [
        'id' => [ 'sql' => ['type'=>'integer','unsigned'=>true,'autoincrement'=>true] ],
        'tstamp' => [ 'sql' => ['type'=>'integer','unsigned'=>true,'default'=>0] ],
        'title' => [ 'label'=>&$GLOBALS['TL_LANG']['tl_dvc_cc_products_config']['title'], 'inputType'=>'text','eval'=>['mandatory'=>true,'maxlength'=>255,'tl_class'=>'w50'], 'sql'=>['type'=>'string','length'=>255,'default'=>''] ],
        'useTitleAsName' => [ 'label'=>&$GLOBALS['TL_LANG']['tl_dvc_cc_products_config']['useTitleAsName'], 'inputType'=>'checkbox','eval'=>['tl_class'=>'w50'], 'sql'=>['type'=>'string','length'=>1,'default'=>''] ],
        'list_fields' => [
            'label'=>&$GLOBALS['TL_LANG']['tl_dvc_cc_products_config']['list_fields'],
            'inputType'=>'select',
            'options'=>['title','titleAddition','name','description'],
            'eval'=>['multiple'=>true,'chosen'=>true,'tl_class'=>'clr'],
            'sql'=>'blob NULL'
        ],
        'list_order' => [
            'label'=>&$GLOBALS['TL_LANG']['tl_dvc_cc_products_config']['list_order'],
            'inputType'=>'select',
            'options'=>['title ASC','title DESC','name ASC','name DESC'],
            'eval'=>['tl_class'=>'w50','includeBlankOption'=>false,'chosen'=>true],
            'sql'=>['type'=>'string','length'=>32,'default'=>'title DESC']
        ],
        'showMenu' => [ 'label'=>&$GLOBALS['TL_LANG']['tl_dvc_cc_products_config']['showMenu'], 'inputType'=>'checkbox', 'sql'=>['type'=>'string','length'=>1,'default'=>'1'] ],
    ],
];

