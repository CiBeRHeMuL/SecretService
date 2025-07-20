<?php

namespace App\Presentation\Web\Controller;

use App\Application\UseCase\File\DownloadFilesUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

class
FilesController extends AbstractController
{
    #[Route('/files/{hash}', 'download_files', requirements: ['hash' => '.*'], methods: ['GET'])]
    public function download(
        string $hash,
        DownloadFilesUseCase $useCase,
    ): Response
    {
        $file = $useCase->execute($hash);
        if ($file === null) {
            return $this->render('message/error/not_found.html.twig');
        }
        $stat = fstat($file->getTempResource());
        return new StreamedResponse(
            static function () use (&$file) {
                rewind($file->getTempResource());
                stream_copy_to_stream($file->getTempResource(), fopen('php://output', 'w'));
                fclose($file->getTempResource());
            },
            headers: [
                'Content-Length' => $stat['size'],
                'Content-Type' => 'application/zip',
                'Content-Disposition' => "attachment; filename=\"$file->name\"",
            ],
        );
    }
}
