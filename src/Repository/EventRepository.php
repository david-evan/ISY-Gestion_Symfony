<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

use App\Entity\Customer;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Event::class);
    }

    
    public function getEventsForCustomer(Customer $customer, int $page, int $nbPerPage = 10)
    {
        $query =  $this ->createQueryBuilder('e')
                        ->andWhere('e.customer = :customer')
                        ->setParameter('customer', $customer)
                        ->orderBy('e.dateCreate', 'DESC')
                        ->setFirstResult(($page-1) * $nbPerPage)
                        ->setMaxResults($nbPerPage)
                        ;
        
        return new Paginator($query, true);
    }


}
