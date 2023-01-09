<?php

/**
 * Class BaseModel
 *
 * To define relationships example:
 * protected static function defineRelations()
   {
        self::addRelationOneToOne('idBrand', 'Brand', 'idBrand', 'nameBrand');
        self::addRelationOneToMany('idBrand', 'Car', 'idBrand');
        self::addRelationManyToMany("idCar","Tag","idTag","car_have_tag");
        self::addRelationManyToMany('idTag','Car','idCar','car_have_tag');
   }
 *
 */
class BaseModel extends PicORM\Model
{
    protected $_model;

    protected static $_primaryKey;
    protected static $_relations = [];

    public function __construct()
    {
        \PicORM\PicORM::configure([
            'datasource' => new PDO('mysql:dbname=' . $_ENV['DB_NAME'] . ';host=' . $_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']),
        ]);

        $this->_model = get_class($this);
    }

    /**
     * Override the PicORM save method. We want to set deleted to 0 if this is a new save.
     *
     * @return bool
     */
    public function save()
    {
        $class = get_called_class();

        if (property_exists($class, 'deleted') && empty($this->deleted)) {
            $this->deleted = 0;
        }
        if (property_exists($class, 'updated')) {
            $this->updated = gmdate('Y-m-d H:i:s', time());
        }
        if (property_exists($class, 'created') && empty($this->created)) {
            $this->created = gmdate('Y-m-d H:i:s', time());
        }

        return parent::save();
    }

    /**
     * We want to override PicORMs delete here. We do not want to hard delete unless there is no deleted column on the table
     */
    public function delete()
    {
        $class = get_called_class();
        if (property_exists($class, 'deleted')) {

            $this->deleted = 1;
            return $this->save();

        } else {

            return parent::delete();

        }
    }

    /**
     * This is a helper meatho for loading model values into an array so they can be used for form values and passing
     * invalid post data back to the form
     *
     * @param $model
     * @return array
     */
     public function getFields()
    {
        $fields = [];
        foreach ($this as $key => $value) {
            $fields[$key] = $value;
        }
        return $fields;
    }

    /**
     * When we call our magic method, we want to make sure we are only including non deleted records
     *
     * @param string $method
     * @param array  $args
     * @return mixed
     * @throws \PicORM\Exception
     */
    public function __call($method, $args)
    {
        if (isset($args[0])) {
            $args[0]['deleted'] = 0;
        }

        return parent::__call($method, $args);
    }

    /**
     * We want to override findOne to not include deleted unless the table does not have a deleted column
     *
     * @param array $where
     * @param array $order
     * @return \PicORM\Model
     */
     public static function findOne($where = [], $order = [])
     {
        $class = get_called_class();

        if (property_exists($class, 'deleted')) {

            $where['deleted'] = '0';
            return parent::findOne($where, $order);

         } else {

             return parent::findOne($where, $order);

         }
     }

    /**
     * We want to override find to not include deleted unless the table does not have a deleted column
     *
     * @param array $where
     * @param array $order
     * @param null  $limitStart
     * @param null  $limitEnd
     * @return \PicORM\Collection
     */
     public static function find($where = [], $order = [], $limitStart = null, $limitEnd = null)
     {
        $class = get_called_class();
        if (property_exists($class, 'deleted')) {

            $where['deleted'] = '0';
            return parent::find($where, $order, $limitStart, $limitEnd);

         } else {

             return parent::find($where, $order, $limitStart, $limitEnd);

         }
     }

}