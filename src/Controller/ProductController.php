<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'products', methods: ['GET'])]
    public function getProductsList(ProductRepository $productRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $productList = $productRepository->findAll();
        $jsonProductList = $serializer->serialize($productList, 'json');

        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    }

    #[Route('/products/{id}', name: 'detailProduct', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getDetailProduct(int $id, SerializerInterface $serializer, ProductRepository $productRepository): JsonResponse
    {
        $product = $productRepository->find($id);
        if ($product instanceof \App\Entity\Product) {
            $jsonProduct = $serializer->serialize($product, 'json');

            return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
