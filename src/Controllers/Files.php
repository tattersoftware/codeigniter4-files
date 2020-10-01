<?php namespace Tatter\Files\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Files\File;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Tatter\Files\Config\Files as FilesConfig;
use Tatter\Files\Exceptions\FilesException;
use Tatter\Files\Models\FileModel;

class Files extends Controller
{
	/**
	 * Files config.
	 *
	 * @var FilesConfig
	 */
	protected $config;

	/**
	 * The model to use, may be a child of this library's.
	 *
	 * @var FileModel
	 */
	protected $model;

	/**
	 * Helpers to load.
	 */
	protected $helpers = ['alerts', 'files', 'handlers', 'text'];

	/**
	 * Preloads the configuration and verifies the storage directory.
	 * Parameters are mostly for testing purposes.
	 *
	 * @param FilesConfig|null $config
	 * @param FileModel|null $model
	 *
	 * @throws FilesException
	 */
	public function __construct(FilesConfig $config = null, FileModel $model = null)
	{
		$this->config = $config ?? config('Files');

		// Use the short model name so a child may be loaded first
		$this->model = $model ?? model('FileModel');

		// Verify the storage directory
		if (! is_dir($this->config->storagePath) && ! @mkdir($this->config->storagePath, 0775, true))
		{
			throw FilesException::forDirFail($this->config->storagePath);
		}

		// Verify authentication is configured correctly
		// @see https://codeigniter4.github.io/CodeIgniter4/extending/authentication.html
		if (! function_exists('user_id') || ! empty($this->config->failNoAuth))
		{
			throw new FilesException(lang('Files.noAuth'));
		}		
	}

	/**
	 * Displays a list of all files. If global listing is not
	 * permitted then falls back to the user's files.
	 *
	 * @return RedirectResponse|string
	 */
	public function index()
	{
		// If global listing is denied then try for the user's files
		if (! $this->model->mayList())
		{
			return $this->user();
		}

		// Prep metadata
		$data = [
			'source'  => 'index',
			'sort'    => $this->getSort(),
			'order'   => $this->getOrder(),
			'format'  => $this->getFormat(),
			'search'  => $this->request->getVar('search'),
			'access'  => $this->model->mayAdmin() ? 'manage' : 'display',
			'exports' => $this->getExports(),
			'bulks'   => handlers()->where(['bulk' => 1])->findAll(),
		];

		// Get the files
		if ($data['search'])
		{
			$this->model->like('filename', $data['search']);
		}
		$data['files'] = $this->model->orderBy($data['sort'], $this->getOrder())->findAll();

		// AJAX calls skip the wrapping
		if ($this->request->isAJAX())
		{
			return view('Tatter\Files\Views\Formats\\' . $data['format'], $data);
		}

		return view('Tatter\Files\Views\index', $data);
	}

