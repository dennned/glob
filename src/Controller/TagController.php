<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\PersistentCollection;

class TagController extends AbstractController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showTagsCloud()
    {
        $repositoryTags = $this->getDoctrine()->getRepository(Tag::class);

        $tags = [];
        $post = new Post();

//        $t = new PersistentCollection();

        foreach ($repositoryTags->findAll() as $tag) {
            $tags[$tag->getId()] = $tag;
        }

        $getParams = $_GET['get'] ?? null;
        $tagIdSelected = null;

        if(null !== $getParams){
            $tagIdSelected = intval($getParams['tag']) ? intval($getParams['tag']) : $tagIdSelected;
        }

        return $this->render('blog/_tag_block.html.twig', [
            'post' => $post,
        ]);
    }
}
