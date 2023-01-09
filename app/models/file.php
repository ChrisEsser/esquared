<?php


/**
 * Class File
 */
class File extends BaseModel
{

    public $file_id;
    public $uid;
    public $type;
    public $original_name;
    public $deleted;

    protected static $_tableName = 'files';
    protected static $_primaryKey = 'file_id';
    protected static $_relations = [];

    public $cache = [];

    protected static $_tableFields = [
        'uid',
        'type',
        'original_name',
        'deleted',
    ];

}