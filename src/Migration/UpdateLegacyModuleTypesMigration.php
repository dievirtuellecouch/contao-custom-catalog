<?php

namespace DVC\ContaoCustomCatalog\Migration;

use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class UpdateLegacyModuleTypesMigration implements MigrationInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function getName(): string
    {
        return 'Update legacy module types customcataloglist/reader to dvc_cc_* types';
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

            $cnt = (int) $this->connection->fetchOne("SELECT COUNT(*) FROM tl_module WHERE type IN ('customcataloglist','customcatalogreader')");
            return $cnt > 0;
        } catch (\Throwable) {
            return false;
        }
    }

    public function run(): MigrationResult
    {
        try {
            $this->connection->executeStatement("UPDATE tl_module SET type='dvc_cc_branch_list' WHERE type='customcataloglist'");
            $this->connection->executeStatement("UPDATE tl_module SET type='dvc_cc_branch_reader' WHERE type='customcatalogreader'");
        } catch (\Throwable $e) {
            return new MigrationResult(false, 'Failed to update legacy module types: '.$e->getMessage());
        }
        return new MigrationResult(true, 'Updated legacy module types to dvc_cc_*');
    }
}

