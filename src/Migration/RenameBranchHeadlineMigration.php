<?php

namespace DVC\ContaoCustomCatalog\Migration;

use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

final class RenameBranchHeadlineMigration implements MigrationInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function getName(): string
    {
        return 'Rename the legacy branch headline column to an ASCII identifier';
    }

    public function shouldRun(): bool
    {
        $schema = $this->connection->createSchemaManager();
        return $schema->tablesExist(['tl_cc_branch'])
            && isset($schema->listTableColumns('tl_cc_branch')['überschrift']);
    }

    public function run(): MigrationResult
    {
        if (!$this->shouldRun()) {
            return new MigrationResult(true, 'Branch headline already migrated.');
        }
        if (isset($this->connection->createSchemaManager()->listTableColumns('tl_cc_branch')['headline'])) {
            return new MigrationResult(false, 'Both headline columns exist. Resolve their values before renaming; no data was changed.');
        }
        try {
            // Rename in place: retain every value and the existing column definition.
            $this->connection->executeStatement('ALTER TABLE tl_cc_branch RENAME COLUMN `überschrift` TO headline');
        } catch (\Throwable $e) {
            return new MigrationResult(false, 'Could not rename branch headline: '.$e->getMessage());
        }
        return new MigrationResult(true, 'Renamed branch headline; all values retained.');
    }
}
