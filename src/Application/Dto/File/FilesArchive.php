<?php

namespace App\Application\Dto\File;

class FilesArchive
{
    /**
     * @param string $name
     * @param resource $tempResource временный поток с данными файла
     */
    public function __construct(
        readonly private(set) string $name,
        private $tempResource,
    ) {
    }

    /**
     * @return resource
     */
    public function getTempResource()
    {
        return $this->tempResource;
    }
}
