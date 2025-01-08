<?php

namespace App\Controller\API;

use App\Annotation\TokenRequired;
use App\DTO\ProjectDTO;
use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Service\DeleteService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Id;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ProjectApiController extends AbstractController
{
    #[Route('/api/projects/create', methods: "POST")]
    public function create(#[MapRequestPayload] Project $project, EntityManagerInterface $em){
        $em->persist($project);
        $em->flush();
        return $this->json("OK");

    }

    #[Route('/api/projects/get', methods: "GET")]
    public function list(Request $request, ProjectRepository $repository)
    {
        $searchTitle = $request->query->get('search', '');

        $page = max(1, $request->query->getInt('page', 1)); // Ensure page is at least 1
        $limit = max(1, $request->query->getInt('limit', 100)); // Ensure limit is at least 1       

        $list = $repository->paginateProjects(
            $this->isGranted('ROLE_ADMIN'),
            false, 
            $searchTitle, 
            $page, 
            $limit
        );

        return $this->json($list);
    }

    
    #[Route('/api/projects/get/{id}', methods: "GET", requirements: ['id' => '\d+'])]
    public function getId(Request $req, int $id, ProjectRepository $repository){
        $projects = $repository->findById($id);

        $projectDTOs = array_map(fn(Project $project) => new ProjectDTO($project), $projects);
        return $this->json($projectDTOs);
    }
    
    #[Route('/api/projects/edit/{id}', methods: "PUT")]
    public function edit(Request $req, int $id, #[MapRequestPayload] Project $project, EntityManagerInterface $em, ProjectRepository $repository){
        $exist = $repository->find($id);

        $exist->setName($project->getName());
        $exist->setDescription($project->getDescription());

        // Update entity with form data
        $em->flush();

        return $this->json("OK");
    }
    
    #[Route('/api/projects/softdelete-{id}', methods: "DELETE")]
    public function softdelete(Request $req, int $id, ProjectRepository $repository, DeleteService $deleteService){
        $exist = $repository->find($id);

        $deleteService->softDelete($exist);
        return $this->json("Soft Delete Successful");
    }

    #[Route('/api/projects/harddelete-{id}', methods: "DELETE")]
    public function harddelete(Request $req, int $id, ProjectRepository $repository, DeleteService $deleteService){
        $exist = $repository->find($id);

        $deleteService->hardDelete($exist);
        return $this->json("Soft Delete Successful");
    }
}
