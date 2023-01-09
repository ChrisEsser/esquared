<?php

/**
 * Class ResetToken
 *
 * MAGIC METHODS
 * @method User getUser()
 */
class ResetToken extends BaseModel
{
    public $token_id;
    public $token;
    public $user_id;
    public $created;

    protected static $_tableName = 'reset_tokens';
    protected static $_primaryKey = 'token_id';
    protected static $_relations = [];

    protected static $_tableFields = [
        'token',
        'user_id',
        'created',
    ];

    protected static function defineRelations()
    {
        self::addRelationOneToOne('user_id', 'User', 'user_id');
    }

}
