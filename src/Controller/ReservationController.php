<?php
namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[Route('/api/reservation')]
final class ReservationController extends AbstractController
{    
    #[Route( name: 'api_reservations', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
public function getReservations(ReservationRepository $reservationRepository): Response
{
    try{
        $reservations = $reservationRepository->findAll();
    } catch(\Exception $e){
        return $this->json(['error' => 'Erreur lors de la récupération des réservations.'], Response::HTTP_BAD_REQUEST);
    }
    
    $data = [];
    foreach ($reservations as $reservation) {
        $data[] = [
            'id' => $reservation->getId(),
            'date' => $reservation->getDate()->format('Y-m-d'),
            'timeSlot' => $reservation->getTimeSlot(),
            'eventName' => $reservation->getEventName(),
            'user' => [
                'id' => $reservation->getUser()->getId(),
                'name' => $reservation->getUser()->getName(),
            ],
        ];
    }

    return $this->json($data);
}

    #[Route('/new', name: 'api_reservation_new', methods: ['POST'])]
    #[IsGranted('ROLE_USER')] 
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservation = new Reservation();
        $data = json_decode($request->getContent(), true); 

       
        if (isset($data['date'], $data['timeSlot'], $data['eventName'])) {
            $reservation->setDate(new \DateTime($data['date']));
            $reservation->setTimeSlot($data['timeSlot']);
            $reservation->setEventName($data['eventName']);
            $reservation->setUser($this->getUser());


            if ($reservation->getDate() < new \DateTime('+1 day')) {
                return $this->json(['error' => 'La réservation doit être faite au moins 24 heures à l\'avance.'], Response::HTTP_BAD_REQUEST);
            }

            
            $existingReservation = $entityManager->getRepository(Reservation::class)->findOneBy([
                'date' => $reservation->getDate(),
                'timeSlot' => $reservation->getTimeSlot(),
            ]);

            if ($existingReservation) {
                return $this->json(['error' => 'Cette plage horaire est déjà réservée.'], Response::HTTP_BAD_REQUEST);
            }


            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->json([
                'message' => 'Réservation créée avec succès',
                'reservation' => [
                    'id' => $reservation->getId(),
                    'date' => $reservation->getDate()->format('Y-m-d H:i:s'),
                    'timeSlot' => $reservation->getTimeSlot(),
                    'eventName' => $reservation->getEventName(),
                ]
            ], Response::HTTP_CREATED);
        }

        return $this->json(['error' => 'Données manquantes.'], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'api_reservation_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(int $id, EntityManagerInterface $entityManager): Response
    {
        $reservation = $entityManager->getRepository(Reservation::class)->find($id);
    
        if (!$reservation) {
            throw new NotFoundHttpException('Reservation not found');
        }
    

        if ($reservation->getUser() !== $this->getUser()) {
            throw new AccessDeniedHttpException('You do not have access to this reservation');
        }
    
        return $this->json([
            'reservation' => [
                'id' => $reservation->getId(),
                'date' => $reservation->getDate()->format('Y-m-d H:i:s'),
                'timeSlot' => $reservation->getTimeSlot(),
                'eventName' => $reservation->getEventName(),
            ]
        ]);
    }

    #[Route('/{id}/edit', name: 'api_reservation_edit', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
      
        $data = json_decode($request->getContent(), true);  

       
        if (isset($data['date'])) {
            $reservation->setDate(new \DateTime($data['date']));
        }
        if (isset($data['timeSlot'])) {
            $reservation->setTimeSlot($data['timeSlot']);
        }
        if (isset($data['eventName'])) {
            $reservation->setEventName($data['eventName']);
        }
        try{
            $entityManager->flush();
        } catch(\Exception $e){
            return $this->json(['error' => 'Erreur lors de la modification.'], Response::HTTP_BAD_REQUEST);
        }
      
        return $this->json([
            'message' => 'Réservation modifiée avec succès',
            'reservation' => [
                'id' => $reservation->getId(),
                'date' => $reservation->getDate()->format('Y-m-d H:i:s'),
                'timeSlot' => $reservation->getTimeSlot(),
                'eventName' => $reservation->getEventName(),
            ]
        ]);
    }

    #[Route('/{id}', name: 'api_reservation_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
     
       try{
            $entityManager->remove($reservation);
            $entityManager->flush();

            return $this->json(['message' => 'Réservation supprimée avec succès']);
       }
        catch(\Exception $e){
            return $this->json(['error' => 'Erreur lors de la suppression.'], Response::HTTP_BAD_REQUEST);
        }
        
    }
}
