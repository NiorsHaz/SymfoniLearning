<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Task::class);
    }

    public function restoreTask(int $taskId): void
    {
        $qb = $this->createQueryBuilder('t');

        $qb->update('App\Entity\Task', 't')
            ->set('t.deletedAt', ':null')
            ->where('t.id = :id')
            ->setParameter('null', null)
            ->setParameter('id', $taskId)
            ->getQuery()
            ->execute();
    }

    public function findTotalEstimates(bool $isAdmin, bool $deleted,string $title = '', int $minEstimate = 0, int $maxEstimate = 10000, int $project_id, int $category_id): int
    {
        $qb = $this->createQueryBuilder('t')
            ->select('SUM(t.estimates)');

        $query = $this->createQueryWithFilters($qb, $isAdmin, $deleted, $title, $minEstimate, $maxEstimate, $project_id, $category_id);

        $total = $query->getSingleScalarResult();
        if($total != null){
            return $total;
        }
        else return 0;

    }

    /**
     * Retourne un tableau de tâches filtrées par titre et estimation.
     * 
     * @param string $title Le titre à rechercher (optionnel)
     * @param int $minEstimate La valeur minimale pour estimates
     * @param int $maxEstimate La valeur maximale pour estimates
     * @return Task[] Un tableau d'objets Task
     */
    public function findByFilters(bool $isAdmin, string $title = '', int $minEstimate, int $maxEstimate, int $project_id, bool $deleted): array
    {
        $qb = $this->createQueryBuilder('t');

        $query = $this->createQueryWithFilters($qb, $isAdmin, $deleted ,$title, $minEstimate, $maxEstimate, $project_id);

        return $query->getResult();
    }

    private function createQueryWithFilters(QueryBuilder $qb, bool $isAdmin = false, bool $deleted = false, string $title = '', int $minEstimate = 0, int $maxEstimate = 10000, int $project_id = 0, int $category_id = 0): Query
    {
        if ($isAdmin) {
            $qb->andWhere('t.deletedAt IS NULL');
        }

        if ($deleted){
            $qb->andWhere('t.deletedAt IS NOT NULL');
        }
        else{
            $qb->andWhere('t.deletedAt IS NULL');
        }

        // Rechercher par titre (si renseigné)
        if ($title) {
            $qb->andWhere('t.title LIKE :title')
                ->setParameter('title', '%' . $title . '%');
        }
        if($project_id > 0){
            $qb->andWhere('t.project = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if($category_id > 0){
            $qb->andWhere('t.Category = :category_id')
                ->setParameter('category_id', $category_id);
        }

        // Filtrer par estimates (min/max)
        $qb->andWhere('t.estimates BETWEEN :min AND :max')
            ->setParameter('min', $minEstimate)
            ->setParameter('max', $maxEstimate);

        return $qb->getQuery();
    }

    // Use Paginator
    public function paginateWithPaginatorTask(int $page = 1, int $limit = 2): Paginator
    {
        $sql = $this->createQueryBuilder('t')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->setHint(Paginator::HINT_ENABLE_DISTINCT, false); // Désactive l'ajout automatique de distinct dans la requete
        return new Paginator($sql, false); // second params (false) desactive left join
    }

    // Use KnpPaginatorBundle
    public function paginateTask(bool $isAdmin, bool $deleted,string $title = '', int $minEstimate = 0, int $maxEstimate = 10000, int $page = 1, int $limit = 2, int $category_id): PaginationInterface
    {
        $qb = $this->createQueryBuilder('t')->leftJoin('t.project', 'p')->select('t', 'p');
        $query = $this->createQueryWithFilters($qb, $isAdmin, $deleted, $title, $minEstimate, $maxEstimate, 0 ,$category_id);
        return $this->paginator->paginate($query, $page, $limit, ['distinct' => true, 'sortFieldAllowList' => ['t.id']]);
    }

    //    public function findOneBySomeField($value): ?Task
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
