<?php

namespace DVC\ContaoCustomCatalog\Migration;

use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class EnsureModuleFieldsMigration implements MigrationInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function getName(): string
    {
        return 'Ensure tl_module has dvc_cc_* fields for branch search module';
    }

    public function shouldRun(): bool
    {
        try {
            $sm = method_exists($this->connection, 'createSchemaManager')
                ? $this->connection->createSchemaManager()
                : $this->connection->getSchemaManager();

            if (!$sm->tablesExist(['tl_module'])) {
                return false;
            }

            $cols = $sm->listTableColumns('tl_module');
            if (!isset($cols['dvc_cc_reload_module_id']) || !isset($cols['dvc_cc_default_radius'])) {
                return true;
            }
        } catch (\Throwable) {
        }
        return false;
    }

    public function run(): MigrationResult
    {
        try {
            $sm = method_exists($this->connection, 'createSchemaManager')
                ? $this->connection->createSchemaManager()
                : $this->connection->getSchemaManager();
            $cols = $sm->listTableColumns('tl_module');

            if (!isset($cols['dvc_cc_reload_module_id'])) {
                $this->connection->executeStatement("ALTER TABLE tl_module ADD dvc_cc_reload_module_id INT UNSIGNED NOT NULL DEFAULT 0");
            }
            if (!isset($cols['dvc_cc_default_radius'])) {
                $this->connection->executeStatement("ALTER TABLE tl_module ADD dvc_cc_default_radius VARCHAR(8) NOT NULL DEFAULT '20'");
            }
        } catch (\Throwable $e) {
            return new MigrationResult(false, 'Failed to ensure tl_module fields: '.$e->getMessage());
        }
        return new MigrationResult(true, 'Ensured tl_module fields for branch search module');
    }
}

