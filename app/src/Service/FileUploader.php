<?php
/**
 * File Uploader service.
 */
namespace Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class FileUploader.
 */
class FileUploader
{
    /**
     * Target directory.
     *
     * @var string $targetDir
     */
    protected $targetDir;

    /**
     * FileUploader constructor.
     *
     * @param string $targetDir Target directory
     */
    public function __construct($targetDir)
    {
        $this->targetDir = $targetDir;
    }

    /**
     * Upload files
     * @param UploadedFile $file
     * @return string
     * @throws \Exception
     */
    public function upload(UploadedFile $file)
    {
        $fileName = bin2hex(random_bytes(32)).'.'.$file->guessExtension();
        $file->move($this->targetDir, $fileName);

        return $fileName;
    }

    /**
     * Get target directory.
     *
     * @return string Target directory
     */
    public function getTargetDir()
    {
        return $this->targetDir;
    }
}
