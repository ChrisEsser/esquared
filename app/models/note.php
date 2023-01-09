<?php

/**
 * Class Note
 *
 * MAGIC METHODS
 * @method \Property getProperty()
 * @method \User getUser()
 */
class Note extends BaseModel
{

    public $note_id;
    public $property_id;
    public $note;
    public $type;
    public $status;
    public $created;
    public $created_by;
    public $updated;
    public $deleted;

    protected static $_tableName = 'notes';
    protected static $_primaryKey = 'note_id';
    protected static $_relations = [];

    private $cache = [];

    protected static $_tableFields = [
        'property_id',
        'note',
        'type',
        'status',
        'created',
        'created_by',
        'updated',
        'deleted',
    ];

    protected static function defineRelations()
    {
        self::addRelationOneToOne('property_id', 'Property', 'property_id');
        self::addRelationOneToOne('created_by', 'User', 'user_id');
    }

    public function typeStrings()
    {
        return [
            0 => 'Standard Note',
            1 => 'To Do',
        ];
    }

    public function statusStrings()
    {
        return [
            0 => 'Incomplete',
            1 => 'In Progress',
            2 => 'Completed',
        ];
    }

}