<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\Post;
use App\Entity\Tag;
use App\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * This custom Doctrine repository contains some methods which are useful when
 * querying for blog post information.
 *
 * See https://symfony.com/doc/current/doctrine/repository.html
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @param int $page
     * @param Tag|null $tag
     * @param null $params
     * @return Paginator
     * @throws \Exception
     */
    public function findLatest(int $page = 1, Tag $tag = null, $params = null): Paginator
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('t')
            ->leftJoin('p.tags', 't')
            ->where('p.publishedAt <= :now')
            ->orderBy('p.publishedAt', 'DESC')
            ->setParameter('now', new \DateTime())
        ;

        if (null !== $tag) {
            $qb->andWhere(':tag MEMBER OF p.tags')
                ->setParameter('tag', $tag);
        }

        if($params['categoryId']){
            $qb->andWhere('p.category = :categoryId')
                ->setParameter('categoryId', $params['categoryId']);
        }

        return (new Paginator($qb))->paginate($page);
    }

    /**
     * @param null $tags
     * @param Post $post
     * @param int $limit
     * @return mixed
     */
    public function findRelevantPosts($tags = null, Post $post, int $limit = 3)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.id <> :post')
            ->setParameter('post', $post->getId())
            ->orderBy('RAND()')
            ->setMaxResults($limit);

        // @TODO save for future
        /*if (null !== $tags) {
            $qb->andWhere('t.id IN(:tag)')
                ->setParameter('tag', $tags);
        }*/

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function findBySearchQuery(string $query, int $limit = Post::NUM_ITEMS): array
    {
        $searchTerms = $this->extractSearchTerms($query);

        if (0 === \count($searchTerms)) {
            return [];
        }

        $queryBuilder = $this->createQueryBuilder('p');

        foreach ($searchTerms as $key => $term) {
            $queryBuilder
                ->orWhere('p.title LIKE :t_'.$key)
                ->setParameter('t_'.$key, '%'.$term.'%')
            ;
        }

        return $queryBuilder
            ->orderBy('p.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Transforms the search string into an array of search terms.
     */
    private function extractSearchTerms(string $searchQuery): array
    {
        $searchQuery = trim(preg_replace('/[[:space:]]+/', ' ', $searchQuery));
        $terms = array_unique(explode(' ', $searchQuery));

        // ignore the search terms that are too short
        return array_filter($terms, function ($term) {
            return 2 <= mb_strlen($term);
        });
    }
}
