<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TagController
 * @package App\Controller
 */
class TagController extends AbstractController
{
    /**
     * @param TagRepository $tagsRepository
     * @return Response
     */
    public function showTagsCloud(TagRepository $tagsRepository)
    {
        $tags = $tagsRepository->findRandomTags();

        $post = new Post();

        foreach ($tags as $tag) {
            $post->addTag($tag);
        }

        return $this->render('blog/_post_tags.html.twig', [
            'titleBloc' => true,
            'post' => $post,
            'selectedTag' => $_GET['tag'] ?? null
        ]);
    }
}
