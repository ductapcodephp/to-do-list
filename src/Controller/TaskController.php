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
    public function index(Request $request): Response
    {
        $tasks = $this->taskRepository->findAll();
        $form= $this->createForm(TaskType::class);
        $form->handleRequest($request);
        return $this->render("task/index.html.twig", [
            'tasks' => $tasks,
            'form' => $form
        ]);
    }

    #[Route('/addTask', name: 'app_task_add', methods: ['GET','POST'])]
    public function addTask(Request $request): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task,[
            'action' => $this->generateUrl('app_task_add'),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($task);
            $this->em->flush();
            $html = $this->renderView('task/response.html.twig', [
                'task' => $task,
            ]);

            return new JsonResponse([
                'success' => true,
                'html'    => $html,
            ]);
        }

        return $this->render("task/modal.html.twig", ['form' => $form->createView()]);
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
    #[Route('/editTask/{id}', name: 'app_task_edit', methods: ['GET','POST'])]
    public function editTask(int $id, Request $request): Response
    {
        $task = $this->taskRepository->find($id);
        if (!$task) {
            throw $this->createNotFoundException("Task not found");
        }

        $form = $this->createForm(TaskType::class, $task, [
            'action' => $this->generateUrl('app_task_edit', ['id' => $task->getId()]),
            'method' => 'POST'
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $html = $this->renderView('task/response.html.twig', [
                'task' => $task,
            ]);
            return new JsonResponse(['success' => true]);
        }


        return $this->render('task/modal_edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task
        ]);
    }
    #[Route('/tasks/status', name: 'task_status', methods: ['GET'])]
    public function status(TaskRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $now = new \DateTimeImmutable('now');
        $tasks = $repo->findAll();
        $data = [];

        foreach ($tasks as $task) {
            $oldStatus = $task->getStatus();
            $task->updateStatus($now);

            if ($oldStatus !== $task->getStatus()) {
                $em->persist($task);
            }

            $data[] = [
                'id' => $task->getId(),
                'status' => $task->getStatus(),
                'description' => $task->getDescription(),
            ];
        }

        $em->flush();

        return $this->json($data);
    }



}