<?php

namespace App\Controller;

use App\Service\TrackService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @var Session
     */
    private Session $session;

    public function __construct()
    {
        $this->session = new Session();
        $this->session->start();
    }

    #[Route('/', name: 'index')]
    public function indexAction(): Response
    {
        $errors = $this->session->getFlashBag()->get('error', []);

        $this->session->clear();

        return $this->render('fixer/index.html.twig', [
            'errors' => $errors,
        ]);
    }

    #[Route('/upload', name: 'upload_file')]
    public function uploadAction(Request $request, TrackService $trackService, LoggerInterface $logger): BinaryFileResponse|RedirectResponse
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('track', false);

        if (!$file) {
            $logger->error('Wrong file format.');
            $this->session->getFlashBag()->add('error', 'Нужно загрузитъ GPX файл.');

            return $this->redirectToRoute('index');
        }

        if ($file->getClientOriginalExtension() !== 'gpx' && $file->getClientMimeType() !== 'text/xml') {
            $logger->error('Wrong file content ' . $file->getClientOriginalName());
            $this->session->getFlashBag()->add('error', 'Содержимое файла не похоже на GPX :(');

            return $this->redirectToRoute('index');
        }

        $fixedTrack = $trackService->handleFile($file);

        if (!$fixedTrack) {
            $logger->error('Something wrong with ' . $file->getClientOriginalName());
            $this->session->getFlashBag()->add('error', 'Что-то пошло не так, напишите на ra0ued@zabtech.ru');

            return $this->redirectToRoute('index');
        }

        return $this->file($fixedTrack, 'Fixed_' . $file->getClientOriginalName());
    }
}