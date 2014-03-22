<?php
namespace TYPO3\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
	Doctrine\DBAL\Schema\Schema;

/**
 * Filesize and last modified for Resource instances
 */
class Version20130614125212 extends AbstractMigration {

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function up(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("ALTER TABLE typo3_flow_resource_resource ADD filesize INT NOT NULL");
		$this->addSql("ALTER TABLE typo3_flow_resource_resource ADD lastmodified DATETIME NOT NULL");
	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("ALTER TABLE typo3_flow_resource_resource DROP lastmodified");
		$this->addSql("ALTER TABLE typo3_flow_resource_resource DROP filesize");
	}
}

?>