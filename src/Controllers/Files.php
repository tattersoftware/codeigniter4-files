<?php namespace Tatter\Files\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Files\File;
use CodeIgniter\HTTP\ResponseInterface;
use Tatter\Exports\Models\ExportModel;
use Tatter\Files\Exceptions\FilesException;
use Tatter\Files\Models\FileModel;

class Files extends Controller
{
	protected $helpers = [
		'alerts',
		'files',
		'text',
	];

	public function __construct()
	{
				// Preload the model & config
		$this->model  = new FileModel();
		$this->config = config('Files');

		// Verify the storage directory
		if (! is_dir($this->config->storagePath) && ! mkdir($this->config->storagePath, 0775, true))
		{
			throw FilesException::forDirFail($this->config->storagePath);
		}
	}

	// Displays a list of all files
	public function index()
	{
		$exports = new ExportModel();

		// If global listing is denied then try for the user's files
		if (! $this->model->mayList())
		{
			return $this->user();
		}

		// Check for universal write permission
		if ($userId = session($this->config->userSource))
		{
			$access = $this->model->mayAdmin() ? 'manage' : 'display';
		}
		else
		{
			$access = 'display';
		}

		// Load data
		$data = [
			'config'  => $this->config,
			'files'   => $this->model->orderBy('filename')->findAll(),
			'source'  => 'index',
			'format'  => $this->getFormat(),
			'access'  => $access,
			'exports' => $exports->getByExtensions(),
			'bulks'   => $exports->where('bulk', 1)->findAll(),
		];

		// AJAX calls skip the wrapping
		if ($this->request->isAJAX())
		{
			return view("Tatter\Files\Views\\formats\\{$data['format']}", $data);
		}
		return view('Tatter\Files\Views\index', $data);
	}

	// Displays files for a user (defaults to the current user)
	public function user($userId = null)
	{
		$exports = new ExportModel();

		// Figure out user & access
		$currentUser = session($this->config->userSource);
		$userId      = $userId ?? $currentUser ?? 0;

		// Not logged in
		if (! $userId):
			// Check for list permission
			if (! $this->model->mayList()):
				alert('warning', lang('Permits.notPermitted'));
				return redirect()->back();
			endif;

			$access   = 'display';
			$username = 'User';

			// Logged in, looking at another user
		elseif ($userId !== $currentUser):
			// Check for list permission
			if (! $this->model->mayList()):
				alert('warning', lang('Permits.notPermitted'));
				return redirect()->back();
			endif;

			$access   = $this->model->mayAdmin() ? 'manage' : 'display';
			$username = 'User';

			// Looking at own files
		else:
			$access   = 'manage';
			$username = 'My';
		endif;

		// Load data
		$data = [
			'config'   => $this->config,
			'files'    => $this->model->getForUser($userId),
			'source'   => 'user/' . $userId,
			'format'   => $this->getFormat(),
			'access'   => $access,
			'username' => $username,
			'exports'  => $exports->getByExtensions(),
			'bulks'    => $exports->where('bulk', 1)->findAll(),
		];

		// AJAX calls skip the wrapping
		if ($this->request->isAJAX())
		{
			return view("Tatter\Files\Views\\formats\\{$data['format']}", $data);
		}
		return view('Tatter\Files\Views\index', $data);
	}

	// Determine the correct display format
	protected function getFormat(): string
	{
				$settings = service('settings');

		// Check for a reformat request, then load from settings, fallback to the config default
		$format = $this->request->getGetPost('format') ?? $settings->filesFormat ?? $this->config->defaultFormat;

		// Validate the determined format
		$format = in_array($format, ['cards', 'list', 'select']) ? $format : 'cards';

		// Upate user setting with the new preference
		$settings->filesFormat = $format;

		return $format;
	}

	// AJAX list of selectable files for form; optional user filter
	public function select($userId = null): string
	{
		// Figure out user & access
		$currentUser = session($this->config->userSource);
		// If no user or other user then check for list permission
		if ((empty($userId) || $userId !== $currentUser) && ! $this->model->mayList())
		{
			return lang('Permits.notPermitted');
		}

		// Filter for user files
		$files = empty($userId) ? $this->model->orderBy('filename')->findAll() : $this->model->getForUser($userId);

		$data = [
			'config' => $this->config,
			'files'  => $files,
		];
		return view("Tatter\Files\Views\\formats\\select", $data);
	}

	// Display or process the form to rename a file
	public function rename($fileId = null)
	{
		// Load the request
		$fileId = $this->request->getGetPost('file_id') ?? $fileId;
		$file   = $this->model->find($fileId);

		// Handle missing info
		if (empty($file)):
			if ($this->request->isAJAX()):
				echo lang('Files.noFile');
				return;
			endif;

			alert('warning', lang('Files.noFile'));
			return redirect()->back();
		endif;

		// Check for form submission
		if ($filename = $this->request->getGetPost('filename')):
			// Update the name
			$file->filename = $filename;
			$this->model->save($file);

			// AJAX requests are blank on success
			if ($this->request->isAJAX()):
				return;
			endif;

			// Set the message and return
			alert('success', lang('Files.renameSuccess', [$filename]));
			return redirect()->back();
		endif;

		$data = [
			'config' => $this->config,
			'file'   => $file,
		];

		// Display only the form for AJAX
		if ($this->request->isAJAX())
		{
			return view('Tatter\Files\Views\forms\rename', $data);
		}

		// Display the form
		return view('Tatter\Files\Views\rename', $data);
	}

	// Delete a file
	public function delete($fileId)
	{
		$file = $this->model->find($fileId);
		if (empty($file))
		{
			return redirect()->back();
		}

		$this->model->delete($fileId);
		alert('success', 'Deleted ' . $file->filename);
		return redirect()->back();
	}

