<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/products', name: 'product_')]
class ProductController extends AbstractController
{
    #[Route(name: 'get_collection', methods: [Request::METHOD_GET])]
    public function getProductsList(ProductRepository $productRepository, Request $request): JsonResponse
    {
        $limit = $request->query->getInt('limit', 10);
        $page = $request->query->getInt('page', 1);
        $products = $productRepository->findAllWithPagination($page, $limit);
        $total = $productRepository->count([]);
        $pages = (int) ceil($total / $limit);
        $currentPage = $page;
        $totalPages = $pages;
        $jsonProductList = [];
        foreach ($products as $product) {
            $jsonProductList[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                '_links' => [
                    'self' => [
                        'href' => $this->generateUrl('product_get_item', ['id' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                    'product' => [
                        'href' => $this->generateUrl('product_get_item', ['id' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                ],
            ];
        }
        $links = [
            'self' => $this->generateUrl('product_get_collection', ['page' => $currentPage, 'limit' => $limit], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
        if ($currentPage > 1) {
            $jsonProductList['_links']['first'] = $this->generateUrl('product_get_collection', ['page' => 1, 'limit' => $limit], UrlGeneratorInterface::ABSOLUTE_URL);
            $jsonProductList['_links']['previous'] = $this->generateUrl('product_get_collection', ['page' => $currentPage - 1, 'limit' => $limit], UrlGeneratorInterface::ABSOLUTE_URL);
        }
        if ($currentPage < $totalPages) {
            $jsonProductList['_links']['next'] = $this->generateUrl('product_get_collection', ['page' => $currentPage + 1, 'limit' => $limit], UrlGeneratorInterface::ABSOLUTE_URL);
            $jsonProductList['_links']['last'] = $this->generateUrl('product_get_collection', ['page' => $totalPages, 'limit' => $limit], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $this->json(
            [
                '_embedded' => [
                    'users' => $jsonProductList,
                ],
                'page' => $currentPage,
                'pages' => $totalPages,
                'limit' => $limit,
                'count' => count($jsonProductList),
                'total' => $total,
                '_links' => $links,
            ],
            Response::HTTP_OK,
            [],
            [
                AbstractNormalizer::GROUPS => ['getProducts'],
            ]
        )->setSharedMaxAge(3600);
    }

    #[Route('/{id}', name: 'get_item', methods: [Request::METHOD_GET], requirements: ['id' => '\d+'])]
    public function getDetailProduct(int $id, SerializerInterface $serializer, ProductRepository $productRepository): JsonResponse
    {
        $product = $productRepository->find($id);
        if (!$product instanceof \App\Entity\Product) {
            return new JsonResponse(['message' => 'Product not found.'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(
            [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                '_links' => [
                    'self' => [
                        'href' => $this->generateUrl('product_get_item', ['id' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                    'product' => [
                        'href' => $this->generateUrl('product_get_item', ['id' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                ],
            ],
            Response::HTTP_OK,
            [],
            [
                AbstractNormalizer::GROUPS => ['getProducts'],
            ]
        )->setSharedMaxAge(3600);
    }
}
