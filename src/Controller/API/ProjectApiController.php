<?php

namespace App\Controller\API;

use App\Annotation\TokenRequired;
use App\DTO\ProjectDTO;
use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Service\DeleteService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Id;
use Psr\Log\LoggerInterface;
use App\Service\JwtTokenManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    private $jwtTokenManager;

    public function __construct(JwtTokenManager $jwtTokenManager)
    {
        $this->jwtTokenManager = $jwtTokenManager;
    }

    private function checkCors(): Response
    {
        $response = new Response();
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, x-binarybox-api-key');
        $response->headers->set('Access-Control-Max-Age', '3600');
        return $response;
    }

    #[Route('/api/projects/create', methods: ["POST", "OPTIONS"])]
    #[TokenRequired]
    public function create(Request $req, EntityManagerInterface $em){
        if($req->getMethod() === "OPTIONS"){
            $response = $this->checkCors();
            return $response;
        }
        $project = new Project();

        $data = json_decode($req->getContent(), true);
        $project->setName($data['name'] ?? null);
        $project->setDescription($data['description'] ?? null);

        $em->persist($project);
        $em->flush();

        $response = $this->json("OK");
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'POST, OPTIONS'); // Add POST here'
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, x-binarybox-api-key');
        return $response;

    }

    #[Route('/api/projects/get', methods: ["GET", "OPTIONS"])]
    #[TokenRequired]
    public function list(Request $request, ProjectRepository $repository)
    {
        if($request->getMethod() === "OPTIONS"){
            $response = $this->checkCors();
            return $response;
        }
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

        $response = $this->json($list);
        $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response;
    }

    
    #[Route('/api/projects/get/{id}', methods: ["GET", "OPTIONS"])]
    #[TokenRequired]
    public function getId(Request $req, int $id, ProjectRepository $repository){
        if($req->getMethod() === "OPTIONS"){
            $response = $this->checkCors();
            return $response;
        }
        $projects = $repository->findById($id);

        $projectDTOs = array_map(fn(Project $project) => new ProjectDTO($project), $projects);
        $response = $this->json($projectDTOs);
        // $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response;
    }
    
    #[Route('/api/projects/edit/{id}', methods: ["PUT", "OPTIONS"])]
    #[TokenRequired]
    public function edit(Request $req, int $id, EntityManagerInterface $em, ProjectRepository $repository){
        if($req->getMethod() === "OPTIONS"){
            $response = $this->checkCors();
            return $response;
        }

        $exist = $repository->find($id);

        if (!$exist) {
            return $this->json(['error' => 'Project not found'], 404);
        }
    
        // Decode the JSON payload
        $data = json_decode($req->getContent(), true);
    
        // Update the entity with data from the request
        if (isset($data['name'])) {
            $exist->setName($data['name']);
        }
        if (isset($data['description'])) {
            $exist->setDescription($data['description']);
        }

        // Update entity with form data
        $em->flush();

        $response = $this->json("OK");
        $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response;
    }
    
    #[Route('/api/projects/softdelete-{id}', methods: ["DELETE", "OPTIONS"])]
    #[TokenRequired]
    public function softdelete(Request $req, int $id, ProjectRepository $repository, DeleteService $deleteService){
        if($req->getMethod() === "OPTIONS"){
            $response = $this->checkCors();
            return $response;
        }
        $exist = $repository->find($id);

        $deleteService->softDelete($exist);
        return $this->json("Soft Delete Successful", 200, [
            'Access-Control-Allow-Origin', '*',
        ]);
    }

    #[Route('/api/projects/harddelete-{id}', methods: "DELETE")]
    #[TokenRequired]
    public function harddelete(Request $req, int $id, ProjectRepository $repository, DeleteService $deleteService){
        $exist = $repository->find($id);

        $deleteService->hardDelete($exist);
        return $this->json("Soft Delete Successful");
    }
}