	// Handle bulk actions
	public function bulk()
	{
		// Load post data
		$post = $this->request->getPost();

		// Harvest file IDs and the requested action
		$action  = '';
		$fileIds = [];
		foreach ($post as $key => $value):
			if (is_numeric($value)):
				$fileIds[] = $value;
			else:
				$action = $key;
			endif;
		endforeach;

		// Make sure some files where checked
		if (empty($fileIds)):
			alert('warning', lang('File.nofile'));
			return redirect()->back();
		endif;

		// Handle actions
		switch ($action):
			case '':
				alert('warning', 'No valid action.');
			break;

			// Bulk delete request
			case 'delete':
				$this->model->delete($fileIds);
				alert('success', 'Deleted ' . count($fileIds) . ' files.');
			break;

			default:
				// Match the export handler
				$exports = new ExportModel();
				$handler = $exports->where('uid', $action)->first();
				if (empty($handler)):
					alert('warning', 'No handler found for ' . $uid);
					return redirect()->back();
				endif;

				// Pass to the handler
				//$response = $handler->process($file->path, $file->filename);

				alert('success', 'Processed ' . count($fileIds) . ' files.');
		endswitch;

		return redirect()->back();
	}

	// Receives uploads from Dropzone
	public function upload()
	{
		// Verify upload succeeded
		$file = $this->request->getFile('file');
		if (empty($file))
		{
			return $this->failure(400, 'No file supplied.');
		}
		if (! $file->isValid())
		{
			return ($file->getErrorString() . '(' . $file->getError() . ')');
		}

		// Check for chunks
		if ($this->request->getPost('chunkIndex') !== null):

			// Gather chunk info
			$chunkIndex  = $this->request->getPost('chunkIndex');
			$totalChunks = $this->request->getPost('totalChunks');
			$uuid        = $this->request->getPost('uuid');

			// Check for chunk directory
			$chunkDir = WRITEPATH . 'uploads/' . $uuid;
			if (! is_dir($chunkDir) && ! mkdir($chunkDir, 0775, true)):
				throw FilesException::forChunkDirFail($chunkDir);
			endif;

			// Move the file
			$file->move($chunkDir, $chunkIndex . '.' . $file->getExtension());

			// Check for more chunks
			if ($chunkIndex < $totalChunks - 1):
				return;
			endif;

			// Save client name from last chunk
			$clientname = $file->getClientName();

			// Merge the chunks
			$path = $this->mergeChunks($chunkDir);
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
		$file->move($this->config->storagePath, $row['localname']);
		chmod($this->config->storagePath . $row['localname'], 0664); // WIP

		// Record in the database
		$fileId = $this->model->insert($row);

		// Associate with the current user
		$userId = $userId ?? session($this->config->userSource) ?? 0;
		if ($userId)
		{
			$this->model->addToUser($fileId, $userId);
		}

		// Try to create a thumbnail
		$thumbnails = service('thumbnails');
		$tmpfile    = tempnam(sys_get_temp_dir(), random_string());
		if ($thumbnails->create($this->config->storagePath . $row['localname'], $tmpfile)):
			// Read in file binary data
			$handle = fopen($tmpfile, 'rb');
			$data   = fread($handle, filesize($tmpfile));
			fclose($handle);

			// Encode as base64 and add to the database
			$data = base64_encode($data);
			$this->model->update($fileId, ['thumbnail' => $data]);
		else:
			$errors = implode('. ', $thumbnails->getErrors());
			log_message('debug', "Unable to create thumbnail for {$row['filename']}: {$errors}");
		endif;
		unlink($tmpfile);

		if (! $this->request->isAJAX()):
			set_message('success', "Upload of {$row['filename']} successful.");
			return redirect()->back();
		endif;
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
		{
			throw FilesException::forNoChunks($dir);
		}

		// Create the temp file
		$tmpfile = tempnam(sys_get_temp_dir(), random_string());
		log_message('debug', 'Merging ' . count($chunks) . ' chunks to ' . $tmpfile);

		// Open temp file for writing
		$output = @fopen($tmpfile, 'ab');
		if (! $output)
		{
			throw FilesException::forNewFileFail($tmpfile);
		}

		// Write each chunk to the temp file
		foreach ($chunks as $file):
			$input = @fopen($file, 'rb');
			if (! $input)
			{
				throw FilesException::forWriteFileFail($tmpfile);
			}

			// Buffered merge of chunk
			while ($buffer = fread($input, 4096))
			{
				fwrite($output, $buffer);
			}

			fclose($input);
		endforeach;

		// close output handle
		fclose($output);

		return $tmpfile;
	}

	// Process an export request
	public function export($uid, $fileId)
	{
		// Match the export handler
		$exports = new ExportModel();
		$handler = $exports->where('uid', $uid)->first();
		if (empty($handler)):
			alert('warning', 'No handler found for ' . $uid);
			return redirect()->back();
		endif;

		// Load the file
		$file = $this->model->find($fileId);
		if (empty($file)):
			alert('warning', lang('Files.noFile'));
			return redirect()->back();
		endif;

		// Pass to the handler
		$response = $handler->process($file->path, $file->filename);

		// If the handler returned a response then we're done
		if ($response instanceof ResponseInterface)
		{
			return $response;
		}

		if ($response === true):
			alert('success', lang('Files.noFile', [ucfirst($uid)]) );
		elseif ($response === false):
			$error = implode('. ', $handler->getErrors());
			alert('error', $error);
		endif;

		return redirect()->back();
	}

	// Output a file's thumbnail directly as image data
	public function thumbnail($fileId)
	{
		$file = $this->model->find($fileId);
		$data = $file->getThumbnail('raw');
		return $this->response->setHeader('Content-type', 'image/jpeg')->setBody($data);
	}
}
