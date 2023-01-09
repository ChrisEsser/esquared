<?php

/**
 * Class ScraperUrl
 *
 * MAGIC METHODS
 * @method \ScraperLead[] getScraperLead()
 */
class ScraperUrl extends BaseModel
{
    public $url_id;
    public $url;
    public $last_scraped;
    public $name;
    public $search_string;
    public $state;
    public $depth;
    public $leads_count;

    protected static $_tableName = 'scraper_urls';
    protected static $_primaryKey = 'url_id';
    protected static $_relations = [];

    private $cache = [];

    protected static $_tableFields = [
        'url',
        'last_scraped',
        'name',
        'search_string',
        'state',
        'depth',
        'leads_count',
    ];

    protected static function defineRelations()
    {
        self::addRelationOneToMany('url_id', 'ScraperLead', 'url_id');
    }

}