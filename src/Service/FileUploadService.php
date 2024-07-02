<?php

namespace App\Service;

use Aws\S3\S3Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FileUploadService
{
    private $params;

    /**
     * @param ParameterBagInterface $params
     */
    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    /**
     * @param UploadedFile $file
     * @param SluggerInterface $slugger
     * @return string
     * @throws \Exception
     */
    public function uploadFile(UploadedFile $file, SluggerInterface $slugger): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            $file->move(
                $this->params->get('kernel.project_dir') . '/public/uploads/images',
                $newFilename
            );
        } catch (\Exception $e) {
            throw new \Exception('Error uploading file: ' . $e->getMessage());
        }

        return $newFilename;
    }

}