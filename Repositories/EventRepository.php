<?php


class EventRepository extends \Data\Repository
{
    function __construct(\Data\DatabaseConnection $connection)
    {
        parent::__construct(Event::class, $connection);
    }
}