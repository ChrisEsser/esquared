<?php

/**
 * Class Expense
 *
 * MAGIC METHODS
 * @method Unit getUnit()
 * @method \Property getProperty()
 */
class Expense extends BaseModel
{

    public $expense_id;
    public $property_id;
    public $unit_id;
    public $amount;
    public $description;
    public $date;

    protected static $_tableName = 'expenses';
    protected static $_primaryKey = 'expense_id';
    protected static $_relations = [];

    private $cache = [];

    protected static $_tableFields = [
        'property_id',
        'unit_id',
        'amount',
        'description',
        'date',
    ];

    protected static function defineRelations()
    {
        self::addRelationOneToOne('unit_id', 'Unit', 'unit_id');
        self::addRelationOneToOne('property_id', 'Property', 'property_id');
    }

}