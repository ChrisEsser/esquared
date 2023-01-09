<?php

/**
 * Class Document
 *
 * MAGIC METHODS
 * @method User getUser()
 * @method User getOwner()
 * @method \Property getProperty()
 */
class Document extends BaseModel
{

    public $document_id;
    public $name;
    public $type;
    public $description;
    public $user_id;
    public $created;
    public $updated;
    public $deleted;
    public $owner_id;
    public $property_id;
    public $amount;
    public $document_date;

    protected static $_tableName = 'documents';
    protected static $_primaryKey = 'document_id';
    protected static $_relations = [];

    private $cache = [];

    protected static $_tableFields = [
        'name',
        'type',
        'description',
        'user_id',
        'created',
        'updated',
        'deleted',
        'owner_id',
        'property_id',
        'amount',
        'document_date',
    ];

    protected static function defineRelations()
    {
        self::addRelationOneToOne('user_id', 'User', 'user_id');
        self::addRelationOneToOne('owner_id', 'User', 'user_id', [], 'Owner');
        self::addRelationOneToOne('property_id', 'Property', 'property_id');
    }

    public function typeStrings()
    {
        return [
            0 => 'General',
            1 => 'Utility',
            2 => 'Invoice',
        ];
    }

}