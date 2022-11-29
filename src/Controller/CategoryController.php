<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProgramRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'category_index')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/category/{categoryName}', methods: ['GET'], name: 'category_show')]
    public function show(string $categoryName, CategoryRepository $categoryRepository, ProgramRepository $programRepository): Response
    {
        $categoryId = $categoryRepository->findOneBy(['name' => $categoryName])->getId();
        $programs = $programRepository->findBy(['category' => $categoryId]);
        if (!$programs) {
            throw $this->createNotFoundException(
                'No program with this category Name : ' . $categoryName . ' found in program\'s table.'
            );
        }
        return $this->render('category/show.html.twig', [
            'programs' => $programs,
        ]);
    }
}
