<?php

/**
 * Class User
 *
 * MAGIC METHODS
 * @method \Document[] getDocument()
 * @method \PaymentHistory[] getPayment()
 * @method \Lease[] getLease()
 */
class User extends BaseModel
{
    public $user_id;
    public $email;
    public $password;
    public $first_name;
    public $last_name;
    public $admin;
    public $payment_details;
    public $created;
    public $updated;
    public $deleted;

    protected static $_tableName = 'users';
    protected static $_primaryKey = 'user_id';
    protected static $_relations = [];

    private $cache = [];

    protected static $_tableFields = [
        'email',
        'password',
        'first_name',
        'last_name',
        'admin',
        'payment_details',
        'created',
        'updated',
        'deleted',
    ];

    protected static function defineRelations()
    {
        self::addRelationOneToMany('user_id', 'Document', 'user_id');
        self::addRelationOneToMany('user_id', 'PaymentHistory', 'user_id', 'Payment');
        self::addRelationManyToMany('user_id', 'Lease', 'lease_id', 'user_leases');
    }

}