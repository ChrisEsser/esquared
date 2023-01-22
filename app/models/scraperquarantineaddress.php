<?php

/**
 * Class ScraperQuarantineAddress
 */
class ScraperQuarantineAddress extends BaseModel
{
    public $address_id;
    public $street;
    public $city;
    public $state;
    public $zip;
    public $lat;
    public $lon;

    protected static $_tableName = 'quarantine_addresses';
    protected static $_primaryKey = 'address_id';
    protected static $_relations = [];

    private $cache = [];

    protected static $_tableFields = [
        'street',
        'city',
        'state',
        'zip',
        'lat',
        'lon',
    ];

}