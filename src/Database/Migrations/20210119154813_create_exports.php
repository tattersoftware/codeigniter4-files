<?php namespace Tatter\Files\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExports extends Migration
{
	public function up()
	{
		// Remove the unused downloads table
		$this->forge->dropTable('downloads');

		// Create the exports table
		$fields = [
			'handler'    => ['type' => 'varchar', 'constraint' => 63],
			'file_id'    => ['type' => 'int', 'unsigned' => true],
			'user_id'    => ['type' => 'int', 'unsigned' => true, 'null' => true],
			'created_at' => ['type' => 'datetime', 'null' => true],
		];

		$this->forge->addField('id');
		$this->forge->addField($fields);

		$this->forge->addKey('handler');
		$this->forge->addKey('file_id');
		$this->forge->addKey('user_id');

		$this->forge->createTable('exports');
	}

	public function down()
	{
		// Restore downloads table
		$fields = [
			'file_id'    => ['type' => 'int', 'unsigned' => true],
			'user_id'    => ['type' => 'int', 'unsigned' => true],
			'created_at' => ['type' => 'datetime', 'null' => true],
		];

		$this->forge->addField('id');
		$this->forge->addField($fields);

		$this->forge->addKey(['file_id', 'user_id']);
		$this->forge->addKey(['user_id', 'file_id']);

		$this->forge->createTable('downloads');
	}
}
