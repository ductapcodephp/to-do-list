<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Task;
use App\Form\ExportType;
use App\Form\ImportType;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

final class     TaskController extends AbstractController
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

    #[Route('page-excel', name: 'app_task_excel', methods: ['GET'])]
    public function indexExcel(Request $request): Response
    {
        $tasks = $this->taskRepository->findAll();
        $form= $this->createForm(ImportType::class);
        $form->handleRequest($request);
        return $this->render('excel/index.html.twig', [
            'tasks' => $tasks,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/task/import', name: 'task_import', methods: ['GET','POST'])]
    public function exportExcel(Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ImportType::class, null, [
            'action' => $this->generateUrl('task_import'),
            'method' => 'POST',
            'attr' => ['enctype' => 'multipart/form-data']
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();

            if ($file) {
                $spreadsheet = IOFactory::load($file->getPathname());
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();

                foreach ($rows as $index => $row) {
                    if ($index === 0) {
                        continue;
                    }

                    $id = $row[0] ?? null;
                    if ($id) {
                        $task = $em->getRepository(Task::class)->find($id);
                    }

                    if (empty($task)) {
                        $task = new Task();
                        $em->persist($task);
                    }

                    $task->setTitle($row[1] ?? null);
                    $task->setDescription($row[2] ?? null);
                    $task->setDurationDay((int)($row[3] ?? 0));
                    $task->setTimeStart(!empty($row[4]) ? new \DateTime($row[4]) : null);
                    $task->setTimeEnd(!empty($row[5]) ? new \DateTime($row[5]) : null);
                    $task->setUpdatedAt(new \DateTimeImmutable('now'));
                }

                $em->flush();
                return $this->redirectToRoute('app_task_excel');
            }
        }

        return $this->render('excel/index.html.twig', [
            'tasks' => $this->taskRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/tasks/export', name: 'task_export')]
    public function export(TaskRepository $taskRepository): Response
    {
        $tasks = $taskRepository->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Title');
        $sheet->setCellValue('C1', 'Description');
        $sheet->setCellValue('D1', 'DurationDay');
        $sheet->setCellValue('E1', 'TimeStart');
        $sheet->setCellValue('F1', 'TimeEnd');
        $sheet->setCellValue('G1', 'Status');
        $sheet->setCellValue('H1', 'CreatedAt');
        $sheet->setCellValue('I1', 'UpdatedAt');

        $rowNum = 2;
        foreach ($tasks as $task) {
            $sheet->setCellValue("A{$rowNum}", $task->getId());
            $sheet->setCellValue("B{$rowNum}", $task->getTitle());
            $sheet->setCellValue("C{$rowNum}", $task->getDescription());
            $sheet->setCellValue("D{$rowNum}", $task->getDurationDay());
            $sheet->setCellValue("E{$rowNum}", $task->getTimeStart()?->format('Y-m-d H:i'));
            $sheet->setCellValue("F{$rowNum}", $task->getTimeEnd()?->format('Y-m-d H:i'));
            $sheet->setCellValue("G{$rowNum}", $task->getStatus());
            $sheet->setCellValue("H{$rowNum}", $task->getCreatedAt()?->format('Y-m-d H:i'));
            $sheet->setCellValue("I{$rowNum}", $task->getUpdatedAt()?->format('Y-m-d H:i'));

            $rowNum++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'tasks_export.xlsx';

        $response = new Response();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', "attachment; filename=\"$fileName\"");

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        $response->setContent($content);

        return $response;
    }


    #[Route('component', name:'task_component', methods: ['GET'])]
    public function component(): Response
    {

        return $this->render('practice_component/index.html.twig');
    }
    #[Route('stimulus', name:'task_stimulus', methods: ['GET'])]
    public function stimulus(): Response
    {

        return $this->render('practice_component/stimulus.html.twig');
    }

    #[Route('/download/{filename}', name: 'app_file_download')]
    public function download(string $filename): BinaryFileResponse
    {
        $filePath = $this->getParameter('kernel.project_dir').'/public/uploads/'.$filename;

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('File not found');
        }
        return $this->file($filePath, $filename, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }
    #[Route('form', name:'task_form', methods: ['GET'])]
    public function form(EntityManagerInterface $em): Response
    {
        $post = $em->getRepository(Post::class)->findAll();
        return $this->render('practice_component/form.html.twig',['post'=>$post]);
    }
}