<?php
/** Run with: php tests/regression.php /path/to/contao/vendor/autoload.php */
require $argv[1] ?? __DIR__.'/../vendor/autoload.php';
spl_autoload_register(static function ($class) {
    $prefix = 'DVC\\ContaoCustomCatalog\\';
    if (str_starts_with($class, $prefix)) {
        $file = __DIR__.'/../src/'.str_replace('\\', '/', substr($class, strlen($prefix))).'.php';
        if (is_file($file)) { require $file; }
    }
}, true, true);

use DVC\ContaoCustomCatalog\Util\Coordinates;
use DVC\ContaoCustomCatalog\Util\ReaderContent;
use DVC\ContaoCustomCatalog\Template\EntryWrapper;
use DVC\ContaoCustomCatalog\Dca\TlModule;
use DVC\ContaoCustomCatalog\EventSubscriber\ResponseReplaceSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

$count = 0;
function check(bool $ok, string $message): void {
    global $count;
    if (!$ok) { throw new RuntimeException($message); }
    ++$count;
    echo "PASS $message\n";
}
foreach ([['50.1,8.2', [50.1,8.2]], [serialize(['50.1','8.2']), [50.1,8.2]], [[0,0],[0.0,0.0]], ['',null], [serialize(['','']),null], ['city,street',null], ['91,0',null], ['0,181',null], ['1,2,3',null], [serialize(new stdClass()),null]] as [$input,$expected]) {
    check(Coordinates::fromRaw($input) === $expected, 'coordinate input '.json_encode($input));
}
check(Coordinates::fromModel((object)['address'=>'', 'address_lat'=>'50', 'address_lng'=>'8']) === [50.0,8.0], 'legacy coordinate columns');
check(Coordinates::pair(INF,0) === null, 'reject infinite coordinates');
$entry = new EntryWrapper((object)['headline'=>'Überschrift & Text']);
check($entry->field('überschrift')->value() === $entry->field('headline')->value(), 'legacy headline template compatibility');
check($entry->{'überschrift'} === 'Überschrift & Text', 'legacy headline property');
foreach (['<main class="x">OLD</main>', '<div id="main"><div>ONE<div>TWO</div></div>THREE</div>', '<body class="x">OLD</body>'] as $input) {
    $page = '<!doctype html><html><head><title>T</title></head><header>H</header>'.$input.'<footer>F</footer></html>';
    $result = ReaderContent::replace($page, '<section>NEW $1</section>');
    check(str_contains($result, '<title>T</title>') && str_contains($result,'<footer>F</footer>') && str_contains($result,'NEW $1') && !str_contains($result,'THREE'), 'replace only target container: '.$input);
}
$GLOBALS['TL_DCA']['tl_module'] = ['palettes'=>['__selector__'=>['allowAjaxReload'], 'other'=>'{x},allowAjaxReload', 'dvc_cc_branch_list'=>'{x},allowAjaxReload,jumpTo'], 'subpalettes'=>['allowAjaxReload'=>'x'], 'fields'=>['allowAjaxReload'=>['eval'=>['tl_class'=>'w50']]]];
(new TlModule())->onLoad();
check($GLOBALS['TL_DCA']['tl_module']['palettes']['other'] === '{x},allowAjaxReload', 'unrelated module AJAX palette retained');
check($GLOBALS['TL_DCA']['tl_module']['subpalettes']['allowAjaxReload'] === 'x', 'shared AJAX selector retained');
$jump = ['eval'=>['mandatory'=>true],'relation'=>['type'=>'hasOne']];
$GLOBALS['TL_DCA']['tl_module']['fields']['jumpTo'] = $jump;
require __DIR__.'/../src/Resources/contao/dca/tl_module.php';
check($GLOBALS['TL_DCA']['tl_module']['fields']['jumpTo'] === $jump, 'core jumpTo definition retained');
require __DIR__.'/../src/Resources/contao/dca/tl_cc_branch.php';
check(isset($GLOBALS['TL_DCA']['tl_cc_branch']['fields']['headline']) && !isset($GLOBALS['TL_DCA']['tl_cc_branch']['fields']['überschrift']), 'branch DCA uses ASCII column');
$kernel = new class implements HttpKernelInterface { public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response { return new Response(); } };
$request = new Request(); $request->attributes->set('_dvc_cc_reader_html','DETAIL');
foreach (['application/json','text/html'] as $type) {
    $response = new Response('<main>OLD</main>',200,['Content-Type'=>$type]);
    (new ResponseReplaceSubscriber())(new ResponseEvent($kernel,$request,HttpKernelInterface::MAIN_REQUEST,$response));
    check($response->getContent() === ($type === 'text/html' ? '<main>DETAIL</main>' : '<main>OLD</main>'), 'response content type '.$type);
}
echo "$count regression assertions passed\n";
