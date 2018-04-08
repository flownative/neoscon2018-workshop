<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 *
 */
class Version20180406200301 extends AbstractMigration
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

        $this->addSql('CREATE TABLE flownative_bestbuyproducts_domain_model_category (id VARCHAR(255) NOT NULL, parent VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_1143DA8C3D8E604F (parent), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE flownative_bestbuyproducts_domain_model_product (sku VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, active TINYINT(1) NOT NULL, regularprice INT NOT NULL, relatedproducts LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', shortdescription LONGTEXT NOT NULL, manufacturer VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, modelnumber VARCHAR(255) NOT NULL, PRIMARY KEY(sku)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE flownative_bestbuyproducts_domain_model_85f25_categorypath_join (bestbuyproducts_product VARCHAR(255) NOT NULL, bestbuyproducts_category VARCHAR(255) NOT NULL, INDEX IDX_1564EED490D97AEF (bestbuyproducts_product), INDEX IDX_1564EED49EDDAA03 (bestbuyproducts_category), PRIMARY KEY(bestbuyproducts_product, bestbuyproducts_category)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE flownative_bestbuyproducts_domain_model_category ADD CONSTRAINT FK_1143DA8C3D8E604F FOREIGN KEY (parent) REFERENCES flownative_bestbuyproducts_domain_model_category (id)');
        $this->addSql('ALTER TABLE flownative_bestbuyproducts_domain_model_85f25_categorypath_join ADD CONSTRAINT FK_1564EED490D97AEF FOREIGN KEY (bestbuyproducts_product) REFERENCES flownative_bestbuyproducts_domain_model_product (sku)');
        $this->addSql('ALTER TABLE flownative_bestbuyproducts_domain_model_85f25_categorypath_join ADD CONSTRAINT FK_1564EED49EDDAA03 FOREIGN KEY (bestbuyproducts_category) REFERENCES flownative_bestbuyproducts_domain_model_category (id)');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('ALTER TABLE flownative_bestbuyproducts_domain_model_category DROP FOREIGN KEY FK_1143DA8C3D8E604F');
        $this->addSql('ALTER TABLE flownative_bestbuyproducts_domain_model_85f25_categorypath_join DROP FOREIGN KEY FK_1564EED49EDDAA03');
        $this->addSql('ALTER TABLE flownative_bestbuyproducts_domain_model_85f25_categorypath_join DROP FOREIGN KEY FK_1564EED490D97AEF');
        $this->addSql('DROP TABLE flownative_bestbuyproducts_domain_model_category');
        $this->addSql('DROP TABLE flownative_bestbuyproducts_domain_model_product');
        $this->addSql('DROP TABLE flownative_bestbuyproducts_domain_model_85f25_categorypath_join');
    }
}
