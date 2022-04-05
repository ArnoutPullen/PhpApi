<?php

/**
 * @Table("event")
 */
class Event
{
    /**
     * @FieldName("id")
     */
    public $id;

    /**
     * @FieldName("name")
     */
    public $name;

    /**
     * @FieldName("description")
     */
    public $description;

    /**
     * @FieldName("start")
     */
    public $start;

    /**
     * @FieldName("end")
     */
    public $end;

    /**
     * @FieldName("created")
     */
    public $created;

    /**
     * @FieldName("lastModified")
     */
    public $lastModified;
}
