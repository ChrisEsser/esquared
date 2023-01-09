<?php

class QbConnect extends BaseModel
{
    public $connect_id;
    public $connect_data;

    protected static $_tableName = 'qb_connect';
    protected static $_primaryKey = 'connect_id';
    protected static $_relations = [];

    private $cache = [];

    protected static $_tableFields = [
        'connect_data',
    ];

    protected static function defineRelations()
    {
    }

}
