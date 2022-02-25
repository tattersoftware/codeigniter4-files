<?php

namespace Tatter\Files\Models;

use CodeIgniter\Files\File as CIFile;
use CodeIgniter\Model;
use Config\Mimes;
use Faker\Generator;
use Tatter\Files\Entities\File;
use Tatter\Permits\Traits\PermitsTrait;
use Tatter\Thumbnails\Factories\ThumbnailerFactory;
use Throwable;

class FileModel extends Model
{
    use PermitsTrait;

    protected $table          = 'files';
    protected $primaryKey     = 'id';
    protected $returnType     = File::class;
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $skipValidation = false;
    protected $allowedFields  = [
        'filename',
        'localname',
        'clientname',
        'type',
        'size',
        'thumbnail',
    ];
    protected $validationRules = [
        'filename' => 'required|max_length[255]',
        // file size in bytes
        'size' => 'permit_empty|is_natural',
    ];

    //--------------------------------------------------------------------

    /**
     * Associates a file with a user
     */
    public function addToUser(int $fileId, int $userId): bool
    {
        return (bool) $this->db->table('files_users')->insert([
            'file_id' => $fileId,
            'user_id' => $userId,
        ]);
    }

    /**
     * Returns an array of all a user's Files
     */
    public function getForUser(int $userId): array
    {
        return $this->whereUser($userId)->findAll();
    }

    /**
     * Adds a where filter for a specific user.
     *
     * @return $this
     */
    public function whereUser(int $userId): self
    {
        $this->select('files.*')
            ->join('files_users', 'files_users.file_id = files.id', 'left')
            ->where('user_id', $userId);

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Creates a new File from a path File. See createFromFile().
     *
     * @param array $data Additional data to pass to insert()
     */
    public function createFromPath(string $path, array $data = []): File
    {
        return $this->createFromFile(new CIFile($path, true), $data);
    }

    /**
     * Creates a new File from a framework File. Adds it to the
     * database and moves it into storage (if it is not already).
     *
     * @param array $data Additional data to pass to insert()
     */
    public function createFromFile(CIFile $file, array $data = []): File
    {
        // Gather file info
        $row = [
            'filename'   => $file->getFilename(),
            'clientname' => $file->getFilename(),
            'type'       => Mimes::guessTypeFromExtension($file->getExtension()) ?? $file->getMimeType(),
            'size'       => $file->getSize(),
        ];

        // Merge additional data
        $row = array_merge($row, $data);

        // Normalize paths
        $storage  = config('Files')->getPath();
        $filePath = $file->getRealPath() ?: (string) $file;

        // Determine if we need to move the file
        if (strpos($filePath, $storage) === false) {
            // Move the file
            $file = $file->move($storage, $file->getRandomName());
            chmod((string) $file, 0664);
        }
        $row['localname'] = $file->getFilename();

        // Record it in the database
        $fileId = $this->insert($row);

        // If a user is logged in then associate the File
        if ($userId = user_id()) {
            $this->addToUser($fileId, $userId);
        }

        $entity = $this->find($fileId);

        // Get the extension
        if ($extension = $entity->getExtension()) {
            // Check for a thumbnail handler
            if (ThumbnailerFactory::findForExtension($extension) !== []) {
                // Try to create a Thumbnail
                $thumbnail = pathinfo($row['localname'], PATHINFO_FILENAME);
                $output    = $storage . 'thumbnails' . DIRECTORY_SEPARATOR . $thumbnail;

                try {
                    $result = service('thumbnails')->create($entity->getPath());
                    copy($result, $output);

                    // If it succeeds then update the database
                    $entity->thumbnail = $thumbnail;
                    $this->update($entity->id, [
                        'thumbnail' => $thumbnail,
                    ]);
                } catch (Throwable $e) {
                    log_message('error', $e->getMessage());
                    log_message('error', 'Unable to create thumbnail for ' . $row['filename']);

                    if (ENVIRONMENT === 'testing') {
                        throw $e;
                    }
                }
            } else {
                log_message('debug', 'No thumbnail handler located for extension ' . $extension);
            }
        }

        // Return the File entity
        return $entity;
    }

    /**
     * Faked data for Fabricator.
     */
    public function fake(Generator &$faker): File
    {
        $name = $faker->company . '.' . $faker->fileExtension;

        return new File([
            'filename'   => $name,
            'localname'  => $faker->md5,
            'clientname' => $name,
            'type'       => $faker->mimeType,
            'size'       => random_int(1000, 4_000_000),
            'thumbnail'  => $faker->text(5000),
        ]);
    }
}
