<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TagController
 * @package App\Controller
 */
class TagController extends AbstractController
{
    /**
     * @return Response
     */
    public function showTagsCloud()
    {
        $repositoryTags = $this->getDoctrine()->getRepository(Tag::class);

        $post = new Post();

        foreach ($repositoryTags->findAll() as $tag) {
            $post->addTag($tag);
        }

        return $this->render('blog/_post_tags.html.twig', [
            'titleBloc' => true,
            'post' => $post,
        ]);
    }
}
