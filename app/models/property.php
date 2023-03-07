<?php

/**
 * Class Property
 *
 * MAGIC METHODS
 * @method Unit[] getUnit()
 * @method Document[] getDocument()
 * @method \Note[] getNote()
 */
class Property extends BaseModel
{

    public $property_id;
    public $name;
    public $type;
    public $description;
    public $status;
    public $purchase_price;
    public $purchase_date;
    public $created;
    public $updated;
    public $deleted;

    protected static $_tableName = 'properties';
    protected static $_primaryKey = 'property_id';
    protected static $_relations = [];

    private $cache = [];

    protected static $_tableFields = [
        'name',
        'type',
        'description',
        'status',
        'purchase_price',
        'purchase_date',
        'created',
        'updated',
        'deleted',
    ];

    protected static function defineRelations()
    {
        self::addRelationOneToMany('property_id', 'Unit', 'property_id');
        self::addRelationOneToMany('property_id', 'Document', 'property_id');
        self::addRelationOneToMany('property_id', 'Note', 'property_id');
    }

    public function typeStrings()
    {
        return [
            0 => 'Single Family',
            1 => 'Multi - Residential',
            2 => 'Multi - Commercial',
            3 => 'Multi - Mixed',
        ];
    }

}