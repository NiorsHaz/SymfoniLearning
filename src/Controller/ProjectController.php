<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\CategoryRepository;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProjectController extends AbstractController
{
    #[Route('/projects', name: 'project.index')]
    public function index(Request $request, ProjectRepository $repository, CategoryRepository $crepository): Response
    {

        $name = $request->query->get('search', '');

        $page = $request->query->getInt('page', 1);
        $limit = 1;
        $projects = $repository->paginateProjects($this->isGranted('ROLE_ADMIN'), false,$name ,$page, $limit);
        $maxPage = ceil($projects->count() / $limit);
        return $this->render('project/index.html.twig', [
            'projects' => $projects,
            'searchTitle' => $name,
            'maxPage' => $maxPage,
            'page' => $page
        ]);
    }

    #[Route('/projects/create', name: 'project.create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $task = new Project();
        $form = $this->createForm(ProjectType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($task);
            $em->flush();
            $this->addFlash('success', 'Les informations ont bien été enregistrées');
            return $this->redirectToRoute('project.index');
        }

        return $this->render('project/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/projects/{name}-{id}', name: 'project.show', requirements: ['id' => '\d+', 'name' => '[A-Za-z0-9-]+'])]
    public function show(Request $request, string $name, int $id, ProjectRepository $repository, TaskRepository $trepository): Response
    {
        $project = $repository->find($id);
        $task = $trepository->findByFilters($this->isGranted('ROLE_ADMIN'), '', 0, 10000, $id, false);

        if ($project->getName() !== $name) {
            return $this->redirectToRoute('project.show', ['name' => $project->getName(), 'id' => $project->getId()]);
        }

        return $this->render('project/show.html.twig', [
            'project' => $project,
            'tasks' => $task
        ]);
    }

    #[Route('/project/{id}/edit', name: 'project.edit')]
    public function edit(Project $task, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $form = $this->createForm(ProjectType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Les informations ont bien été enregistrées');
            return $this->redirectToRoute('task.index');
        }

        return $this->render('project/edit.html.twig', [
            'project' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/project/{id}/delete', name: 'project.delete', methods: ['POST'])]
    public function delete(Project $task, DeleteService $deleteService): Response
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

        return $this->redirectToRoute('project.index');
    }
}
