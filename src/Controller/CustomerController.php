<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\ClientRepository;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;


class CustomerController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer l'ensemble des utilisateurs d'un client.
     */
    #[Route('/users', name: 'customers', methods: ['GET'])]
    public function getCustomersList(CustomerRepository $customerRepository, Request $request, SerializerInterface $serializer, TagAwareCacheInterface $cachePool): JsonResponse
    {
        /** @var Client $client */
        $client = $this->getUser();
        $limit = $request->query->getInt('limit', 10);
        $page = $request->query->getInt('page', 1);
        try {
            $customers = $customerRepository->findBy(
                ['client' => $client],
                ['id' => 'asc'],
                $limit,
                ($page - 1) * $limit
            );
        } catch (\Exception $e) {
            dump('Exception occurred: ' . $e->getMessage());
        }
        $total = $customerRepository->count(['client' => $client]);
        $pages = (int) ceil($total / $limit);
        return $this->json(
            [
                '_embedded' => [
                    'users' => $customers,
                ],
                'page' => $page,
                'pages' => $pages,
                'limit' => $limit,
                'count' => count($customers),
                'total' => $total,
                '_links' => [
                    'self' => [
                        'href' => $this->generateUrl('customers', ['page' => $page, 'limit' => $limit], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                    'post' => [
                        'href' => $this->generateUrl('createCustomer', [], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                    'first' => [
                        'href' => $this->generateUrl('customers', ['page' => 1, 'limit' => $limit], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                    'next' => [
                        'href' => $this->generateUrl('customers', ['page' => $page + 1, 'limit' => $limit], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                    'previous' => [
                        'href' => $this->generateUrl('customers', ['page' => $page - 1, 'limit' => $limit], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                    'last' => [
                        'href' => $this->generateUrl('customers', ['page' => $pages, 'limit' => $limit], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                ],
            ],
            Response::HTTP_OK,
            [],
            [
                AbstractNormalizer::GROUPS => ['getCustomer'],
            ]
        );
    }

    /**
     * Cette méthode permet de récupérer le détail d'un utilisateur.
     */
    #[Route('/users/{id}', name: 'detailCustomer', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getDetailCustomer(Customer $customer, SerializerInterface $serializer): JsonResponse
    {
        $loggedClient = $this->getUser();
        if ($customer->getClient() !== $loggedClient) {
            return new JsonResponse(['message' => 'Customer not found.'], Response::HTTP_NOT_FOUND);
        }

        $jsonCustomer = [
            'firstName' => $customer->getFirstName(),
            'lastName' => $customer->getLastName(),
            'email' => $customer->getEmail(),
        ];

        return new JsonResponse($jsonCustomer, Response::HTTP_OK, []);
    }

    /**
     * Cette méthode permet de supprimer un utilisateur.
     */
    #[Route('/users/{id}', name: 'deleteCustomer', methods: ['DELETE'])]
    public function deleteCustomer(Customer $customer, EntityManagerInterface $em): JsonResponse
    {
        $loggedClient = $this->getUser();
        if ($customer->getClient() !== $loggedClient) {
            throw $this->createNotFoundException('Customer not found.');
        }
        $em->remove($customer);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Cette méthode permet de créer un utilisateur.
     */
    #[Route('/users', name: 'createCustomer', methods: ['POST'])]
    public function createCustomer(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ClientRepository $clientRepository): JsonResponse
    {
        $loggedClient = $this->getUser();
        if (null === $loggedClient) {
            return new JsonResponse(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        $customer = $serializer->deserialize($request->getContent(), Customer::class, 'json');
        $content = $request->toArray();
        $idClient = $content['idClient'] ?? -1;
        $customer->setClient($loggedClient);
        $em->persist($customer);
        $em->flush();
        $context = SerializationContext::create()->setGroups(['getCustomer']);
        $jsonCustomer = $serializer->serialize($customer, 'json', $context);
        $location = $urlGenerator->generate('detailCustomer', ['id' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCustomer, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    /**
     * Cette méthode permet de mettre à jour un utilisateur.
     */
    public function updateCustomer(Request $request, SerializerInterface $serializer, Customer $currentCustomer, EntityManagerInterface $em, ClientRepository $clientRepository, ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse {
        $loggedClient = $this->getUser();
        if ($currentCustomer->getClient() !== $loggedClient) {
            throw $this->createNotFoundException('Customer not found.');
        }
        $newCustomer = $serializer->deserialize($request->getContent(), Customer::class, 'json');
        $currentCustomer->setFirstName($newCustomer->getFirstName());
        $currentCustomer->setLastName($newCustomer->getLastName());
        $currentCustomer->setEmail($newCustomer->getEmail());
        $currentCustomer->setClient($newCustomer->getClient());
        $errors = $validator->validate($currentCustomer);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        $currentCustomer->setClient($loggedClient);
        $em->persist($currentCustomer);
        $em->flush();
        $cache->invalidateTags(["customersCache"]);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
