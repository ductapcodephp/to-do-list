<?php

namespace App\Twig\Components;

use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
#[AsLiveComponent(template: 'components/Task.html.twig')]
final class TaskComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $keyword = '';

    #[LiveProp(writable: true)]
    public int $page = 1;

    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly PaginatorInterface $paginator,
    ) {}

    public function getData(): PaginationInterface
    {
        $qb = $this->taskRepository->createQueryBuilder('t');

        if (!empty($this->keyword)) {
            $qb->andWhere('t.title LIKE :keyword')
                ->setParameter('keyword', '%' . $this->keyword . '%');
        }

        return $this->paginator->paginate($qb, $this->page, 10);
    }
}
