<?php
namespace App\Controller;

use App\Entity\Post;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SitemapController
 */
class SitemapController extends AbstractController {

    /**
     * @param string $template
     * @param Request $request
     * @return Response
     */
    public function sitemap(string $template, Request $request): Response
    {
        $urls = [];
        $hostname = $request->getHost();

        $urls[] = ['loc' => $this->get('router')->generate('homepage'), 'changefreq' => 'weekly', 'priority' => '1.0'];

        $articlesRepository = $this->getDoctrine()->getRepository(Post::class);
        $articles = $articlesRepository->findAll();

        foreach ($articles as $article) {
            $urls[] = ['loc' => $this->get('router')->generate('blog_post', ['slug' => $article->getSlug()]), 'changefreq' => 'weekly', 'priority' => '1.0'];
        }

        // Once our array is filled, we define the controller response
        $response = new Response();
        $response->headers->set('Content-Type', 'xml');

        return $this->render($template, [
            'urls' => $urls,
            'hostname' => $hostname
        ]);
    }
}