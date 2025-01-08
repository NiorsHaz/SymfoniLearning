<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use App\Form\AssignTaskType;
use App\Form\AssignUserType;
use App\Repository\TaskRepository;
use App\Repository\CategoryRepository;
use App\Service\DeleteService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'task.index')]
    public function index(Request $request, TaskRepository $repository, CategoryRepository $crepository ): Response
    {
        // Récupérer les valeurs de recherche et de filtre min/max
        $searchTitle = $request->query->get('search', '');
        $minEstimate = $request->query->get('min_estimate', 0);
        $maxEstimate = $request->query->get('max_estimate', 10000); // Limite par défaut
        $category_id = $request->query->get('category', 0);

        $page = $request->query->getInt('page', 1);
        $limit = 2;
        $categories = $crepository->findAll();

        // Appeler la méthode du repository pour filtrer les tâches
        $tasks =  $repository->paginateTask($this->isGranted('ROLE_ADMIN'), false, $searchTitle, $minEstimate, $maxEstimate, $page, $limit, $category_id);
        $totalEstimates = $repository->findTotalEstimates($this->isGranted('ROLE_ADMIN'), false, $searchTitle, $minEstimate, $maxEstimate, 0, $category_id);

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
            'categories' => $categories,
            'totalEstimates' => $totalEstimates,
            'searchTitle' => $searchTitle,
            'minEstimate' => $minEstimate,
            'maxEstimate' => $maxEstimate,
        ]);
    }
    #[Route('/tasks/trashbin', name: 'task.trashbin')]
    public function trashbin(Request $request, TaskRepository $repository, CategoryRepository $crepository): Response
    {
        // Récupérer les valeurs de recherche et de filtre min/max
        $searchTitle = $request->query->get('search', '');
        $minEstimate = $request->query->get('min_estimate', 0);
        $maxEstimate = $request->query->get('max_estimate', 10000); // Limite par défaut
        $category_id = $request->query->get('category', 0);

        $page = $request->query->getInt('page', 1);
        $limit = 2;
        $categories = $crepository->findAll();

        // Appeler la méthode du repository pour filtrer les tâches
        $tasks =  $repository->paginateTask($this->isGranted('ROLE_ADMIN'), true, $searchTitle, $minEstimate, $maxEstimate, $page, $limit, $category_id);
        $totalEstimates = $repository->findTotalEstimates($this->isGranted('ROLE_ADMIN'), true,$searchTitle, $minEstimate, $maxEstimate, 0, $category_id);

        return $this->render('task/deleted.html.twig', [
            'tasks' => $tasks,
            'categories' => $categories,
            'totalEstimates' => $totalEstimates,
            'searchTitle' => $searchTitle,
            'minEstimate' => $minEstimate,
            'maxEstimate' => $maxEstimate,
        ]);
    }

    #[Route('/tasks/{slug}-{id}', name: 'task.show', requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+'])]
    public function show(Request $request, string $slug, int $id, TaskRepository $repository): Response
    {
        $task = $repository->find($id);

        if ($task->getSlug() !== $slug) {
            return $this->redirectToRoute('task.show', ['slug' => $task->getSlug(), 'id' => $task->getId()]);
        }

        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/tasks/restore/{id}', name: 'task.restore', requirements: ['id' => '\d+'])]
    public function restore(int $id, TaskRepository $repository): Response
    {
        $repository->restoreTask($id);

        $this->addFlash('success', 'La tache a bien été restorée');
        return $this->redirectToRoute('task.index');
    }

    #[Route('/tasks/{id}/edit', name: 'task.edit')]
    public function edit(Task $task, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Les informations ont bien été enregistrées');
            return $this->redirectToRoute('task.index');
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/tasks/create', name: 'task.create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($task);
            $em->flush();
            $this->addFlash('success', 'Les informations ont bien été enregistrées');
            return $this->redirectToRoute('task.index');
        }

        return $this->render('task/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/task/{id}/delete', name: 'task.delete', methods: ['POST'])]
    public function delete(Task $task, DeleteService $deleteService): Response
    {
        // Si l'utilisateur est admin, on effectue une suppression hard
        if ($this->isGranted('ROLE_ADMIN')) {
            $deleteService->hardDelete($task);
            $this->addFlash('success', 'Tâche supprimée définitivement.');
        }
        // Sinon, on effectue une soft delete
        else {
            if ($task->isDeleted()) {
                throw new AccessDeniedException('Vous ne pouvez pas supprimer une tâche déjà supprimée.');
            }
            $deleteService->softDelete($task);
            $this->addFlash('success', 'Tâche supprimée temporairement.');
        }

        return $this->redirectToRoute('task.index');
    }
    #[Route('/task/{id}/defdelete', name: 'task.defdelete', methods: ['GET'])]
    public function defdelete(Task $task, DeleteService $deleteService): Response
    {
        // Si l'utilisateur est admin, on effectue une suppression hard
        $deleteService->hardDelete($task);
        $this->addFlash('success', 'Tâche supprimée définitivement.');

        return $this->redirectToRoute('task.index');
    }
    
    #[Route('/assign-tasks', name: 'assign_tasks')]
    public function assignTasks(Request $request, EntityManagerInterface $entityManager): Response
    {
        $projects = $entityManager->getRepository(Project::class)->findAll();
        $tasks = $entityManager->getRepository(Task::class)->findBy(['project' => null]); // Unassigned tasks

        $form = $this->createForm(AssignTaskType::class, null, [
            'projects' => $projects,
            'tasks' => $tasks,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $project = $data['project'];
            $selectedTasks = $data['tasks'];

            foreach ($selectedTasks as $task) {
                $task->setProject($project);
                $entityManager->persist($task);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Tasks assigned successfully!');

            return $this->redirectToRoute('project.index');
        }

        return $this->render('task/assign_tasks.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/assign-users', name: 'assign_user')]
    public function assignUser(Request $request, EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findAll();
        $tasks = $entityManager->getRepository(Task::class)->findAll();

        $form = $this->createForm(AssignUserType::class, null, [
            'tasks' => $tasks,
            'users' => $users
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $task = $data['tasks'];
            $selectedUsers = $data['users'];

            foreach ($selectedUsers as $user) {
                $task->addAssignee($user);
                $entityManager->persist($task);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Users assigned successfully!');

            return $this->redirectToRoute('project.index');
        }

        return $this->render('task/assign_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
