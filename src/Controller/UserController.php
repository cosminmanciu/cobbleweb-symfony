<?php

// src/Controller/UserController.php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Photo;
use App\Service\FileUploadService;
use App\Service\UploadInterface;
use Aws\S3\S3Client;
use GuzzleHttp\Psr7\CachingStream;
use GuzzleHttp\Utils;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserController extends AbstractController
{
    /**
     * @Route("/api/users/register", name="api_register", methods={"POST"})
     * @throws \Exception
     */
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        FileUploadService $fileUploadService

    ): Response
    {
        $user = new User();
        $user->setFirstName($request->get('firstName'));
        $user->setLastName($request->get('lastName'));
        $user->setFullName($request->get('firstName') . ' ' . $request->get('lastName'));
        $user->setEmail($request->get('email'));
        $user->setCreatedAt(new \DateTime());
        $user->setUpdatedAt(new \DateTime());


        $hashedPassword = $passwordHasher->hashPassword($user, $request->get('password'));
        $user->setPassword($hashedPassword);


        $avatar = $request->files->get('avatar');
        if ($avatar) {
            $avatarFilename = $fileUploadService->uploadFile($avatar, $slugger);
            //$photoUrl = $this->uploadToS3($avatar, $avatarFilename, $s3Client);
            $user->setAvatar($avatarFilename);
        } else {
            $user->setAvatar('default-avatar.png');
        }


        $photos = $request->files->get('photos');
        if ($photos && count($photos) >= 4) {
            foreach ($photos as $photo) {
                $photoFilename = $fileUploadService->uploadFile($photo, $slugger);
//              $photoUrl = $this->uploadToS3($photo, $photoFilename, $s3Client);
                $photoEntity = new Photo();
                $photoEntity->setName($photo->getClientOriginalName());
                $photoEntity->setUrl('/uploads/images/' . $photoFilename);
                $photoEntity->setUser($user);
                $entityManager->persist($photoEntity);
            }
        } else {
            return $this->json(['error' => 'At least 4 images should be uploaded.'], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['message' => 'User registered successfully.'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/users/me", name="me", methods={"GET"})
     */
    public function me(Security $security): Response
    {
        $user = $this->getUser();

        // Ensure user is authenticated
        if (!$user) {
            return $this->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $photos = [];
        foreach ($user->getPhotos() as $photo) {
            $photos[] = [
                'id' => $photo->getId(),
                'url' => $photo->getUrl(),
            ];
        }

        return $this->json([
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'fullName' => $user->getFullName(),
            'email' => $user->getEmail(),
            'avatar' => $user->getAvatar(),
            'photos' => $photos,
        ]);
    }
}
