<?php namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use Tatter\Files\Controllers\Files;

/**
 * A simple class to test extended Controllers.
 */
class Widgets extends Files
{
	/**
	 * Displays a list of odd files.
	 *
	 * @return RedirectResponse|string
	 */
	public function files()
	{
		return '';
	}
}
