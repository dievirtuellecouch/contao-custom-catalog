<?php

use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_cc_product'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'sql' => [ 'keys' => [ 'id' => 'primary' ] ],
        'onload_callback' => [
            function() {
                if (!(defined('TL_MODE') && TL_MODE === 'BE')) { return; }
                // Only inject on our module to avoid side effects elsewhere
                if ((\Contao\Input::get('do') ?? '') !== 'cc_products') { return; }
                    $style = '<style>table.tl_listing th.tl_folder_list, table.tl_listing th.tl_folder_tlist { display:none !important; }</style>';
                    $GLOBALS['TL_HEAD'][] = $style;

                    $script = <<<'HTML'
<script>(function(){
 function cleanup(){
  var rows=document.querySelectorAll('table.tl_listing tr');
  for(var i=0;i<rows.length;i++){
    var thList=rows[i].querySelector('th.tl_folder_list, th.tl_folder_tlist');
    if(thList){ rows[i].remove(); }
  }
 }
 function ins(){
  var h=document.querySelector('.tl_pagetitle');
  if(h){
    var wrap=document.querySelector('.dvc-config-link');
    if(!wrap){wrap=document.createElement('div');wrap.className='dvc-config-link';h.parentNode.insertBefore(wrap,h);} 
    var a=wrap.querySelector('a.header_edit_config');
    if(!a){a=document.createElement('a');a.className='header_edit_config';a.setAttribute('onclick','Backend.getScrollOffset();');a.setAttribute('accesskey','e');a.textContent='Konfiguration bearbeiten';a.href='do=dvc_cc_products_config';wrap.appendChild(a);} 
  }
  cleanup();
  // Observe dynamic changes and cleanup again if needed
  var list=document.getElementById('tl_listing');
  if(list && window.MutationObserver){
    var mo=new MutationObserver(function(){ cleanup(); });
    mo.observe(list,{childList:true,subtree:true});
  }
 }
 if(document.readyState==='loading'){
   document.addEventListener('DOMContentLoaded',ins);
 } else {
   ins();
 }
})();</script>
HTML;
                    $GLOBALS['TL_BODY'][] = $script;
            }
        ],
    ],
    'list' => [
        // mode 1 + flag 11 = simple ascending sort; suppress grouping via group_callback
        'sorting' => [
            'mode' => 1,
            'fields' => ['title'],
            'flag' => 11,
            'disableGrouping' => true,
            'group_callback' => static function ($group, $mode, $field, $row, \Contao\DataContainer $dc = null) {
                return '';
            },
        ],
        // Backend label: show title and optional annotation (titleAddition) in parentheses
        'label' => [
            'fields' => ['title','titleAddition','name'],
            'showColumns' => false,
            'label_callback' => static function(array $row, string $label, \Contao\DataContainer $dc = null, array $args = []) {
                // Prefer public title; fall back to internal name if title is empty
                $title = trim((string) ($args[0] ?? ''));
                if ($title === '') {
                    $title = trim((string) ($args[2] ?? ''));
                }
                $addition = trim((string) ($args[1] ?? ''));
                // Avoid duplicate text like "Foo (Foo)"
                if ($addition !== '' && strcasecmp($addition, $title) !== 0) {
                    return $title.' ('.$addition.')';
                }
                return $title;
            },
        ],
        'operations' => ['edit','copy','delete','show'],
    ],
    'palettes' => [
        // Removed {meta_legend} with alias, metaTitle, metaDescription
        // Append internal group at the end with internal title field (name)
        'default' => '{title_legend},title,titleAddition;{content_legend},description;{intern_legend},name',
    ],
    'fields' => [
        'id' => [ 'sql' => ['type'=>'integer','unsigned'=>true,'autoincrement'=>true] ],
        'tstamp' => [ 'sql' => ['type'=>'integer','unsigned'=>true,'default'=>0] ],
        // store name internally but not in palette; title maps to this during import
        'name' => [ 'inputType'=>'text','eval'=>['maxlength'=>255], 'sql'=>['type'=>'string','length'=>255,'default'=>''] ],
        'title' => [ 'inputType'=>'text','eval'=>['mandatory'=>true,'maxlength'=>255,'tl_class'=>'w50'], 'sql'=>['type'=>'string','length'=>255,'default'=>''] ],
        'titleAddition' => [ 'inputType'=>'text','eval'=>['maxlength'=>255,'tl_class'=>'w50'], 'sql'=>['type'=>'string','length'=>255,'default'=>''] ],
        'alias' => [ 'inputType'=>'text','eval'=>['maxlength'=>255], 'sql'=>['type'=>'string','length'=>255,'default'=>''] ],
        'description' => [ 'inputType'=>'textarea','eval'=>['rte'=>'tinyMCE'], 'sql'=>'text NULL' ],
        'metaTitle' => [ 'inputType'=>'text','eval'=>['maxlength'=>255], 'sql'=>['type'=>'string','length'=>255,'default'=>''] ],
        'metaDescription' => [ 'inputType'=>'textarea', 'sql'=>'text NULL' ],
    ],
];
