<?php

/**
 * Class PaymentHistory
 *
 * MAGIC METHODS
 * @method User getUser()
 * @method Unit getUnit()
 * @method Lease getLease()
 */
class PaymentHistory extends BaseModel
{
    public $payment_id;
    public $user_id;
    public $unit_id;
    public $lease_id;
    public $amount;
    public $payment_date;
    public $method;
    public $type;
    public $description;
    public $confirmation_number;
    public $transaction_id;
    public $fee;

    protected static $_tableName = 'payment_history';
    protected static $_primaryKey = 'payment_id';
    protected static $_relations = [];

    private $cache = [];

    protected static $_tableFields = [
        'user_id',
        'unit_id',
        'lease_id',
        'amount',
        'payment_date',
        'method',
        'type',
        'description',
        'confirmation_number',
        'transaction_id',
        'fee',
    ];

    protected static function defineRelations()
    {
        self::addRelationOneToOne('user_id', 'User', 'user_id');
        self::addRelationOneToOne('unit_id', 'Unit', 'unit_id');
        self::addRelationOneToOne('lease_id', 'Lease', 'lease_id');
    }

}