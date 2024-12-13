<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'api_user_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->json([
            'users' => $users
        ]);
    }

    
    #[Route('/new', name: 'api_user_new', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $data = json_decode($request->getContent(), true);  

        if (isset($data['email'], $data['password'], $data['roles'])) {
            $user->setEmail($data['email']);
            $user->setPassword($data['password']); 
            $user->setRoles($data['roles']);
            $user->setName($data['name']);
            $user->setPhoneNumber($data['phoneNumber']);
            $user->setRoles($data['roles']);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->json([
                'message' => 'Utilisateur créé avec succès',
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'roles' => $user->getRoles(),
                ]
            ], Response::HTTP_CREATED);
        }

        return $this->json(['error' => 'Données manquantes ou invalides.'], Response::HTTP_BAD_REQUEST);
    }

    
    #[Route('/{id}', name: 'api_user_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(User $user): Response
    {
        return $this->json([
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ]
        ]);
    }

    #[Route('/{id}/edit', name: 'api_user_edit', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
    
        $data = json_decode($request->getContent(), true);  

     
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['password'])) {
            $user->setPassword($data['password']);  
        }
        if (isset($data['roles'])) {
            $user->setRoles($data['roles']);
        }

        $entityManager->flush();

        return $this->json([
            'message' => 'Utilisateur modifié avec succès',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ]
        ]);
    }


    #[Route('/{id}', name: 'api_user_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
    try{
            $entityManager->remove($user);
            $entityManager->flush();

            return $this->json([
                'message' => 'Utilisateur supprimé avec succès'
            ]);}
        catch(\Exception $e){
            return $this->json(['error' => 'Erreur lors de la suppression.'], Response::HTTP_BAD_REQUEST);
        }

    }
}
