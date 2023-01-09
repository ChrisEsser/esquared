<?php

/**
 * Class UnitHistory
 *
 * MAGIC METHODS
 * @method Unit getUnit()
 */
class UnitHistory extends BaseModel
{
    public $history_id;
    public $unit_id;
    public $date;
    public $note;
    public $type;
    public $created;
    public $updated;
    public $deleted;

    protected static $_tableName = 'unit_history';
    protected static $_primaryKey = 'history_id';
    protected static $_relations = [];

    private $cache = [];

    protected static $_tableFields = [
        'unit_id',
        'date',
        'note',
        'type',
        'created',
        'updated',
        'deleted',
    ];

    protected static function defineRelations()
    {
        self::addRelationOneToOne('unit_id', 'Unit', 'unit_id');
    }

}