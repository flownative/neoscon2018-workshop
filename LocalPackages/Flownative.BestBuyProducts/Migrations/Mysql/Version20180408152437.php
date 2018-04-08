<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs! This block will be used as the migration description if getDescription() is not used.
 */
class Version20180408152437 extends AbstractMigration
{

    /**
     * @return string
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('DROP TABLE flownative_bestbuyproducts_domain_model_85f25_categorypath_join');
        $this->addSql('ALTER TABLE flownative_bestbuyproducts_domain_model_product ADD category VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE flownative_bestbuyproducts_domain_model_product ADD CONSTRAINT FK_6B370A3364C19C1 FOREIGN KEY (category) REFERENCES flownative_bestbuyproducts_domain_model_category (id)');
        $this->addSql('CREATE INDEX IDX_6B370A3364C19C1 ON flownative_bestbuyproducts_domain_model_product (category)');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('CREATE TABLE flownative_bestbuyproducts_domain_model_85f25_categorypath_join (bestbuyproducts_product VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, bestbuyproducts_category VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, INDEX IDX_1564EED490D97AEF (bestbuyproducts_product), INDEX IDX_1564EED49EDDAA03 (bestbuyproducts_category), PRIMARY KEY(bestbuyproducts_product, bestbuyproducts_category)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE flownative_bestbuyproducts_domain_model_85f25_categorypath_join ADD CONSTRAINT FK_1564EED490D97AEF FOREIGN KEY (bestbuyproducts_product) REFERENCES flownative_bestbuyproducts_domain_model_product (sku)');
        $this->addSql('ALTER TABLE flownative_bestbuyproducts_domain_model_85f25_categorypath_join ADD CONSTRAINT FK_1564EED49EDDAA03 FOREIGN KEY (bestbuyproducts_category) REFERENCES flownative_bestbuyproducts_domain_model_category (id)');
        $this->addSql('ALTER TABLE flownative_bestbuyproducts_domain_model_product DROP FOREIGN KEY FK_6B370A3364C19C1');
        $this->addSql('DROP INDEX IDX_6B370A3364C19C1 ON flownative_bestbuyproducts_domain_model_product');
        $this->addSql('ALTER TABLE flownative_bestbuyproducts_domain_model_product DROP category');
    }
}
