<?php

namespace App\Controller\Api;

use App\Entity\Operation;
use App\Entity\Pot;
use App\Models\JsonError;
use App\Service\TotalCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OperationController extends AbstractController
{
        /**
     * @Route("/api/operations", name="api_show_operations", methods = {"GET"})
     */
    public function showOperation(): Response
    {
        return $this->json(
            $this->getUser()->getOperations(), 
            Response::HTTP_OK,
            [],
            ['groups' => ['show_operation']]
        );
    }

    /**
     * @Route("/api/operations", name="api_add_operation", methods = {"POST"})
     */
    public function addOperation(EntityManagerInterface $doctrine, Request $request, SerializerInterface $serializer, ValidatorInterface $validator, TotalCalculator $calculator): Response
    {
        //Deserialisation 
        $data = $request->getContent();
        try {
            $newOperation = $serializer->deserialize($data, Operation::class, "json");
            $newOperation->setUser($this->getUser());
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }


        $errors = $validator->validate($newOperation);

        //Vérification de la cagnotte associée à l'opération
        $pot = $newOperation->getPot();
        try {
            if (!$pot) {
                throw new Exception('Cette cagnotte n\'existe pas (identifiant erroné)', RESPONSE::HTTP_NOT_FOUND);
            }
            //Vérification du solde de la cagnotte en cas de retrait
            if (!$newOperation->getType() && ($newOperation->getAmount() > $calculator->calculateAmount($pot))) {
                throw new Exception('Retrait supérieur au montant de la cagnotte :(', Response::HTTP_BAD_REQUEST);
            }


            $this->denyAccessUnlessGranted('USER', $newOperation->getPot()->getUser(), 'Vous n\'avez pas accès à cette cagnotte');
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), $e->getCode());
        }

        //Vérification des données du formulaire
        $errors = $validator->validate($newOperation);
        if (count($errors) > 0) {
            $myJsonError = new JsonError(Response::HTTP_UNPROCESSABLE_ENTITY, "Des erreurs de validation ont été trouvées");
            $myJsonError->setValidationErrors($errors);
            return $this->json($myJsonError, $myJsonError->getError());
        }

        $doctrine->persist($newOperation);
        $doctrine->flush();

        return $this->json(
            $newOperation, Response::HTTP_CREATED,
            [],
            ['groups' => ['show_operation']]
        );
    }

    /**
     * @Route("/api/pots/{id}/operations", name="api_show_operations_by_pot", methods = {"GET"})
     */
    public function showOperations(Pot $pot = null): Response
    {
        try {
            if (!$pot) {
                throw new Exception('Cette cagnotte n\'existe pas (identifiant erroné)', RESPONSE::HTTP_NOT_FOUND);
            }
            $this->denyAccessUnlessGranted('USER', $pot->getUser(), 'Vous n\'avez pas accès à cette cagnotte');
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), $e->getCode());
        }

        return $this->json(
            $pot->getOperations(), 
            Response::HTTP_OK,
            [],
            ['groups' => ['show_operation']]
        );
    }
}