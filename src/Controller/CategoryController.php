<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategoryController extends AbstractController
{
    #[Route('/category/create', name: 'category.create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $task = new Category();
        $form = $this->createForm(CategoryType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($task);
            $em->flush();
            $this->addFlash('success', 'Les informations ont bien été enregistrées');
            return $this->redirectToRoute('task.index');
        }

        return $this->render('category/create.html.twig', [
            'form' => $form,
        ]);
    }
}
