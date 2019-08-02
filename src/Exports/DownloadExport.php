<?php namespace Tatter\Files\Thumbnails;

use Config\Services;
use Tatter\Files\Interfaces\ExportInterface;

class DownloadExport implements ExportInterface
{
	public $definition = [
		'category' => 'Core',
		'name'     => 'Download',
		'uid'      => 'info',
		'icon'     => 'fas fa-file-download',
		'summary'  => 'Download a file straight from the browser',
	];
	public $extensions = ['*'];
	
	// Open the file and read data to browser
	public function process(string $path)
	{		
		$file = $this->model->find($fileId);
		$data = $file->getThumbnail('raw');
		return $this->response->setHeader('Content-type', 'image/jpeg')->setBody($data);
		readfile($path);
	}
}
