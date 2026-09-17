<?php
/** Uses a dedicated disposable database; never modifies application tables. */
require $argv[1] ?? __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../src/Migration/RenameBranchHeadlineMigration.php';
require __DIR__.'/../src/Migration/EnsureTablesMigration.php';
require __DIR__.'/../src/Migration/EnsureModuleFieldsMigration.php';
use Doctrine\DBAL\DriverManager;
use DVC\ContaoCustomCatalog\Migration\RenameBranchHeadlineMigration;
use DVC\ContaoCustomCatalog\Migration\EnsureTablesMigration;
use DVC\ContaoCustomCatalog\Migration\EnsureModuleFieldsMigration;
// A dedicated disposable database is mandatory for schema-manager introspection.
$url = getenv('CATALOG_TEST_DATABASE_URL');
if (!$url || !str_ends_with(parse_url($url, PHP_URL_PATH) ?: '', '/catalog_regression')) {
    throw new RuntimeException('Set CATALOG_TEST_DATABASE_URL to the dedicated catalog_regression database.');
}
$db = DriverManager::getConnection(['url'=>$url]);
function verify(bool $ok, string $label): void { if (!$ok) { throw new RuntimeException($label); } echo "PASS $label\n"; }
$tables = ['tl_cc_branch','tl_cc_product','tl_cc_person','tl_dvc_cc_products_config','tl_module'];
foreach ($tables as $table) { $db->executeStatement('DROP TABLE IF EXISTS '.$table); }
try {
    $db->executeStatement("CREATE TABLE tl_cc_branch (id INT PRIMARY KEY, `überschrift` VARCHAR(255) NOT NULL DEFAULT '')");
    $db->executeStatement('INSERT INTO tl_cc_branch VALUES (1, ?), (2, ?)', ['Überschrift & "Text"', '']);
    $migration = new RenameBranchHeadlineMigration($db);
    verify($migration->shouldRun(), 'legacy column detected');
    verify($migration->run()->isSuccessful(), 'headline rename succeeds');
    verify($db->fetchFirstColumn('SELECT headline FROM tl_cc_branch ORDER BY id') === ['Überschrift & "Text"',''], 'all headline values preserved');
    verify(!$migration->shouldRun() && $migration->run()->isSuccessful(), 'headline migration idempotent');
    $db->executeStatement("ALTER TABLE tl_cc_branch ADD `überschrift` VARCHAR(255) DEFAULT ''");
    verify(!$migration->run()->isSuccessful(), 'ambiguous dual-column upgrade refuses to overwrite data');
    $db->executeStatement('DROP TABLE tl_cc_branch');
    $ensure = new EnsureTablesMigration($db);
    verify($ensure->shouldRun() && $ensure->run()->isSuccessful(), 'fresh catalog tables created');
    verify(!$ensure->shouldRun(), 'table creation idempotent');
    verify(isset($db->createSchemaManager()->listTableColumns('tl_cc_branch')['headline']), 'fresh schema uses ASCII headline');
    $db->executeStatement('DROP TABLE tl_dvc_cc_products_config');
    verify($ensure->shouldRun() && $ensure->run()->isSuccessful(), 'missing products config detected and restored');
    $db->executeStatement('ALTER TABLE tl_cc_person DROP contentImageAlt');
    verify($ensure->shouldRun() && $ensure->run()->isSuccessful(), 'missing existing-table column detected and restored');
    $db->executeStatement('CREATE TABLE tl_module (id INT PRIMARY KEY)');
    $modules = new EnsureModuleFieldsMigration($db);
    verify($modules->shouldRun() && $modules->run()->isSuccessful() && !$modules->shouldRun(), 'all module fields created idempotently');
} finally {
    foreach ($tables as $table) { $db->executeStatement('DROP TABLE IF EXISTS '.$table); }
}
