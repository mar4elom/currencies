<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CurrencyRepository extends EntityRepository
{
    /*  
        Count total rows in table currency
    */
    public function findTotalCount()
    {
        $qb = $this->createQueryBuilder('c');
        return $qb
            ->select('count(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /*  
        Count total rows in table currency where current date is not equal row date
    */
    public function findDateCount()
    {
        $qb = $this->createQueryBuilder('c');
        return $qb
            ->select('count(c.id)')
            ->where('c.date <> CURRENT_DATE()')
            ->getQuery()
            ->getSingleScalarResult();
    }
}