<?php
/**
 * Created by PhpStorm.
 * User: smkoyan
 * Date: 3/7/2018
 * Time: 11:42
 */

namespace App\Controller;

use App\Entity\PhoneNumber;
use App\Entity\User;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends Controller
{
    /**
     * @Route("/api/users", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createUser(Request $request) {
        $params = json_decode( $request->getContent() );

        $firstName = $params->firstName;
        $lastName = $params->lastName;

        $entityManager = $this->getDoctrine()->getManager();

        $user = new User();
        $user->setFirstName($firstName);
        $user->setLastName($lastName);

        $entityManager->persist($user);

        try {
            $entityManager->flush();
        } catch (ORMException $ex) {
            return $this->json([
                'success' => false,
                'message' => 'Fail to create user, try again later'
            ], 500);
        }

        return $this->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->getId()
                ]
            ]
        ], 201);
    }

    /**
     * @Route("/api/users", methods={"GET"})
     *
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function getAllUsers(SerializerInterface $serializer) {
        $repository = $this->getDoctrine()->getRepository(User::class);

        $users = $repository->findAll();

        $response = $serializer->serialize([
            'success' => true,
            'data' => [
                'users' => $users
            ]
        ], 'json');

        return new Response($response,200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("/api/users/{userId}", methods={"GET"}, requirements={
     *     "userId"="\d+"
     * })
     *
     * @param SerializerInterface $serializer
     * @param $userId
     * @return JsonResponse|Response
     */
    public function getUserById(SerializerInterface $serializer, $userId) {
        $repository = $this->getDoctrine()->getRepository(User::class);

        $user = $repository->find($userId);

        if ( is_null($user) ) {
            return $this->json([
                'success' => false,
                'message' => "User with id => $userId does not exist"
            ], 404);
        }

        $response = $serializer->serialize([
            'success' => true,
            'data' => [
                'user' => $user
            ]
        ], 'json');

        return new Response($response, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("/api/users/{userId}", methods={"DELETE"}, requirements={
     *     "userId"="\d+"
     * })
     *
     * @param $userId
     * @return JsonResponse
     */
    public function deleteUserById($userId) {
        $entityManager = $this->getDoctrine()->getManager();
        $repository = $entityManager->getRepository(User::class);

        $user = $repository->find($userId);

        if ( is_null($user) ) {
            return $this->json([
                'success' => false,
                'message' => "User with id => $userId does not exist"
            ], 404);
        }

        $entityManager->remove($user);

        try {
            $entityManager->flush();
        } catch (ORMException $ex) {
            return $this->json([
                'success' => false,
                'message' => 'Fail to delete user, try again later'
            ], 500);
        }

        return $this->json([
           'success' => true
        ], 200);
    }

    /**
     * @Route("/api/users/{userId}", methods={"PUT"}, requirements={
     *     "userId"="\d+"
     * })
     *
     * @param Request $request
     * @param $userId
     *
     * @return JsonResponse
     */
    public function updateUserById(Request $request, $userId) {
        $params = json_decode( $request->getContent() );

        $entityManager = $this->getDoctrine()->getManager();
        $repository = $entityManager->getRepository(User::class);

        $user = $repository->find($userId);
        if ( is_null($user) ) {
            return $this->json([
                'success' => false,
                'message' => "User with id => $userId does not exist"
            ], 404);
        }

        if ( isset($params->firstName) ) {
            $user->setFirstName($params->firstName);
        }
        if ( isset($params->lastName) ) {
            $user->setLastName($params->lastName);
        }

        try {
            $entityManager->flush();
        } catch (ORMException $ex) {
            return $this->json([
                'success' => false,
                'message' => 'Fail to update user, try again later'
            ], 500);
        }

        return $this->json([
            'success' => true
        ], 200);
    }

    /**
     * @Route("/api/users/{userId}/numbers", methods={"POST"}, requirements={
     *     "userId"="\d+"
     * })
     *
     * @param Request $request
     * @param $userId
     * @return JsonResponse
     */
    public function createUserPhoneNumber(Request $request, $userId) {
        $params = json_decode( $request->getContent() );

        $phoneNumber = $params->number;

        $entityManager = $this->getDoctrine()->getManager();
        $repository = $entityManager->getRepository(User::class);

        $user = $repository->find($userId);
        if ( is_null($user) ) {
            return $this->json([
                'success' => false,
                'message' => "User with id => $userId does not exist"
            ], 404);
        }

        $number = new PhoneNumber();
        $number->setNumber($phoneNumber);
        $number->setUser($user);

        $entityManager->persist($number);

        try {
            $entityManager->flush();
        } catch (ORMException $ex) {
            return $this->json([
                'success' => false,
                'message' => 'Fail to create user phone number, try again later'
            ], 500);
        }


        return $this->json([
            'success' => true,
            'number' => [
                'id' => $number->getId()
            ]
        ], 201);
    }

    /**
     * @Route("/api/users/{userId}/numbers", methods={"GET"}, requirements={
     *     "userId"="\d+"
     * })
     *
     * @param SerializerInterface $serializer
     * @param $userId
     * @return Response
     */
    public function getUserPhoneNumbers(SerializerInterface $serializer, $userId) {
        $repository = $this->getDoctrine()->getRepository(PhoneNumber::class);

        $numbers = $repository->findByUserId($userId);

        $response = $serializer->serialize([
            'success' => true,
            'data' => [
                'numbers' => $numbers
            ]
        ], 'json');

        return new Response($response, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("/api/users/{userId}/numbers", methods={"DELETE"}, requirements={
     *     "userId"="\d+"
     * })
     *
     * @param $userId
     * @return JsonResponse
     */
    public function deleteUserPhoneNumbers($userId) {
        $repository = $this->getDoctrine()->getRepository(PhoneNumber::class);

        $repository->DeleteByUserId($userId);

        return $this->json([
            'success' => true
        ], 200);
    }
}




