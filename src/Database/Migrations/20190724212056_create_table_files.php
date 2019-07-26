<?php namespace Tatter\Files\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_create_table_files extends Migration
{
	public function up()
	{
		// files
		$fields = [
			'filename'      => ['type' => 'VARCHAR', 'constraint' => 255],
			'localname'     => ['type' => 'VARCHAR', 'constraint' => 255],
			'clientname'    => ['type' => 'VARCHAR', 'constraint' => 255],
			'type'          => ['type' => 'VARCHAR', 'constraint' => 255],
			'size'          => ['type' => 'INT', 'unsigned' => true],
			'thumbnail'     => ['type' => 'LONGBLOB', null => true],
			'created_at'    => ['type' => 'DATETIME', 'null' => true],
			'updated_at'    => ['type' => 'DATETIME', 'null' => true],
			'deleted_at'    => ['type' => 'DATETIME', 'null' => true],
		];
		
		$this->forge->addField('id');
		$this->forge->addField($fields);

		$this->forge->addKey('filename');
		$this->forge->addKey('created_at');
		
		$this->forge->createTable('files');
		

		/*** Pivot tables ***/
		
		// files_users
		$fields = [
			'file_id'       => ['type' => 'INT', 'unsigned' => true],
			'user_id'       => ['type' => 'INT', 'unsigned' => true],
			'created_at'    => ['type' => 'DATETIME', 'null' => true],
		];
		
		$this->forge->addField('id');
		$this->forge->addField($fields);

		$this->forge->addUniqueKey(['file_id', 'user_id']);
		$this->forge->addUniqueKey(['user_id', 'file_id']);
		
		$this->forge->createTable('files_users');
		
		// downloads
		$fields = [
			'file_id'       => ['type' => 'INT', 'unsigned' => true],
			'user_id'       => ['type' => 'INT', 'unsigned' => true],
			'created_at'    => ['type' => 'DATETIME', 'null' => true],
		];
		
		$this->forge->addField('id');
		$this->forge->addField($fields);

		$this->forge->addKey(['file_id', 'user_id']);
		$this->forge->addKey(['user_id', 'file_id']);
		
		$this->forge->createTable('downloads');
	}

	public function down()
	{
		$this->forge->dropTable('files');
		$this->forge->dropTable('files_users');
		$this->forge->dropTable('downloads');
	}
}
