<?php

/**
 * Class ScraperLead
 *
 * MAGIC METHODS
 * @method \ScraperUrl getScraperUrl()
 */
class ScraperLead extends BaseModel
{
    public $lead_id;
    public $url_id;
    public $url;
    public $created;
    public $last_seen;
    public $deleted;
    public $active;
    public $flagged;
    public $judgment_amount;

    protected static $_tableName = 'scraper_leads';
    protected static $_primaryKey = 'lead_id';
    protected static $_relations = [];

    private $cache = [];

    protected static $_tableFields = [
        'url_id',
        'url',
        'created',
        'last_seen',
        'deleted',
        'active',
        'flagged',
        'judgment_amount',
    ];

    protected static function defineRelations()
    {
        self::addRelationOneToOne('url_id', 'ScraperUrl', 'url_id');
    }

}