<?php

use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_cc_person'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'sql' => [ 'keys' => [ 'id' => 'primary' ] ],
    ],
    'list' => [
        'sorting' => [ 'mode'=>1,'fields'=>['name'] ],
        'label' => [ 'fields'=>['name','jobTitle'], 'showColumns'=>true ],
        'operations' => ['edit','copy','delete','show'],
    ],
    'palettes' => [
        'default' => '{title_legend},name,jobTitle;{image_legend},contentImage,contentImageAlt;{telefon_legend},contactTelephone,contactTelephoneLinkText,contactTelephoneTitleText;{email_legend},contactEmail,contactEmailLinkText,contactEmailTitleText',
    ],
    'fields' => [
        'id' => [ 'sql' => ['type'=>'integer','unsigned'=>true,'autoincrement'=>true] ],
        'tstamp' => [ 'sql' => ['type'=>'integer','unsigned'=>true,'default'=>0] ],
        'name' => [ 'inputType'=>'text','eval'=>['mandatory'=>true,'maxlength'=>255,'tl_class'=>'w50'], 'sql'=>['type'=>'string','length'=>255,'default'=>''] ],
        'jobTitle' => [ 'inputType'=>'text','eval'=>['maxlength'=>255,'tl_class'=>'w50'], 'sql'=>['type'=>'string','length'=>255,'default'=>''] ],
        'contactEmail' => [ 'inputType'=>'text','eval'=>['rgxp'=>'email','tl_class'=>'w50'], 'sql'=>['type'=>'string','length'=>255,'default'=>''] ],
        'contactEmailLinkText' => [ 'inputType'=>'text','eval'=>['tl_class'=>'w50'], 'sql'=>['type'=>'string','length'=>255,'default'=>''] ],
        'contactEmailTitleText' => [ 'inputType'=>'text','eval'=>['tl_class'=>'w50 clr'], 'sql'=>['type'=>'string','length'=>255,'default'=>''] ],
        'contactTelephone' => [ 'inputType'=>'text','eval'=>['tl_class'=>'w50'], 'sql'=>['type'=>'string','length'=>64,'default'=>''] ],
        'contactTelephoneLinkText' => [ 'inputType'=>'text','eval'=>['tl_class'=>'w50'], 'sql'=>['type'=>'string','length'=>255,'default'=>''] ],
        'contactTelephoneTitleText' => [ 'inputType'=>'text','eval'=>['tl_class'=>'w50 clr'], 'sql'=>['type'=>'string','length'=>255,'default'=>''] ],
        'contentImage' => [ 'inputType'=>'fileTree','eval'=>['fieldType'=>'radio','files'=>true,'tl_class'=>'clr'], 'sql'=>'binary(16) NULL' ],
        'contentImageAlt' => [ 'inputType'=>'text','eval'=>['maxlength'=>255,'tl_class'=>'w50'], 'sql'=>['type'=>'string','length'=>255,'default'=>''] ],
        // meta fields removed from backend UI
    ],
];
