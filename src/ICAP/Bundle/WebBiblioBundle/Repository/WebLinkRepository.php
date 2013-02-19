<?php

namespace ICAP\Bundle\WebBiblioBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * WebLinkRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class WebLinkRepository extends EntityRepository
{
    const TAGNAMES_PROPERTY = "tagnames";
    const USERNAME_PROPERTY = "usernames";

    public function customSearch($params)
    {
        $qb = $this->getPublishedWebLinksQueryBuilder();
        foreach ($params as $key => $value) {
            if($key == self::TAGNAMES_PROPERTY){
                $qb
                    ->leftJoin('weblink.tags', 'tag')
                    ->andWhere(
                        $qb
                            ->expr()
                            ->in('tag.name', $value)
                    )
                ;
            }else if($key == self::USERNAME_PROPERTY){
                $qb
                    ->andWhere(
                        $qb
                            ->expr()
                            ->in('weblink.username', $value)
                    )
                ;
            } 
        }

        return $qb->getQuery()->getResult();
    }

    public function getPublishedWebLinksQueryBuilder()
    {
        return $this
            ->createQueryBuilder('weblink')
            ->andWhere('weblink.published = :value')
            ->setParameter('value', true)
            ->orderBy('weblink.url', 'ASC')
        ;
    }

    public function getPublishedWebLinksQuery()
    {
        return $this->getPublishedWebLinksQueryBuilder()->getQuery();
    }

    public function getPublishedWebLinks()
    {
        return $this->getPublishedWebLinksQuery()->getResults();
    }

    public function getWebLinksQueryBuilderForUsername($username)
    {
        return $this
            ->createQueryBuilder('webLink')
            ->andWhere('webLink.username = :username')
            ->setParameter('username', $username)
        ;
    }
}
