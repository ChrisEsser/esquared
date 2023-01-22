<?php

/**
 * Class ScraperLeadAddress
 *
 * MAGIC METHODS
 * @method \ScraperLead getScraperLead()
 */
class ScraperLeadAddress extends BaseModel
{
    public $address_id;
    public $lead_id;
    public $street;
    public $city;
    public $state;
    public $zip;
    public $lat;
    public $lon;

    protected static $_tableName = 'lead_addresses';
    protected static $_primaryKey = 'address_id';
    protected static $_relations = [];

    private $cache = [];

    protected static $_tableFields = [
        'lead_id',
        'street',
        'city',
        'state',
        'zip',
        'lat',
        'lon',
    ];

    protected static function defineRelations()
    {
        self::addRelationOneToOne('lead_id', 'ScraperLead', 'lead_id');
    }

}