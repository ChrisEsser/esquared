<?php

/**
 * Class Lease
 *
 * MAGIC METHODS
 * @method \Unit getUnit()
 */
class Lease extends BaseModel
{
    public $lease_id;
    public $unit_id;
    public $start_date;
    public $end_date;
    public $rent;
    public $rent_frequency;

    protected static $_tableName = 'leases';
    protected static $_primaryKey = 'lease_id';
    protected static $_relations = [];

    private $cache = [];

    protected static $_tableFields = [
        'unit_id',
        'start_date',
        'end_date',
        'rent',
        'rent_frequency',
    ];

    protected static function defineRelations()
    {
        self::addRelationOneToOne('unit_id', 'Unit', 'unit_id');
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
