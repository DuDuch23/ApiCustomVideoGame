<?php

namespace App\Repository\Traits;

trait PaginationTrait
{
    public function findAllWithPagination($page, $limit){
        $qb = $this->createQueryBuilder("p")
        ->setFirstResult(($page - 1) * $limit)
        ->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }
}