<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(UserPasswordHasherInterface $hasher, EntityManagerInterface $em): Response
    {
        // $user = new User();
        // $user->setEmail('moi@test.com')
        //     ->setUsername('Moi')
        //     ->setPassword($hasher->hashPassword($user, '0000'))
        //     ->setRoles([]);
        // $em->persist($user);
        // $em->flush();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
