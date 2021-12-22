<?php

namespace Tatter\Files\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Tatter\Exports\Exceptions\ExportsException;
use Tatter\Files\Config\Files as FilesConfig;
use Tatter\Files\Entities\File;
use Tatter\Files\Exceptions\FilesException;
use Tatter\Files\Models\ExportModel;
use Tatter\Files\Models\FileModel;
use Throwable;

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
    protected $helpers = ['alerts', 'files', 'handlers', 'html', 'preferences', 'text'];

    /**
     * Overriding data for views.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Preloads the configuration and model.
     * Parameters are mostly for testing purposes.
     */
    public function __construct(?FilesConfig $config = null, ?FileModel $model = null)
    {
        $this->config = $config ?? config('Files');
        $this->model  = $model ?? model(FileModel::class); // @phpstan-ignore-line
    }

    //--------------------------------------------------------------------

    /**
     * Handles the final display of files based on $data.
     */
    public function display(): string
    {
        // Apply any defaults for missing metadata
        $this->setDefaults();

        // Get the Files
        if (! isset($this->data['files'])) {
            // Apply a target user
            if ($this->data['userId']) {
                $this->model->whereUser($this->data['userId']);
            }

            // Apply any requested search filters
            if ($this->data['search']) {
                $this->model->like('filename', $this->data['search']);
            }

            // Sort and order
            $this->model->orderBy($this->data['sort'], $this->data['order']);

            // Paginate non-select formats
            if ($this->data['format'] !== 'select') {
                $this->setData([
                    'files' => $this->model->paginate($this->data['perPage'], 'default', $this->data['page']),
                    'pager' => $this->model->pager,
                ], true);
            } else {
                $this->setData([
                    'files' => $this->model->findAll(),
                ], true);
            }
        }

        // AJAX calls skip the wrapping
        if ($this->data['ajax']) {
            return view('Tatter\Files\Views\Formats\\' . $this->data['format'], $this->data);
        }

        return view('Tatter\Files\Views\index', $this->data);
    }

    //--------------------------------------------------------------------

    /**
     * Lists of files; if global listing is not permitted then
     * falls back to user().
     *
     * @return RedirectResponse|string
     */
    public function index()
    {
        // Check for list permission
        if (! $this->model->mayList()) {
            return $this->user();
        }

        return $this->display();
    }

    /**
     * Filters files for a user (defaults to the current user).
     *
     * @param int|string|null $userId ID of the target user
     *
     * @return ResponseInterface|ResponseInterface|string
     */
    public function user($userId = null)
    {
        // Figure out user & access
        $userId = $userId ?? user_id();

        // Not logged in
        if ($userId === null) {
            // Check for list permission
            if (! $this->model->mayList()) {
                return $this->failure(403, lang('Permits.notPermitted'));
            }

            $this->setData([
                'access'   => 'display',
                'title'    => 'All Files',
                'username' => '',
            ]);
        }
        // Logged in, looking at another user
        elseif ($userId !== user_id()) {
            // Check for list permission
            if (! $this->model->mayList()) {
                return $this->failure(403, lang('Permits.notPermitted'));
            }

            $this->setData([
                'access'   => $this->model->mayAdmin() ? 'manage' : 'display',
                'title'    => 'User Files',
                'username' => 'User',
            ]);
        }
        // Looking at own files
        else {
            $this->setData([
                'access'   => 'manage',
                'title'    => 'My Files',
                'username' => 'My',
            ]);
        }

        $this->setData([
            'userId' => $userId,
            'source' => 'user/' . $userId,
        ]);

        return $this->display();
    }

    //--------------------------------------------------------------------

    /**
     * Display the Dropzone uploader.
     *
     * @return ResponseInterface|string
     */
    public function new()
    {
        // Check for create permission
        if (! $this->model->mayCreate()) {
            return $this->failure(403, lang('Permits.notPermitted'));
        }

        return view('Tatter\Files\Views\new');
    }

    /**
     * Displays or processes the form to rename a file.
     *
     * @param string|null $fileId
     *
     * @return ResponseInterface|string
     */
    public function rename($fileId = null)
    {
        // Load the request
        $fileId = $this->request->getGetPost('file_id') ?? $fileId;
        $file   = $this->model->find($fileId);

        // Handle missing info
        if (empty($file)) {
            return $this->failure(400, lang('Files.noFile'));
        }

        // Check for form submission
        if ($filename = $this->request->getGetPost('filename')) {
            // Update the name
            $file->filename = $filename;
            $this->model->save($file);

            // AJAX requests are blank on success
            return $this->request->isAJAX()
                ? ''
                : redirect()->back()->with('message', lang('Files.renameSuccess', [$filename]));
        }

        // AJAX skips the wrapper
        return view(
            $this->request->isAJAX() ? 'Tatter\Files\Views\Forms\rename' : 'Tatter\Files\Views\rename',
            [
                'config' => $this->config,
                'file'   => $file,
            ]
        );
    }

    /**
     * Deletes a file.
     *
     * @param string $fileId
     *
     * @return ResponseInterface
     */
    public function delete($fileId)
    {
        $file = $this->model->find($fileId);
        if (empty($file)) {
            return $this->failure(400, lang('Files.noFile'));
        }
        if (! $this->model->mayDelete($file)) {
            return $this->failure(403, lang('Permits.notPermitted'));
        }

        if ($this->model->delete($fileId)) {
            return redirect()->back()->with('message', lang('Files.deleteSuccess'));
        }

        return $this->failure(400, implode('. ', $this->model->errors()));
    }

    /**
     * Handles bulk actions.
     */
    public function bulk(): ResponseInterface
    {
        // Load post data
        $post = $this->request->getPost();

        // Harvest file IDs and the requested action
        $action  = '';
        $fileIds = [];

        foreach ($post as $key => $value) {
            if (is_numeric($value)) {
                $fileIds[] = $value;
            } else {
                $action = $key;
            }
        }

        // Make sure some files where checked
        if (empty($fileIds)) {
            return $this->failure(400, lang('Files.noFile'));
        }

        // Handle actions
        if (empty($action)) {
            return $this->failure(400, 'No valid action');
        }

        // Bulk delete request
        if ($action === 'delete') {
            $this->model->delete($fileIds);

            return redirect()->back()->with('success', 'Deleted ' . count($fileIds) . ' files.');
        }

        // Bulk export of some kind, match the handler
        if (! $handler = handlers('Exports')->where(['slug' => $action])->first()) {
            return $this->failure(400, 'No handler found for ' . $action);
        }

        $export = new $handler();

        foreach ($fileIds as $fileId) {
            if ($file = $this->model->find($fileId)) {
                $export->setFile($file->object->setBasename($file->filename));
            }
        }

        try {
            $result = $export->process();
        } catch (ExportsException $e) {
            return $this->failure(400, $e->getMessage());
        }

        alert('success', 'Processed ' . count($fileIds) . ' files.');

        return $result;
    }

    /**
     * Receives uploads from Dropzone.
     *
     * @return ResponseInterface|string
     */
    public function upload()
    {
        // Check for create permission
        if (! $this->model->mayCreate()) {
            return $this->failure(403, lang('Permits.notPermitted'));
        }

        // Verify upload succeeded
        $upload = $this->request->getFile('file');
        if (empty($upload)) {
            return $this->failure(400, lang('Files.noFile'));
        }
        if (! $upload->isValid()) {
            return $upload->getErrorString() . '(' . $upload->getError() . ')';
        }

        // Check for chunks
        if ($this->request->getPost('chunkIndex') !== null) {
            // Gather chunk info
            $chunkIndex  = $this->request->getPost('chunkIndex');
            $totalChunks = $this->request->getPost('totalChunks');
            $uuid        = $this->request->getPost('uuid');

            // Check for chunk directory
            $chunkDir = WRITEPATH . 'uploads/' . $uuid;
            if (! is_dir($chunkDir) && ! mkdir($chunkDir, 0775, true)) {
                return $this->failure(400, lang('Files.chunkDirFail', [$chunkDir]));
            }

            // Move the file
            try {
                $upload->move($chunkDir, $chunkIndex . '.' . $upload->getExtension());
            } catch (HTTPException $e) {
                log_message('error', $e->getMessage());

                return $this->failure(400, $e->getMessage());
            }

            // Check for more chunks
            if ($chunkIndex < $totalChunks - 1) {
                session_write_close();

                return '';
            }

      		// Get chunks from target directory
			helper('filesystem');
			$chunks = get_filenames($chunkDir, true);
			if (empty($chunks)) {
				throw FilesException::forNoChunks($chunkDir);
			}

            // Merge the chunks
            try {
                $path = merge_file_chunks(...$chunks);
            } catch (Throwable $e) {
                log_message('error', $e->getMessage());

                return $this->failure(400, $e->getMessage());
            }

	        log_message('debug', 'Merged ' . count($chunks) . ' chunks to ' . $path);
        }

        // Get additional post data to pass to model
        $data               = $this->request->getPost();
        $data['filename']   = $data['filename'] ?? $upload->getClientName();
        $data['clientname'] = $data['clientname'] ?? $upload->getClientName();

        // Accept the file
        $file = $this->model->createFromPath($path ?? $upload->getRealPath(), $data);

        // Trigger the Event with the new File
        Events::trigger('upload', $file);

        if ($this->request->isAJAX()) {
            session_write_close();

            return '';
        }

        return redirect()->back()->with('message', lang('File.uploadSucces', [$file->clientname]));
    }

    /**
     * Processes Export requests.
     *
     * @param string     $slug   The slug to match to Exports attribute
     * @param int|string $fileId
     */
    public function export(string $slug, $fileId): ResponseInterface
    {
        // Match the export handler
        $handler = handlers('Exports')->where(['slug' => $slug])->first();
        if (empty($handler)) {
            alert('warning', 'No handler found for ' . $slug);

            return redirect()->back();
        }

        // Load the file
        $file = $this->model->find($fileId);
        if (empty($file)) {
            alert('warning', lang('Files.noFile'));

            return redirect()->back();
        }

        // Verify the file exists
        if (! $fileObject = $file->getObject()) {
            log_message('error', lang('Files.fileNotFound', [$file->getPath()]));
            alert('warning', lang('Files.fileNotFound', [$file->filename]));

            return redirect()->back();
        }

        // Create the record
        model(ExportModel::class)->insert([
            'handler' => $slug,
            'file_id' => $file->id,
            'user_id' => user_id(),
        ]);

        // Pass to the handler
        $export   = new $handler($file->object);
        $response = $export->setFilename($file->filename)->process();

        // If the handler returned a response then we're done
        if ($response instanceof ResponseInterface) {
            return $response;
        }

        return redirect()->back();
    }

    /**
     * Outputs a file thumbnail directly as image data.
     *
     * @param int|string $fileId
     */
    public function thumbnail($fileId): ResponseInterface
    {
        if ($file = $this->model->find($fileId)) {
            $path = $file->getThumbnail();
        } else {
            $path = File::locateDefaultThumbnail();
        }

        return $this->response->setHeader('Content-type', 'image/jpeg')->setBody(file_get_contents($path));
    }

    //--------------------------------------------------------------------

    /**
     * Handles failures.
     *
     * @return RedirectResponse|ResponseInterface
     */
    protected function failure(int $code, string $message, ?bool $isAjax = null): ResponseInterface
    {
        log_message('debug', $message);

        if ($isAjax ?? $this->request->isAJAX()) {
            return $this->response
                ->setStatusCode($code)
                ->setJSON(['error' => $message]);
        }

        return redirect()->back()->with('error', $message);
    }

    /**
     * Sets a value in $this->data, overwrites optional.
     *
     * @param array<string, mixed> $data
     *
     * @return $this
     */
    protected function setData(array $data, bool $overwrite = false): self
    {
        if ($overwrite) {
            $this->data = array_merge($this->data, $data);
        } else {
            $this->data = array_merge($data, $this->data);
        }

        return $this;
    }

    /**
     * Merges in the default metadata.
     *
     * @return $this
     */
    protected function setDefaults(): self
    {
        return $this->setData([
            'source'   => 'index',
            'layout'   => 'files',
            'files'    => null,
            'selected' => explode(',', $this->request->getVar('selected') ?? ''),
            'userId'   => null,
            'username' => '',
            'ajax'     => $this->request->isAJAX(),
            'search'   => $this->request->getVar('search'),
            'sort'     => $this->getSort(),
            'order'    => $this->getOrder(),
            'format'   => $this->getFormat(),
            'perPage'  => $this->getPerPage(),
            'page'     => $this->request->getVar('page'),
            'pager'    => null,
            'access'   => $this->model->mayAdmin() ? 'manage' : 'display',
            'exports'  => $this->getExports(),
            'bulks'    => handlers()->where(['bulk' => 1])->findAll(),
        ]);
    }

    /**
     * Determines the sort field.
     */
    protected function getSort(): string
    {
        // Check for a sort request
        if (null !== $sort = $this->validateSort($this->request->getVar('sort'))) {
            // Store the new preference
            preference('Files.sort', $sort);

            return $sort;
        }

        return $this->validateSort(preference('Files.sort')) ?? 'filename';
    }

    /**
     * Determines whether the given field is valid for sorting.
     */
    private function validateSort(?string $sort): ?string
    {
        if ($sort === null) {
            return null;
        }

        $allowed = $this->model->allowedFields; // @phpstan-ignore-line
        $allowed = array_merge($allowed, [
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        return in_array($sort, $allowed, true) ? $sort : null;
    }

    /**
     * Determines the sort order.
     */
    protected function getOrder(): string
    {
        // Check for a sort request
        if (null !== $order = $this->validateOrder($this->request->getVar('order'))) {
            // Store the new preference
            preference('Files.order', $order);

            return $order;
        }

        return $this->validateOrder(preference('Files.order')) ?? 'asc';
    }

    /**
     * Determines whether the given order is valid.
     */
    private function validateOrder(?string $order): ?string
    {
        if ($order === null) {
            return null;
        }

        return in_array($order, ['asc', 'desc'], true) ? $order : null;
    }

    /**
     * Determines items per page.
     */
    protected function getPerPage(): int
    {
        // Check for a sort request
        if (null !== $perPage = $this->validatePerPage($this->request->getVar('perPage'))) {
            // Store the new preference
            preference('App.perPage', $perPage);

            return $perPage;
        }

        return $this->validatePerPage(preference('Files.perPage')) ?? 10;
    }

    /**
     * Determines whether the "per page" is valid.
     *
     * int|string|null
     *
     * @param mixed $perPage
     */
    private function validatePerPage($perPage): ?int
    {
        if ($perPage === null || ! is_numeric($perPage)) {
            return null;
        }
        $perPage = (int) $perPage;

        return $perPage > 0 ? $perPage : null;
    }

    /**
     * Determines the display format.
     */
    protected function getFormat(): string
    {
        // Check for a sort request
        if (null !== $format = $this->validateFormat($this->request->getVar('format'))) {
            // Store the new preference
            preference('Files.format', $format);

            return $format;
        }

        return $this->validateFormat(preference('Files.format')) ?? 'cards';
    }

    /**
     * Determines whether the display format is valid.
     */
    private function validateFormat(?string $format): ?string
    {
        if ($format === null) {
            return null;
        }

        return in_array($format, ['cards', 'list', 'select'], true) ? $format : null;
    }

    /**
     * Gets Export handlers indexed by the extension they support.
     *
     * @return array<string, array>
     */
    protected function getExports(): array
    {
        $exports = [];

        foreach (handlers('Exports')->findAll() as $class) {
            $attributes = handlers()->getAttributes($class);

            // Add the class name for easy access later
            $attributes['class'] = $class;

            foreach (explode(',', $attributes['extensions']) as $extension) {
                $exports[$extension][] = $attributes;
            }
        }

        return $exports;
    }
}
