<?php

namespace App\Controller;

use App\Form\AddPicturesType;
use App\Form\NewGalleryType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class GalleryController extends AbstractController
{
    public function __construct(
        private string $galleryDirectory,
        private SluggerInterface $slugger
    ){}

    #[Route('/gallery', name: 'app_gallery_index')]
    public function index(Filesystem $filesystem): Response
    {
        $galleries = [];
        $baseDir = $this->galleryDirectory;

        if ($filesystem->exists($baseDir)) {
            $files = scandir($baseDir);
            
            $galleries = array_filter($files, function($file) use ($baseDir) {
                return $file !== '.' && $file !== '..' && is_dir($baseDir . '/' . $file); 
            });
        }

        $result = [];
        foreach ($galleries as $slug) {
            $galleryPath = $baseDir . '/' . $slug;
            $thumbnailFilename = null;

            $filesInGallery = scandir($galleryPath);

            foreach ($filesInGallery as $file) {
                $fullPath = $galleryPath . '/' . $file;

                if ($file === '.' || $file === '..' || !is_file($fullPath)) {
                    continue;
                }

                $extension = pathinfo($file, PATHINFO_EXTENSION);
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];

                if (in_array(strtolower($extension), $allowedExtensions)) {
                    $thumbnailFilename = $file;
                    break;
                }
            }
            
            $result[] = [
                'slug' => $slug,
                'thumbnail' => $thumbnailFilename
            ];
        }

        // dd($result);
        
        return $this->render('gallery/index.html.twig', [
            'page_title' => 'Gallery',
            'galleries' => $result,
        ]);
    }

    #[Route('/gallery/new', name: 'app_gallery_new')]
    public function new(Request $request, Filesystem $filesystem): Response
    {
        $form = $this->createForm(NewGalleryType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $galleryName = $data['name'];

            $slug = $this->slugger->slug($galleryName)->lower()->toString();
            $galleryPath = $this->galleryDirectory . '/' . $slug;

            try {
                $filesystem->mkdir($galleryPath);
                $this->addFlash('success', sprintf('Gallery "%s" created successfully!', $galleryName));

                return $this->redirectToRoute('app_gallery_upload', ['slug' => $slug]);
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Could not create gallery directory.');
            }
        }

        return $this->render('gallery/new.html.twig', [
            'page_title' => 'Add New Gallery',
            'form' => $form->createView(),
        ]);
    }

    #[Route('/gallery/{slug}/upload', name: 'app_gallery_upload')]
    public function upload(string $slug, Request $request, Filesystem $filesystem): Response
    {
        $galleryPath = $this->galleryDirectory . '/' . $slug;
        $form = $this->createForm(AddPicturesType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $data = $form->getData();

            /** @var UploadedFile[] $uploadedFiles */
            $uploadedFiles = $form->get('pictures')->getData();
            $uploadCount = 0;

            foreach ($uploadedFiles as $file) {
                if($file instanceof UploadedFile) {
                    $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $this->slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                    try {
                        $file->move($galleryPath, $newFilename);
                        $uploadCount++;
                    } catch (\Exception $e) {
                        $this->addFlash('danger', sprintf('Error uploading file %s: %s', $originalFilename, $e->getMessage()));
                    }
                }
            }

            if ($uploadCount > 0) {
                $this->addFlash('success', sprintf('Successfully uploaded %d picture(s) to gallery %s.', $uploadCount, $slug));
                return $this->redirectToRoute('app_gallery_index'); 
            }
        }

        return $this->render('gallery/upload.html.twig', [
            'page_title' => 'Add Pictures to ' . $slug,
            'slug' => $slug,
            'form' => $form->createView(),
        ]);
    }
}