<?php

namespace DVC\ContaoCustomCatalog\Migration;

use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

class EnsureTablesMigration implements MigrationInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function getName(): string
    {
        return 'Ensure tl_cc_branch, tl_cc_product, tl_cc_person tables exist';
    }

    public function shouldRun(): bool
    {
        try {
            $sm = method_exists($this->connection, 'createSchemaManager')
                ? $this->connection->createSchemaManager()
                : $this->connection->getSchemaManager();

            $names = array_map('strtolower', $sm->listTableNames());
            foreach (['tl_cc_branch','tl_cc_product','tl_cc_person'] as $t) {
                if (!in_array(strtolower($t), $names, true)) {
                    return true;
                }
            }
        } catch (\Throwable) {
        }
        return false;
    }

    public function run(): MigrationResult
    {
        $sm = method_exists($this->connection, 'createSchemaManager')
            ? $this->connection->createSchemaManager()
            : $this->connection->getSchemaManager();
        $existing = array_map('strtolower', $sm->listTableNames());

        $schema = new Schema();

        // Configuration table for products configuration
        if (!in_array('tl_dvc_cc_products_config', $existing, true)) {
            $t = $schema->createTable('tl_dvc_cc_products_config');
            $t->addColumn('id','integer',['autoincrement'=>true,'unsigned'=>true]);
            $t->addColumn('tstamp','integer',['unsigned'=>true,'default'=>0]);
            $t->addColumn('title','string',['length'=>255,'default'=>'']);
            $t->addColumn('useTitleAsName','string',['length'=>1,'default'=>'']);
            $t->addColumn('list_fields','blob',['notnull'=>false]);
            $t->addColumn('list_order','string',['length'=>32,'default'=>'title DESC']);
            $t->addColumn('showMenu','string',['length'=>1,'default'=>'1']);
            $t->setPrimaryKey(['id']);
        }

        if (!in_array('tl_cc_branch', $existing, true)) {
            $t = $schema->createTable('tl_cc_branch');
            $t->addColumn('id','integer',['autoincrement'=>true,'unsigned'=>true]);
            $t->addColumn('tstamp','integer',['unsigned'=>true,'default'=>0]);
            $t->addColumn('name','string',['length'=>255,'default'=>'']);
            // align with DCA: alias VARCHAR(128) NOT NULL DEFAULT ''
            $t->addColumn('alias','string',['length'=>128,'default'=>'']);
            $t->addColumn('überschrift','string',['length'=>255,'default'=>'']);
            $t->addColumn('addressTitle','string',['length'=>255,'default'=>'']);
            $t->addColumn('metaTitle','string',['length'=>255,'default'=>'']);
            $t->addColumn('metaDescription','text',['notnull'=>false]);
            $t->addColumn('metaRobots','string',['length'=>64,'default'=>'']);
            $t->addColumn('contactEmail','string',['length'=>255,'default'=>'']);
            $t->addColumn('contactPhone','string',['length'=>64,'default'=>'']);
            $t->addColumn('contactPerson','integer',['unsigned'=>true,'default'=>0]);
            $t->addColumn('contactAdditionText','text',['notnull'=>false]);
            $t->addColumn('address','string',['length'=>255,'default'=>'']);
            $t->addColumn('address_street','string',['length'=>255,'default'=>'']);
            $t->addColumn('address_zipcode','string',['length'=>32,'default'=>'']);
            $t->addColumn('address_city','string',['length'=>128,'default'=>'']);
            $t->addColumn('mapLink','string',['length'=>255,'default'=>'']);
            $t->addColumn('serviceGrouping','string',['length'=>32,'default'=>'']);
            // align with DCA: BLOB NULL
            $t->addColumn('openingHours','blob',['notnull'=>false]);
            $t->addColumn('importantNotice','text',['notnull'=>false]);
            $t->addColumn('availableProducts','blob',['notnull'=>false]);
            $t->addColumn('gallerySources','blob',['notnull'=>false]);
            $t->addColumn('published','boolean',['default'=>true]);
            $t->setPrimaryKey(['id']);
        }
        // If table exists, ensure new columns are present
        else {
            try {
                $cols = $sm->listTableColumns('tl_cc_branch');
                if (!isset($cols['addresstitle'])) {
                    $this->connection->executeStatement("ALTER TABLE tl_cc_branch ADD addressTitle VARCHAR(255) NOT NULL DEFAULT ''");
                }
                // ensure alias column length and nullability match DCA (VARCHAR(128) NOT NULL DEFAULT '')
                try {
                    $this->connection->executeStatement("ALTER TABLE tl_cc_branch MODIFY alias VARCHAR(128) NOT NULL DEFAULT ''");
                } catch (\Throwable) {}
                if (isset($cols['openinghours'])) {
                    $this->connection->executeStatement("ALTER TABLE tl_cc_branch MODIFY openingHours BLOB NULL");
                }
                if (!isset($cols['published'])) {
                    $this->connection->executeStatement("ALTER TABLE tl_cc_branch ADD published TINYINT(1) NOT NULL DEFAULT 1");
                }
            } catch (\Throwable) {}
        }

        if (!in_array('tl_cc_product', $existing, true)) {
            $t = $schema->createTable('tl_cc_product');
            $t->addColumn('id','integer',['autoincrement'=>true,'unsigned'=>true]);
            $t->addColumn('tstamp','integer',['unsigned'=>true,'default'=>0]);
            $t->addColumn('name','string',['length'=>255,'default'=>'']);
            // align with DCA
            $t->addColumn('title','string',['length'=>255,'default'=>'']);
            $t->addColumn('titleAddition','string',['length'=>255,'default'=>'']);
            $t->addColumn('alias','string',['length'=>255,'default'=>'']);
            $t->addColumn('description','text',['notnull'=>false]);
            $t->addColumn('metaTitle','string',['length'=>255,'default'=>'']);
            $t->addColumn('metaDescription','text',['notnull'=>false]);
            $t->setPrimaryKey(['id']);
        }
        // If table exists, ensure new columns are present
        else {
            try {
                $cols = $sm->listTableColumns('tl_cc_product');
                if (!isset($cols['title'])) {
                    $this->connection->executeStatement("ALTER TABLE tl_cc_product ADD title VARCHAR(255) NOT NULL DEFAULT ''");
                }
                if (!isset($cols['titleaddition'])) {
                    $this->connection->executeStatement("ALTER TABLE tl_cc_product ADD titleAddition VARCHAR(255) NOT NULL DEFAULT ''");
                }
            } catch (\Throwable) {}
        }

        if (!in_array('tl_cc_person', $existing, true)) {
            $t = $schema->createTable('tl_cc_person');
            $t->addColumn('id','integer',['autoincrement'=>true,'unsigned'=>true]);
            $t->addColumn('tstamp','integer',['unsigned'=>true,'default'=>0]);
            $t->addColumn('name','string',['length'=>255,'default'=>'']);
            $t->addColumn('jobTitle','string',['length'=>255,'default'=>'']);
            $t->addColumn('contactEmail','string',['length'=>255,'default'=>'']);
            $t->addColumn('contactEmailLinkText','string',['length'=>255,'default'=>'']);
            $t->addColumn('contactEmailTitleText','string',['length'=>255,'default'=>'']);
            $t->addColumn('contactTelephone','string',['length'=>64,'default'=>'']);
            $t->addColumn('contactTelephoneLinkText','string',['length'=>255,'default'=>'']);
            $t->addColumn('contactTelephoneTitleText','string',['length'=>255,'default'=>'']);
            $t->addColumn('contentImage','binary',['length'=>16,'notnull'=>false]);
            $t->addColumn('contentImageAlt','string',['length'=>255,'default'=>'']);
            $t->addColumn('metaTitle','string',['length'=>255,'default'=>'']);
            $t->addColumn('metaDescription','text',['notnull'=>false]);
            $t->setPrimaryKey(['id']);
        }
        // If table exists, ensure new columns are present
        else {
            try {
                $cols = $sm->listTableColumns('tl_cc_person');
                if (!isset($cols['contactemaillinktext'])) {
                    $this->connection->executeStatement("ALTER TABLE tl_cc_person ADD contactEmailLinkText VARCHAR(255) NOT NULL DEFAULT ''");
                }
                if (!isset($cols['contactemailtitletext'])) {
                    $this->connection->executeStatement("ALTER TABLE tl_cc_person ADD contactEmailTitleText VARCHAR(255) NOT NULL DEFAULT ''");
                }
                if (!isset($cols['contacttelephonelinktext'])) {
                    $this->connection->executeStatement("ALTER TABLE tl_cc_person ADD contactTelephoneLinkText VARCHAR(255) NOT NULL DEFAULT ''");
                }
                if (!isset($cols['contacttelephonetitletext'])) {
                    $this->connection->executeStatement("ALTER TABLE tl_cc_person ADD contactTelephoneTitleText VARCHAR(255) NOT NULL DEFAULT ''");
                }
                if (!isset($cols['contentimagealt'])) {
                    $this->connection->executeStatement("ALTER TABLE tl_cc_person ADD contentImageAlt VARCHAR(255) NOT NULL DEFAULT ''");
                }
            } catch (\Throwable) {}
        }

        $sql = $schema->toSql($this->connection->getDatabasePlatform());
        foreach ($sql as $query) {
            $this->connection->executeStatement($query);
        }

        return new MigrationResult(true, 'Custom catalog tables ensured.');
    }
}

