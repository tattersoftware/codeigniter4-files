<?php namespace Tests\Support;

use CodeIgniter\Test\CIDatabaseTestCase;

class FilesTestCase extends CIDatabaseTestCase
{
	/**
	 * Should the database be refreshed before each test?
	 *
	 * @var boolean
	 */
	protected $refresh = true;

	/**
	 * The namespace to help us find the migration classes.
	 *
	 * @var string
	 */
	protected $namespace = 'Tatter\Files';
}
