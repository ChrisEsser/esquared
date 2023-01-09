<?php

/**
 * Class Unit
 *
 * MAGIC METHODS
 * @method \Property getProperty()
 * @method \User[] getRenter()
 */
class Unit extends BaseModel
{

    public $unit_id;
    public $property_id;
    public $name;
    public $description;
    public $status;
    public $rent;
    public $rent_frequency;
	public $created;
    public $updated;
    public $deleted;

    protected static $_tableName = 'units';
    protected static $_primaryKey = 'unit_id';
    protected static $_relations = [];

    private $cache = [];

    protected static $_tableFields = [
        'property_id',
        'name',
        'description',
        'status',
        'rent',
        'rent_frequency',
        'created',
        'updated',
        'deleted',
    ];

    protected static function defineRelations()
    {
        self::addRelationOneToOne('property_id', 'Property', 'property_id');
        self::addRelationOneToMany('unit_id', 'User', 'unit_id', 'Renter');
    }

    public function statusStrings()
    {
        return [
            0 => 'Unknown',
            1 => 'Occupied',
            2 => 'Available',
            3 => 'In Rehab',
        ];
    }

    public function rentFrequencyStrings()
    {
        return [
            0 => 'monthly',
            1 => 'bi-monthly',
            2 => 'annual',
        ];
    }


}