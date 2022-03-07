<?php

namespace App\Controller\Api;

use App\Entity\Pot;
use App\Models\JsonError;
use App\Service\TotalCalculator;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PotController extends AbstractController
{
    /**
     * @Route("/api/pots", name="api_add_pot", methods = {"POST"})
     */
    public function addPot(EntityManagerInterface $doctrine, Request $request, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        $data = $request->getContent();
        try {
            $newPot = $serializer->deserialize($data, Pot::class, "json");
            $newPot->setUser($this->getUser());
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $errors = $validator->validate($newPot);

        if (count($errors) > 0) {
            $myJsonError = new JsonError(Response::HTTP_UNPROCESSABLE_ENTITY, "Des erreurs de validation ont été trouvées");
            $myJsonError->setValidationErrors($errors);
            return $this->json($myJsonError, $myJsonError->getError());
        }

        $doctrine->persist($newPot);
        $doctrine->flush();

        return $this->json(
            $newPot, Response::HTTP_CREATED,
            [],
            ['groups' => ['show_pot']]
        );
    }

    /**
     * Retourne les cagnottes liées à un utilisateur
     * 
     * @Route("/api/pots", name="api_pots", methods = {"GET"})
     */
    public function potsByUser(TotalCalculator $calculator): Response
    {
        $pots = $this->getUser()->getPots();
        foreach ($pots as $pot) {
            //Récupération du total des opérations d'une cagnotte
            $calculator->calculateAmount($pot);
        }
        return $this->json(
            $pots, 
            Response::HTTP_OK,
            [],
            ['groups' => ['show_pot']]
        );
    }

    /**
     * @Route("/api/pots/{id}", name="api_show_pot", methods = {"GET"})
     */
    public function showPot(Pot $pot = null, TotalCalculator $calculator): Response
    {
        try {
            if (!$pot) {
                throw new Exception('Cette cagnotte n\'existe pas (identifiant erroné)', RESPONSE::HTTP_NOT_FOUND);
            }
            $this->denyAccessUnlessGranted('USER', $pot->getUser(), 'Vous n\'avez pas accès à cette cagnotte');
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), $e->getCode());
        }

        //Récupération du total des opérations d'une cagnotte
        $calculator->calculateAmount($pot);

        return $this->json(
            $pot, 
            Response::HTTP_OK,
            [],
            ['groups' => ['show_pot']]
        );
    }

    /**
     * @Route("/api/pots/{id}", name="api_update_pot", methods = {"PATCH"})
     */
    public function updatePot(Pot $pot = null,EntityManagerInterface $doctrine, Request $request, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        $data = $request->getContent();

        try {
            if (!$pot) {
                throw new Exception('Cette cagnotte n\'existe pas (identifiant erroné)', RESPONSE::HTTP_NOT_FOUND);
            }
            $this->denyAccessUnlessGranted('USER', $pot->getUser(), 'Vous n\'avez pas accès à cette cagnotte');
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), $e->getCode());
        }

        try {
            $newPot = $serializer->deserialize($data, Pot::class, "json");
            $newPot->setUser($this->getUser());
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $pot
            ->setName($newPot->getName())
            ->setDateGoal($newPot->getDateGoal())
            ->setAmountGoal($newPot->getAmountGoal())
            ->setUpdatedAt(new \DateTime)
        ;
        $errors = $validator->validate($newPot);

        if (count($errors) > 0) {
            $myJsonError = new JsonError(Response::HTTP_UNPROCESSABLE_ENTITY, "Des erreurs de validation ont été trouvées");
            $myJsonError->setValidationErrors($errors);
            return $this->json($myJsonError, $myJsonError->getError());
        }

        $doctrine->flush();    
        
        return $this->json(
            $pot, 
            Response::HTTP_OK,
            [],
            ['groups' => ['show_pot']]
        );
    }
}