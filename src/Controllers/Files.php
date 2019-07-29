<?php namespace Tatter\Files\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Files\File;
use Tatter\Files\Exceptions\FilesException;
use Tatter\Files\Models\FileModel;

class Files extends Controller
{
	protected $helpers = ['alerts', 'files', 'text'];

	public function __construct()
	{		
		// Preload the model & config
		$this->model  = new FileModel();
		$this->config = config('Files');
	}
	
	// Displays a list all files
	public function index($format = 'cards')
	{
		// Check for list permission
		if (! $this->model->mayList()):
			alert('warning', lang('Permits.notPermitted'));
			return redirect()->back();
		endif;
		
		$files = $this->model->orderBy('filename')->findAll();
		$format = in_array($format, ['cards', 'list']) ? $format : 'cards';
		$access = $this->model->mayManage() ? 'manage' : 'display';
		
		return view("Tatter\Files\Views\\{$format}\\{$access}", ['config' => $this->config, 'files' => $files]);
	}
	
	// Displays files for a user (defaults to the current user)
	public function user($userId = null, $format = 'card')
	{
		$userId = $userId ?? session($this->config->userSource) ?? 0;
		$files = $this->model->getForUser($userId);
		return view("Tatter\Files\Views\\{$format}\\{$access}", ['config' => $this->config, 'files' => $files]);
	}
	
	// Receives uploads from Dropzone
	public function upload()
	{
		// Verify upload succeeded
		$file = $this->request->getFile('file');
		if (empty($file))
			return $this->failure(400, 'No file supplied.');
		if (! $file->isValid())
			return ($file->getErrorString() . '(' . $file->getError() . ')');

		// Check for chunks
		if ($this->request->getPost('chunkIndex') !== null):
		
			// Gather chunk info
			$chunkIndex = $this->request->getPost('chunkIndex');
			$totalChunks = $this->request->getPost('totalChunks');
			$uuid = $this->request->getPost('uuid');
			
			// Check for chunk directory
			$dir = WRITEPATH . 'uploads/chunks/' . $uuid;
			if (! is_dir($dir)):
				if (! mkdir($dir, 0775, true)):
					throw FilesException::forChunkDirFail($dir);
				endif;
			endif;
			
			// Move the file
			$file->move($dir, $chunkIndex . '.' . $file->getExtension());
			
			// Check for more chunks
			if ($chunkIndex < $totalChunks-1):
				return;
			endif;
			
			// Save client name from last chunk
			$clientname = $file->getClientName();
			
			// Merge the chunks
			$path = $this->mergeChunks($dir);
			$file = new File($path);
			
			// Gather merged file data
			$row = [
				'filename'   => $clientname,
				'localname'  => $file->getRandomName(),
				'clientname' => $clientname,
				'type'       => $file->getMimeType(),
				'size'       => $file->getSize(),
			];

		// No chunks, handle as a straight upload
		else:
			log_message('debug', 'New file upload: ' . $file->getClientName());

			// Gather file info
			$row = [
				'filename'   => $file->getClientName(),
				'localname'  => $file->getRandomName(),
				'clientname' => $file->getClientName(),
				'type'       => $file->getMimeType(),
				'size'       => $file->getSize(),
			];
		endif;
		
		// Move the file
		$file->move(WRITEPATH . 'uploads/files', $row['filename']);
		chmod(WRITEPATH . 'uploads/files/' . $row['filename'], 0664); // WIP

		// Record in the database
		$fileId = $this->model->insert($row);
		
		// Associate with the current user
		$userId = $userId ?? session($this->config->userSource) ?? 0;
		if ($userId)
			$this->model->addToUser($fileId, $userId);
	}
	
	protected function failure($errorCode, $errorMessage)
	{
		log_message('debug', $errorMessage);
		
		if ($this->request->isAJAX()):
			$response = ['error' => $errorMessage];
			$this->response->setStatusCode($errorCode);
			return $this->response->setJSON($response);
		else:
			alert('error', $errorMessage);
			return redirect()->back();
		endif;
	}
	
	// Merges all chunks in a target directory into a single file, returns the file path
	protected function mergeChunks($dir)
	{
		helper('filesystem');
		helper('text');
		
		// Get chunks from target directory
		$chunks = get_filenames($dir, true);
		if (empty($chunks))
			throw FilesException::forNoChunks($dir);
		
		// Create the temp file
		$tmpfile = tempnam(sys_get_temp_dir(), random_string());
		log_message('debug', 'Merging ' . count($chunks) . ' chunks to ' . $tmpfile);

		// Open temp file for writing
		$output = @fopen($tmpfile, 'ab');
		if (! $output)
			throw FilesException::forNewFileFail($tmpfile);
		
		// Write each chunk to the temp file
		foreach ($chunks as $file):
			$input = @fopen($file, 'rb');
			if (! $input)
				throw FilesException::forWriteFileFail($tmpfile);
			
			// Buffered merge of chunk
			while ($buffer = fread($input, 4096))
				fwrite($output, $buffer);
			
			fclose($input);
		endforeach;
		
		// close output handle
		fclose($output);

		return $tmpfile;
	}
	
	// Output a file's thumbnail directly as image data
	public function thumbnail($fileId)
	{
		$file = $this->model->find($fileId);

		if (empty($file->thumbnail)):
			$locator = service('locator');
			$path = $locator->locateFile('\Tatter\Files\Assets\Unavailable.png', null, 'png');
			$handle = fopen($path, "rb");
			$data = fread($handle, filesize($path));
			fclose($handle);
			return $this->response->setHeader('Content-type', 'image/png')->setBody($data);
		endif;
		
		//$this->response->setHeader('Content-type', 'image/png')->setBody($body);
		//$file = FCPATH . '/assets/img/hammock.png';
		//readfile($file);
		//$data = base64_decode($this->unavailable());
		//return $this->response->setHeader('Content-type', 'image/png')->setBody($data);
	}
	
}
