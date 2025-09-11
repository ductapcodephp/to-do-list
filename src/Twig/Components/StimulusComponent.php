<?php

namespace App\Twig\Components;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'components/StimulusComponent.html.twig')]
final class StimulusComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query='';
    #[LiveProp(writable: true)]
    public string $mode = 'view';
    public function __construct(private TaskRepository $taskRepository,
            EntityManagerInterface $em
    )
    {
        $this->em = $em;
    }

    public function getData(): array
    {
//        return $this->taskRepository->findByTitleLike($this->query);
//          return $this->em->getRepository(Task::class)->findAll();
        return [
            ['title' => 'Task 1'],
            ['title' => 'Task 2'],
        ];
    }

    #[LiveAction]
    public function save(array $args = []): void
    {
        $this->mode = 'saved';
    }


}
