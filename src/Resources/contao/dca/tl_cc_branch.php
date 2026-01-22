<?php

use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_cc_branch'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => 1,
            'fields' => ['name'],
        ],
        'label' => [
            'fields' => ['name'],
            'showColumns' => true,
        ],
        'operations' => [
            'edit', 'copy', 'delete', 'show'
        ],
    ],
    'palettes' => [
        '__selector__' => [],
        'default' => '{title_legend},name,überschrift,alias;{meta_legend},metaTitle,metaDescription,metaRobots;{address_legend},addressTitle,address,address_street,address_zipcode,address_city,contactAdditionText;{opening_legend},openingHours;{zusatzinformationen_legend},mapLink,importantNotice;{contact_legend},contactEmail,contactPhone;{business_legend},serviceGrouping,availableProducts;{gallery_legend},gallerySources;{ansprechpartner_legend},contactPerson;{publish_legend},published',
    ],
    'fields' => [
        'id' => [ 'sql' => ['type'=>'integer','unsigned'=>true,'autoincrement'=>true] ],
        'tstamp' => [ 'sql' => ['type'=>'integer','unsigned'=>true,'default'=>0] ],
        'name' => [
            'inputType' => 'text',
            'search' => true,
            'eval' => ['mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'],
            'sql' => ['type'=>'string','length'=>255,'default'=>''],
        ],
        'überschrift' => [
            'inputType' => 'text',
            'eval' => ['maxlength'=>255, 'tl_class'=>'w50'],
            'sql' => ['type'=>'string','length'=>255,'default'=>''],
        ],
        'metaTitle' => [ 'inputType'=>'text','eval'=>['maxlength'=>255, 'tl_class'=>'w50'],'sql'=>['type'=>'string','length'=>255,'default'=>''] ],
        'metaDescription' => [ 'inputType'=>'textarea','eval'=>['tl_class'=>'w50'], 'sql'=>'text NULL' ],
        'metaRobots' => [
            'inputType'=>'select',
            'options' => ['index,follow','index,nofollow','noindex,follow','noindex,nofollow'],
            'eval'=>['includeBlankOption'=>true,'maxlength'=>64, 'tl_class'=>'w50'],
            'sql'=>['type'=>'string','length'=>64,'default'=>'']
        ],
        'contactEmail' => [ 'inputType'=>'text','eval'=>['rgxp'=>'email','tl_class'=>'w50'],'sql'=>['type'=>'string','length'=>255,'default'=>''] ],
        'contactPhone' => [ 'inputType'=>'text','eval'=>['tl_class'=>'w50'],'sql'=>['type'=>'string','length'=>64,'default'=>''] ],
        'contactPerson' => [ 'inputType'=>'select','eval'=>['includeBlankOption'=>true],'foreignKey'=>'tl_cc_person.name','sql'=>['type'=>'integer','unsigned'=>true,'default'=>0] ],
        'contactAdditionText' => [ 'inputType'=>'textarea','eval'=>['rte'=>'tinyMCE'], 'sql'=>'text NULL' ],
        'addressTitle' => [
            'inputType' => 'text',
            'eval' => ['maxlength'=>255],
            'sql' => ['type'=>'string','length'=>255,'default'=>''],
        ],
        'address' => [
            'inputType' => 'text',
            'eval' => [
                'multiple' => true,
                'size' => 2,
                // full-width wrapper; Contao's tl_text_2 will make both inputs half width
                'tl_class' => 'clr',
            ],
            'sql'=>['type'=>'string','length'=>255,'default'=>'']
        ],
        'address_street' => [ 'inputType'=>'text','eval'=>['tl_class'=>'w50'], 'sql'=>['type'=>'string','length'=>255,'default'=>''] ],
        'address_zipcode' => [ 'inputType'=>'text','eval'=>['tl_class'=>'w50'], 'sql'=>['type'=>'string','length'=>32,'default'=>''] ],
        'address_city' => [
            'inputType' => 'text',
            'eval' => ['tl_class' => 'clr'],
            'sql' => ['type' => 'string', 'length' => 128, 'default' => ''],
        ],
        'mapLink' => [ 'inputType'=>'text','sql'=>['type'=>'string','length'=>255,'default'=>''] ],
        'serviceGrouping' => [
            'inputType' => 'radio',
            'options' => [
                'none' => 'Keinen',
                'zls'  => 'ZLS – Zulassungsservice',
                'zld'  => 'ZLD – Zulassungsdienst',
            ],
            'eval' => [],
            'sql' => ['type'=>'string','length'=>64,'default'=>'']
        ],
        'openingHours' => [
            'inputType' => 'tableWizard',
            'eval' => [
                'allowHtml' => true,
                'tl_class' => 'clr',
                // Match legacy sizing: smaller cells for day/time
                'style' => 'width:142px;height:66px',
            ],
            'sql' => 'blob NULL',
        ],
        'importantNotice' => [ 'inputType'=>'textarea','eval'=>['rte'=>'tinyMCE'], 'sql'=>'text NULL' ],
        'availableProducts' => [
            'inputType' => 'select',
            'eval' => ['multiple' => true, 'chosen' => true],
            // Build labels from product title (+ optional addition), not the empty internal name
            'options_callback' => ['DVC\\ContaoCustomCatalog\\Dca\\TlCcBranch', 'getProductOptions'],
            'sql' => 'blob NULL',
        ],
        'gallerySources' => [
            'inputType' => 'fileTree',
            'eval' => [
                'multiple' => true,
                'fieldType' => 'checkbox',
                'filesOnly' => true,
                'isGallery' => true,
                'isSortable' => true,
            ],
            'sql' => 'blob NULL',
        ],
        'published' => [ 'inputType'=>'checkbox', 'eval'=>['isBoolean'=>true], 'sql'=>['type'=>'boolean','default'=>true] ],
        'alias' => [
            'inputType' => 'text',
            'eval' => ['maxlength'=>128, 'tl_class'=>'w50'],
            'sql' => ['type'=>'string','length'=>128,'default'=>''],
        ],
    ],
];
