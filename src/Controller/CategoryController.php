<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoryController extends AbstractController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showCategories()
    {
        $repositoryCategories = $this->getDoctrine()->getRepository(Category::class);

        $categories = [];
        foreach ($repositoryCategories->findAll() as $category) {
            $categories[$category->getId()]['category'] = $category;
            $categories[$category->getId()]['count'] = $this->getCountCategories($category->getId());
        }

        $getParams = $_GET['get'] ?? null;
        $categoryIdSelected = null;

        if(null !== $getParams){
            $categoryIdSelected = intval($getParams['category']) ? intval($getParams['category']) : $categoryIdSelected;
        }

        return $this->render('blog/_category_block.html.twig', [
            'categories' => $categories,
            'categoryIdSelected' => $categoryIdSelected,
        ]);
    }

    /**
     * @param $idCategory
     * @return int
     */
    protected function getCountCategories($idCategory)
    {
        $repositoryPost = $this->getDoctrine()->getRepository(Post::class);

        return count($repositoryPost->findBy(['category' => $idCategory]));
    }
}
