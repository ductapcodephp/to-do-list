<?php

namespace App\Twig\Components;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'components/PracticeComponent.html.twig')]
final class PracticeComponent extends AbstractController
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public int $max = 1000;

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp(writable: true)]
    public bool $agreeToTerms = false;

    public function __construct(private TaskRepository $taskRepository)
    {

    }

    public function getData(): array
    {
//        return $this->taskRepository->findByTitleLike($this->query);
        return $this->taskRepository->findAll();

    }

    public function getRandomNumber(): int
    {
        return rand(0, $this->max);
    }

    #[LiveAction]
    public function resetMax(): void
    {
        $this->max = 1000;
    }

    #[LiveAction]
    public function addItem(#[LiveArg] int $id, #[LiveArg('itemName')] string $name): RedirectResponse
    {
        return $this->redirectToRoute('app_task');
//        $this->query = sprintf('Added item %d - %s', $id, $name);
    }

    #[LiveAction]
    public function myAction(Request $request): void
    {
        $files = $request->files->all('multiple');

        foreach ($files as $file) {
            $file->move(__DIR__ . '/../../../var/uploads', $file->getClientOriginalName());
        }
    }

    #[LiveAction]
    public function initiateDownload(#[LiveArg] string $filename, UrlGeneratorInterface $urlGenerator): RedirectResponse {

        $url = $urlGenerator->generate('app_file_download', [
            'filename' => $filename,
        ]);
        return new RedirectResponse($url);
    }
    public function getFiles(): array
    {
        return [
            'Screenshot from 2025-09-05 11-01-35.png',
            'Screenshot from 2025-09-11 11-19-55.png',
        ];
    }

}