	/**
	 * Displays files for a user (defaults to the current user).
	 *
	 * @param string|integer|null $userId ID of the target user
	 *
	 * @return RedirectResponse|string
	 */
	public function user($userId = null)
	{
		$exports = new ExportModel();

		// Figure out user & access
		$userId = $userId ?? user_id() ?? 0;

		// Not logged in
		if (! $userId)
		{
			// Check for list permission
			if (! $this->model->mayList())
			{
				alert('warning', lang('Permits.notPermitted'));
				return redirect()->back();
			}

			$access   = 'display';
			$username = 'User';

			// Logged in, looking at another user
		}
		elseif ($userId !== $currentUser)
		{
			// Check for list permission
			if (! $this->model->mayList())
			{
				alert('warning', lang('Permits.notPermitted'));
				return redirect()->back();
			}

			$access   = $this->model->mayAdmin() ? 'manage' : 'display';
			$username = 'User';

			// Looking at own files
		}
		else
		{
			$access   = 'manage';
			$username = 'My';
		}

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
			return view("Tatter\Files\Views\Formats\\{$data['format']}", $data);
		}
		return view('Tatter\Files\Views\index', $data);
	}

	/**
	 * Lists selectable files for a form (AJAX).
	 *
	 * @param string|integer|null $userId Optional user to filter by
	 *
	 * @return string HTML view
	 */
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
		return view("Tatter\Files\Views\Formats\\select", $data);
	}

	/**
	 * Displays or processes the form to rename a file.
	 *
	 * @param string|null $fileId
	 *
	 * @return RedirectResponse|string
	 */
	public function rename($fileId = null)
	{
		// Load the request
		$fileId = $this->request->getGetPost('file_id') ?? $fileId;
		$file   = $this->model->find($fileId);

		// Handle missing info
		if (empty($file))
		{
			if ($this->request->isAJAX())
			{
				echo lang('Files.noFile');
				return '';
			}

			alert('warning', lang('Files.noFile'));
			return redirect()->back();
		}

		// Check for form submission
		if ($filename = $this->request->getGetPost('filename'))
		{
			// Update the name
			$file->filename = $filename;
			$this->model->save($file);

			// AJAX requests are blank on success
			if ($this->request->isAJAX())
			{
				return '';
			}

			// Set the message and return
			alert('success', lang('Files.renameSuccess', [$filename]));
			return redirect()->back();
		}

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

	/**
	 * Deletes a file.
	 *
	 * @param string $fileId
	 *
	 * @return RedirectResponse
	 */
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

	/**
	 * Handles bulk actions.
	 *
	 * @return RedirectResponse
	 */
	public function bulk(): RedirectResponse
	{
		// Load post data
		$post = $this->request->getPost();

		// Harvest file IDs and the requested action
		$action  = '';
		$fileIds = [];
		foreach ($post as $key => $value)
		{
			if (is_numeric($value))
			{
				$fileIds[] = $value;
			}
			else
			{
				$action = $key;
			}
		}

		// Make sure some files where checked
		if (empty($fileIds))
		{
			alert('warning', lang('File.nofile'));
			return redirect()->back();
		}

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
				if (empty($handler))
				{
					alert('warning', 'No handler found for ' . $action);
					return redirect()->back();
				}

				// Pass to the handler
				//$response = $handler->process($file->path, $file->filename);

				alert('success', 'Processed ' . count($fileIds) . ' files.');
		endswitch;

		return redirect()->back();
	}

	/**
	 * Receives uploads from Dropzone.
	 *
	 * @return ResponseInterface|string|null
	 */
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
		if ($this->request->getPost('chunkIndex') !== null)
		{
			// Gather chunk info
			$chunkIndex  = $this->request->getPost('chunkIndex');
			$totalChunks = $this->request->getPost('totalChunks');
			$uuid        = $this->request->getPost('uuid');

			// Check for chunk directory
			$chunkDir = WRITEPATH . 'uploads/' . $uuid;
			if (! is_dir($chunkDir) && ! mkdir($chunkDir, 0775, true))
			{
				throw FilesException::forChunkDirFail($chunkDir);
			}

			// Move the file
			$file->move($chunkDir, $chunkIndex . '.' . $file->getExtension());

			// Check for more chunks
			if ($chunkIndex < $totalChunks - 1)
			{
				return null;
			}

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
		}
		else
		{
			log_message('debug', 'New file upload: ' . $file->getClientName());

			// Gather file info
			$row = [
				'filename'   => $file->getClientName(),
				'localname'  => $file->getRandomName(),
				'clientname' => $file->getClientName(),
				'type'       => $file->getMimeType(),
				'size'       => $file->getSize(),
			];
		}

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
		if ($thumbnails->create($this->config->storagePath . $row['localname'], $tmpfile))
		{
			// Read in file binary data
			$handle = fopen($tmpfile, 'rb');
			$data   = fread($handle, filesize($tmpfile));
			fclose($handle);

			// Encode as base64 and add to the database
			$data = base64_encode($data);
			$this->model->update($fileId, ['thumbnail' => $data]);
		}
		else
		{
			$errors = implode('. ', $thumbnails->getErrors());
			log_message('debug', "Unable to create thumbnail for {$row['filename']}: {$errors}");
		}
		unlink($tmpfile);

		if (! $this->request->isAJAX())
		{
			alert('success', "Upload of {$row['filename']} successful.");
			return redirect()->back();
		}

		return '';
	}

	/**
	 * Handles failures.
	 *
	 * @return ResponseInterface
	 */
	protected function failure($errorCode, $errorMessage): ResponseInterface
	{
		log_message('debug', $errorMessage);

		if ($this->request->isAJAX())
		{
			$response = ['error' => $errorMessage];
			$this->response->setStatusCode($errorCode);
			return $this->response->setJSON($response);
		}
		else
		{
			alert('error', $errorMessage);
			return redirect()->back();
		}
	}

	/**
	 * Merges all chunks in a target directory into a single file, returns the file path.
	 *
	 * @return string
	 *
	 * @throws FilesException
	 */
	protected function mergeChunks($dir): string
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
		foreach ($chunks as $file)
		{
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
		}

		// close output handle
		fclose($output);

		return $tmpfile;
	}

	/**
	 * Processes Export requests.
	 *
	 * @param string         $slug   The slug to match to Exports attribute
	 * @param string|integer $fileId
	 *
	 * @return ResponseInterface
	 */
	public function export(string $slug, $fileId): ResponseInterface
	{
		// Match the export handler
		$exports = new ExportModel();
		$handler = $exports->where('slug', $slug)->first();
		if (empty($handler))
		{
			alert('warning', 'No handler found for ' . $slug);
			return redirect()->back();
		}

		// Load the file
		$file = $this->model->find($fileId);
		if (empty($file))
		{
			alert('warning', lang('Files.noFile'));
			return redirect()->back();
		}

		// Pass to the handler
		$response = $handler->process($file->path, $file->filename);

		// If the handler returned a response then we're done
		if ($response instanceof ResponseInterface)
		{
			return $response;
		}

		if ($response === true)
		{
			alert('success', lang('Files.noFile', [ucfirst($slug)]) );
		}
		elseif ($response === false)
		{
			$error = implode('. ', $handler->getErrors());
			alert('error', $error);
		}

		return redirect()->back();
	}

	/**
	 * Outputs a file thumbnail directly as image data.
	 *
	 * @param string|integer $fileId
	 *
	 * @return ResponseInterface
	 */
	public function thumbnail($fileId)
	{
		$file = $this->model->find($fileId);
		$data = $file->getThumbnail('raw');
		return $this->response->setHeader('Content-type', 'image/jpeg')->setBody($data);
	}

	//--------------------------------------------------------------------

	/**
	 * Determines the sort field.
	 *
	 * @return string
	 */
	protected function getSort(): string
	{
		// Check for a request, then load from Settings
		$sorts = [
			$this->request->getVar('sort'),
			service('settings')->filesSort,
		];

		foreach ($sorts as $sort)
		{
			// Validate
			if (in_array($sort, $this->model->allowedFields))
			{
				// Update user setting with the new preference
				service('settings')->filesSort = $sort;
				return $sort;
			}
		}

		return 'filename';
	}

	/**
	 * Determines the sort order.
	 *
	 * @return string
	 */
	protected function getOrder(): string
	{
		// Check for a request, then load from Settings
		$orders = [
			$this->request->getVar('order'),
			service('settings')->filesOrder,
		];

		foreach ($orders as $order)
		{
			$order = strtolower($order);

			// Validate
			if (in_array($order, ['asc', 'desc']))
			{
				// Update user setting with the new preference
				service('settings')->filesOrder = $order;
				return $order;
			}
		}

		return 'asc';
	}

	/**
	 * Determines the display format.
	 *
	 * @return string
	 */
	protected function getFormat(): string
	{
		// Check for a request, then load from Settings, fallback to the config default
		$formats = [
			$this->request->getVar('format'),
			service('settings')->filesFormat,
			$this->config->defaultFormat,
		];

		foreach ($formats as $format)
		{
			// Validate
			if (in_array($format, ['cards', 'list', 'select']))
			{
				// Update user setting with the new preference
				service('settings')->filesFormat = $format;
				return $format;
			}
		}

		return 'cards';
	}

	/**
	 * Gets Export handlers indexed by the extension they support.
	 *
	 * @return array<string, array>
	 */
	protected function getExports(): array
	{
		$exports = [];
		foreach (handlers('Exports') as $class)
		{
			$attributes = handlers()->attributes($class);

			// Add the class name for easy access later
			$attributes['class'] = $class;

			foreach (explode(',', $attributes['extension']) as $extension)
			{
				$exports[$extension][] = $attributes;
			}
		}

		return $exports;
	}
}
