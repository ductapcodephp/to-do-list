<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TaskController extends AbstractController
{
    private EntityManagerInterface $em;
    private TaskRepository $taskRepository;
    public function __construct( EntityManagerInterface $em , TaskRepository $taskRepository)
    {
        $this->em = $em;
        $this->taskRepository = $taskRepository;
    }

    #[Route('/task', name: 'app_task')]
    public function index(): Response
    {
        $tasks = $this->em->getRepository(Task::class)->findAll();

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    #[Route('/addTask', name: 'app_task_add', methods: ['POST', 'GET'])]
    public function addTask(Request $request): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($task);
            $this->em->flush();

            $tasks = $this->taskRepository->findAll();
            $html = $this->renderView('task/reponse.html.twig', compact("tasks"));

            return new JsonResponse([
                'success' => true,
                'html' => $html
            ]);
        }

        return $this->render('task/modal.html.twig', [
            'form' => $form->createView(),
        ]);
    }




    #[Route('/deleteTask', name: 'app_task_delete', methods: ['POST'])]
    public function deleteTask(Request $request): JsonResponse
    {

        $data = json_decode($request->getContent(), true);
        $ids= $data['ids'];
        if(!empty($ids)) {
            foreach ($ids as $id) {
                $task = $this->taskRepository->find($id);
                if($task) {
                $this->em->remove($task);
                }
            }
            $this->em->flush();
        }

        return new JsonResponse(['success' => true]);
    }
    #[Route('/updateTask/{id}', name: 'app_task_update', methods: ['PUT'])]
    public function updateTask(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $task = $this->taskRepository->find($id);
        if($task) {
            $task->setTitle($data['title']);
            $task->setDescription($data['description'] ?? null);
            $task->setTimeStart(!empty($data['startDate']) ? new \DateTime($data['startDate']) : null);
            $task->setTimeEnd(!empty($data['endDate']) ? new \DateTime($data['endDate']) : null);
            $task->setStatus('pending');
            $this->em->flush();
        }
        return new JsonResponse([
            'success' => true,

        ]);
    }
}