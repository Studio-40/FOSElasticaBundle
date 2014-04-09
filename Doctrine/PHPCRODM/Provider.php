<?php

namespace FOS\ElasticaBundle\Doctrine\PHPCRODM;

use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use FOS\ElasticaBundle\Doctrine\AbstractProvider;
use FOS\ElasticaBundle\Exception\InvalidArgumentTypeException;

class Provider extends AbstractProvider
{
    const ENTITY_ALIAS = 'a';

    /**
     * @see FOS\ElasticaBundle\Doctrine\AbstractProvider::countObjects()
     */
    protected function countObjects($queryBuilder)
    {
        if (!$queryBuilder instanceof QueryBuilder) {
            throw new InvalidArgumentTypeException($queryBuilder, 'Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder');
        }

        /* Clone the query builder before altering its field selection and DQL,
         * lest we leave the query builder in a bad state for fetchSlice().
         */
        $qb = clone $queryBuilder;

        return sizeof($qb->getQuery()->getResult());
    }

    /**
     * @see FOS\ElasticaBundle\Doctrine\AbstractProvider::fetchSlice()
     */
    protected function fetchSlice($queryBuilder, $limit, $offset)
    {
        if (!$queryBuilder instanceof QueryBuilder) {
            throw new InvalidArgumentTypeException($queryBuilder, 'Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder');
        }

        $queryBuilder->setFirstResult($offset);
        $queryBuilder->setMaxResults($limit);

        return $queryBuilder->getQuery()
            ->getResult()->toArray();
    }

    /**
     * @see FOS\ElasticaBundle\Doctrine\AbstractProvider::createQueryBuilder()
     */
    protected function createQueryBuilder()
    {
        return $this->managerRegistry
            ->getManagerForClass($this->objectClass)
            ->getRepository($this->objectClass)
            // ORM query builders require an alias argument
            ->{$this->options['query_builder_method']}(static::ENTITY_ALIAS);
    }
}
