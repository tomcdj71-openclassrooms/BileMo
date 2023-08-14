<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('/users', name: 'user_')]
class CustomerController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer l'ensemble des utilisateurs d'un client.
     */
    #[Route(name: 'get_collection', methods: [Request::METHOD_GET])]
    public function getCustomersList(CustomerRepository $customerRepository, Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $this->getUser();
        $limit = $request->query->getInt('limit', 10);
        $page = $request->query->getInt('page', 1);
        $customers = $customerRepository->findAllWithPagination($client, $page, $limit);
        $total = $customerRepository->count(['client' => $client]);
        $pages = (int) ceil($total / $limit);
        $currentPage = $page;
        $totalPages = $pages;
        $customersJson = [];
        foreach ($customers as $customer) {
            $customersJson[] = [
                'id' => $customer->getId(),
                'firstName' => $customer->getFirstName(),
                'lastName' => $customer->getLastName(),
                'email' => $customer->getEmail(),
                '_links' => [
                    'self' => [
                        'href' => $this->generateUrl('user_get_item', ['id' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                    'user' => [
                        'href' => $this->generateUrl('user_get_item', ['id' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                    'delete' => [
                        'href' => $this->generateUrl('user_delete_item', ['id' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                    'put' => [
                        'href' => $this->generateUrl('user_put_item', ['id' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                ],
            ];
        }
        $links = [
            'self' => $this->generateUrl('user_get_collection', ['page' => $currentPage, 'limit' => $limit], UrlGeneratorInterface::ABSOLUTE_URL),
            'post' => $this->generateUrl('user_post_item', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
        if ($currentPage > 1) {
            $links['first'] = $this->generateUrl('user_get_collection', ['page' => 1, 'limit' => $limit], UrlGeneratorInterface::ABSOLUTE_URL);
            $links['previous'] = $this->generateUrl('user_get_collection', ['page' => $currentPage - 1, 'limit' => $limit], UrlGeneratorInterface::ABSOLUTE_URL);
        }
        if ($currentPage < $totalPages) {
            $links['next'] = $this->generateUrl('user_get_collection', ['page' => $currentPage + 1, 'limit' => $limit], UrlGeneratorInterface::ABSOLUTE_URL);
            $links['last'] = $this->generateUrl('user_get_collection', ['page' => $totalPages, 'limit' => $limit], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $this->json(
            [
                '_embedded' => [
                    'users' => $customersJson,
                ],
                'page' => $currentPage,
                'pages' => $totalPages,
                'limit' => $limit,
                'count' => count($customersJson),
                'total' => $total,
                '_links' => $links,
            ],
            Response::HTTP_OK,
            [],
            [
                AbstractNormalizer::GROUPS => ['getCustomer'],
            ]
        )->setSharedMaxAge(3600);
    }

    /**
     * Cette méthode permet de récupérer le détail d'un utilisateur.
     */
    #[Route('/{id}', name: 'get_item', methods: [Request::METHOD_GET], requirements: ['id' => '\d+'])]
    public function getDetailCustomer(Customer $customer): JsonResponse
    {
        $loggedClient = $this->getUser();
        if ($customer->getClient() !== $loggedClient) {
            return new JsonResponse(['message' => 'Customer not found.'], Response::HTTP_NOT_FOUND);
        }
        $customerId = $customer->getId();

        return $this->json(
            [
                'id' => $customerId,
                'firstName' => $customer->getFirstName(),
                'lastName' => $customer->getLastName(),
                'email' => $customer->getEmail(),
                '_links' => [
                    'self' => [
                        'href' => $this->generateUrl('user_get_item', ['id' => $customerId], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                    'get' => [
                        'href' => $this->generateUrl('user_get_item', ['id' => $customerId], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                    'put' => [
                        'href' => $this->generateUrl('user_put_item', ['id' => $customerId], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                    'delete' => [
                        'href' => $this->generateUrl('user_delete_item', ['id' => $customerId], UrlGeneratorInterface::ABSOLUTE_URL),
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
     * Cette méthode permet de supprimer un utilisateur.
     */
    #[Route('/{id}', name: 'delete_item', methods: [Request::METHOD_DELETE], requirements: ['id' => '\d+'])]
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
    #[Route(name: 'post_item', methods: [Request::METHOD_POST])]
    public function createCustomer(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $loggedClient = $this->getUser();
        if (!$loggedClient instanceof \Symfony\Component\Security\Core\User\UserInterface) {
            return new JsonResponse(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        $data = json_decode($request->getContent(), true);
        try {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return new JsonResponse(['message' => 'Invalid JSON format. Maybe you missed double quotes somewhere ?'], Response::HTTP_BAD_REQUEST);
        }
        $customer = new Customer();
        $customer->setFirstName($data['firstName']);
        $customer->setLastName($data['lastName']);
        $customer->setEmail($data['email']);
        $customer->setClient($loggedClient);
        $errors = $validator->validate($customer);
        if ($errors->count() > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $propertyPath = $error->getPropertyPath();
                $errorMessage = $error->getMessage();
                $fieldErrorMessage = sprintf('%s', $errorMessage);
                
                $errorMessages[$propertyPath] = $fieldErrorMessage;
            }
            return new JsonResponse($errorMessages, Response::HTTP_BAD_REQUEST);
        }
        $em->persist($customer);
        $em->flush();

        return $this->json(
            $customer,
            Response::HTTP_CREATED,
            [
                'content-type' => 'application/hal+json',
                'location' => $this->generateUrl(
                    'user_get_item',
                    ['id' => $customer->getId()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ],
            ['groups' => ['getCustomer']]
        );
    }

    /**
     * Cette méthode permet de mettre à jour un utilisateur.
     */
    #[Route('/{id}', name: 'put_item', methods: [Request::METHOD_PUT], requirements: ['id' => '\d+'])]
    public function updateCustomer(Request $request, SerializerInterface $serializer, Customer $currentCustomer, EntityManagerInterface $em, ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse
    {
        $loggedClient = $this->getUser();
        if ($currentCustomer->getClient() !== $loggedClient) {
            throw $this->createNotFoundException('Customer not found.');
        }
        $newCustomer = $serializer->deserialize($request->getContent(), Customer::class, 'json');
        $currentCustomer->setFirstName($newCustomer->getFirstName());
        $currentCustomer->setLastName($newCustomer->getLastName());
        $currentCustomer->setEmail($newCustomer->getEmail());
        $errors = $validator->validate($currentCustomer);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        $currentCustomer->setClient($loggedClient->getId());
        $em->persist($currentCustomer);
        $em->flush();
        $cache->invalidateTags(['customersCache']);

        return $this->json(
            null,
            Response::HTTP_NO_CONTENT
        );
    }
}
