<?php

namespace App\Controller;

use App\Entity\PhoneNumber;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Serializer\SerializerInterface;

class PhoneNumberController extends Controller
{
    /**
     * @Route("/api/numbers/{numberId}", methods={"GET"}, requirements={
     *     "numberId"="\d+"
     * })
     *
     * @param SerializerInterface $serializer
     * @param $numberId
     * @return JsonResponse|Response
     */
    public function getNumberById(SerializerInterface $serializer, $numberId) {
        $repository = $this->getDoctrine()->getRepository(PhoneNumber::class);

        $number = $repository->find($numberId);
        if ( is_null($number) ) {
            return $this->json([
                'success' => false,
                'message' => "Number with id => $numberId does not exist"
            ], 404);
        }

        $response = $serializer->serialize([
            'success' => true,
            'data' => [
                'phoneNumber' => $number
            ]
        ], 'json');

        return new Response($response, 200, [
           'Content-Type' => 'application/json'
        ]);
    }


    /**
     * @Route("/api/numbers/{numberId}", methods={"PUT"}, requirements={
     *     "numberId"="\d+"
     * })
     *
     * @param Request $request
     * @param $numberId
     *
     * @return JsonResponse
     */
    public function updateNumberById(Request $request, $numberId) {
        $params = json_decode( $request->getContent() );

        $number = $params->number;

        $entityManager = $this->getDoctrine()->getManager();
        $repository = $entityManager->getRepository(PhoneNumber::class);

        $phoneNumber = $repository->find($numberId);
        if ( is_null($number) ) {
            return $this->json([
                'success' => false,
                'message' => "Phone number with id => $numberId does not exist"
            ], 404);
        }

        $phoneNumber->setNumber($number);

        try {
            $entityManager->flush();
        } catch (ORMException $ex) {
            return $this->json([
                'success' => false,
                'message' => 'Fail to update phone number, try again later'
            ], 500);
        }

        return $this->json([
            'success' => true
        ], 200);
    }


    /**
     * @Route("/api/numbers/{numberId}", methods={"DELETE"}, requirements={
     *     "numberId"="\d+"
     * })
     *
     * @param $numberId
     * @return JsonResponse
     */
    public function deleteNumberById($numberId) {
        $entityManager = $this->getDoctrine()->getManager();
        $repository = $entityManager->getRepository(PhoneNumber::class);

        $phoneNumber = $repository->find($numberId);
        if ( is_null($phoneNumber) ) {
            return $this->json([
                'success' => false,
                'message' => "Phone number with id => $numberId does not exist"
            ], 404);
        }

        $entityManager->remove($phoneNumber);

        try {
            $entityManager->flush();
        } catch (ORMException $ex) {
            return $this->json([
                'success' => false,
                'message' => 'Fail to delete phone number, try again later'
            ], 500);
        }

        return $this->json([
            'success' => true
        ], 200);
    }
}
