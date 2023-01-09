<?php

/**
 * Class UserToken
 *
 * MAGIC METHODS
 * @method User getUser()
 */
class UserToken extends BaseModel
{
    public $token_id;
    public $user_id;
    public $user_login;
    public $selector;
    public $hash;
    public $expiration;
    public $totp;
    public $remember;
    public $last_validated;
    public $last_location;
    public $last_ip_address;
    public $last_session_id;
    public $deleted;

    protected static $_tableName = 'user_tokens';
    protected static $_primaryKey = 'token_id';
    protected static $_relations = [];

    protected static $_tableFields = [
        'user_id',
        'user_login',
        'selector',
        'hash',
        'expiration',
        'totp',
        'remember',
        'last_validated',
        'last_location',
        'last_ip_address',
        'last_session_id',
        'deleted'
    ];

    protected static function defineRelations()
    {
        self::addRelationOneToOne('user_id', 'User', 'user_id');
    }

}
