<?php

namespace Thelia\Model\Map;

use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\InstancePoolTrait;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\RelationMap;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Map\TableMapTrait;
use Thelia\Model\Customer;
use Thelia\Model\CustomerQuery;


/**
 * This class defines the structure of the 'customer' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 */
class CustomerTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;
    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'Thelia.Model.Map.CustomerTableMap';

    /**
     * The default database name for this class
     */
    const DATABASE_NAME = 'thelia';

    /**
     * The table name for this class
     */
    const TABLE_NAME = 'customer';

    /**
     * The related Propel class for this table
     */
    const OM_CLASS = '\\Thelia\\Model\\Customer';

    /**
     * A class that can be returned by this tableMap
     */
    const CLASS_DEFAULT = 'Thelia.Model.Customer';

    /**
     * The total number of columns
     */
    const NUM_COLUMNS = 19;

    /**
     * The number of lazy-loaded columns
     */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    const NUM_HYDRATE_COLUMNS = 19;

    /**
     * the column name for the ID field
     */
    const ID = 'customer.ID';

    /**
     * the column name for the REF field
     */
    const REF = 'customer.REF';

    /**
     * the column name for the TITLE_ID field
     */
    const TITLE_ID = 'customer.TITLE_ID';

    /**
     * the column name for the FIRSTNAME field
     */
    const FIRSTNAME = 'customer.FIRSTNAME';

    /**
     * the column name for the LASTNAME field
     */
    const LASTNAME = 'customer.LASTNAME';

    /**
     * the column name for the EMAIL field
     */
    const EMAIL = 'customer.EMAIL';

    /**
     * the column name for the PASSWORD field
     */
    const PASSWORD = 'customer.PASSWORD';

    /**
     * the column name for the ALGO field
     */
    const ALGO = 'customer.ALGO';

    /**
     * the column name for the RESELLER field
     */
    const RESELLER = 'customer.RESELLER';

    /**
     * the column name for the LANG field
     */
    const LANG = 'customer.LANG';

    /**
     * the column name for the SPONSOR field
     */
    const SPONSOR = 'customer.SPONSOR';

    /**
     * the column name for the DISCOUNT field
     */
    const DISCOUNT = 'customer.DISCOUNT';

    /**
     * the column name for the REMEMBER_ME_TOKEN field
     */
    const REMEMBER_ME_TOKEN = 'customer.REMEMBER_ME_TOKEN';

    /**
     * the column name for the REMEMBER_ME_SERIAL field
     */
    const REMEMBER_ME_SERIAL = 'customer.REMEMBER_ME_SERIAL';

    /**
     * the column name for the CREATED_AT field
     */
    const CREATED_AT = 'customer.CREATED_AT';

    /**
     * the column name for the UPDATED_AT field
     */
    const UPDATED_AT = 'customer.UPDATED_AT';

    /**
     * the column name for the VERSION field
     */
    const VERSION = 'customer.VERSION';

    /**
     * the column name for the VERSION_CREATED_AT field
     */
    const VERSION_CREATED_AT = 'customer.VERSION_CREATED_AT';

    /**
     * the column name for the VERSION_CREATED_BY field
     */
    const VERSION_CREATED_BY = 'customer.VERSION_CREATED_BY';

    /**
     * The default string format for model objects of the related table
     */
    const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        self::TYPE_PHPNAME       => array('Id', 'Ref', 'TitleId', 'Firstname', 'Lastname', 'Email', 'Password', 'Algo', 'Reseller', 'Lang', 'Sponsor', 'Discount', 'RememberMeToken', 'RememberMeSerial', 'CreatedAt', 'UpdatedAt', 'Version', 'VersionCreatedAt', 'VersionCreatedBy', ),
        self::TYPE_STUDLYPHPNAME => array('id', 'ref', 'titleId', 'firstname', 'lastname', 'email', 'password', 'algo', 'reseller', 'lang', 'sponsor', 'discount', 'rememberMeToken', 'rememberMeSerial', 'createdAt', 'updatedAt', 'version', 'versionCreatedAt', 'versionCreatedBy', ),
        self::TYPE_COLNAME       => array(CustomerTableMap::ID, CustomerTableMap::REF, CustomerTableMap::TITLE_ID, CustomerTableMap::FIRSTNAME, CustomerTableMap::LASTNAME, CustomerTableMap::EMAIL, CustomerTableMap::PASSWORD, CustomerTableMap::ALGO, CustomerTableMap::RESELLER, CustomerTableMap::LANG, CustomerTableMap::SPONSOR, CustomerTableMap::DISCOUNT, CustomerTableMap::REMEMBER_ME_TOKEN, CustomerTableMap::REMEMBER_ME_SERIAL, CustomerTableMap::CREATED_AT, CustomerTableMap::UPDATED_AT, CustomerTableMap::VERSION, CustomerTableMap::VERSION_CREATED_AT, CustomerTableMap::VERSION_CREATED_BY, ),
        self::TYPE_RAW_COLNAME   => array('ID', 'REF', 'TITLE_ID', 'FIRSTNAME', 'LASTNAME', 'EMAIL', 'PASSWORD', 'ALGO', 'RESELLER', 'LANG', 'SPONSOR', 'DISCOUNT', 'REMEMBER_ME_TOKEN', 'REMEMBER_ME_SERIAL', 'CREATED_AT', 'UPDATED_AT', 'VERSION', 'VERSION_CREATED_AT', 'VERSION_CREATED_BY', ),
        self::TYPE_FIELDNAME     => array('id', 'ref', 'title_id', 'firstname', 'lastname', 'email', 'password', 'algo', 'reseller', 'lang', 'sponsor', 'discount', 'remember_me_token', 'remember_me_serial', 'created_at', 'updated_at', 'version', 'version_created_at', 'version_created_by', ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        self::TYPE_PHPNAME       => array('Id' => 0, 'Ref' => 1, 'TitleId' => 2, 'Firstname' => 3, 'Lastname' => 4, 'Email' => 5, 'Password' => 6, 'Algo' => 7, 'Reseller' => 8, 'Lang' => 9, 'Sponsor' => 10, 'Discount' => 11, 'RememberMeToken' => 12, 'RememberMeSerial' => 13, 'CreatedAt' => 14, 'UpdatedAt' => 15, 'Version' => 16, 'VersionCreatedAt' => 17, 'VersionCreatedBy' => 18, ),
        self::TYPE_STUDLYPHPNAME => array('id' => 0, 'ref' => 1, 'titleId' => 2, 'firstname' => 3, 'lastname' => 4, 'email' => 5, 'password' => 6, 'algo' => 7, 'reseller' => 8, 'lang' => 9, 'sponsor' => 10, 'discount' => 11, 'rememberMeToken' => 12, 'rememberMeSerial' => 13, 'createdAt' => 14, 'updatedAt' => 15, 'version' => 16, 'versionCreatedAt' => 17, 'versionCreatedBy' => 18, ),
        self::TYPE_COLNAME       => array(CustomerTableMap::ID => 0, CustomerTableMap::REF => 1, CustomerTableMap::TITLE_ID => 2, CustomerTableMap::FIRSTNAME => 3, CustomerTableMap::LASTNAME => 4, CustomerTableMap::EMAIL => 5, CustomerTableMap::PASSWORD => 6, CustomerTableMap::ALGO => 7, CustomerTableMap::RESELLER => 8, CustomerTableMap::LANG => 9, CustomerTableMap::SPONSOR => 10, CustomerTableMap::DISCOUNT => 11, CustomerTableMap::REMEMBER_ME_TOKEN => 12, CustomerTableMap::REMEMBER_ME_SERIAL => 13, CustomerTableMap::CREATED_AT => 14, CustomerTableMap::UPDATED_AT => 15, CustomerTableMap::VERSION => 16, CustomerTableMap::VERSION_CREATED_AT => 17, CustomerTableMap::VERSION_CREATED_BY => 18, ),
        self::TYPE_RAW_COLNAME   => array('ID' => 0, 'REF' => 1, 'TITLE_ID' => 2, 'FIRSTNAME' => 3, 'LASTNAME' => 4, 'EMAIL' => 5, 'PASSWORD' => 6, 'ALGO' => 7, 'RESELLER' => 8, 'LANG' => 9, 'SPONSOR' => 10, 'DISCOUNT' => 11, 'REMEMBER_ME_TOKEN' => 12, 'REMEMBER_ME_SERIAL' => 13, 'CREATED_AT' => 14, 'UPDATED_AT' => 15, 'VERSION' => 16, 'VERSION_CREATED_AT' => 17, 'VERSION_CREATED_BY' => 18, ),
        self::TYPE_FIELDNAME     => array('id' => 0, 'ref' => 1, 'title_id' => 2, 'firstname' => 3, 'lastname' => 4, 'email' => 5, 'password' => 6, 'algo' => 7, 'reseller' => 8, 'lang' => 9, 'sponsor' => 10, 'discount' => 11, 'remember_me_token' => 12, 'remember_me_serial' => 13, 'created_at' => 14, 'updated_at' => 15, 'version' => 16, 'version_created_at' => 17, 'version_created_by' => 18, ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, )
    );

    /**
     * Initialize the table attributes and columns
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws PropelException
     */
    public function initialize()
    {
        // attributes
        $this->setName('customer');
        $this->setPhpName('Customer');
        $this->setClassName('\\Thelia\\Model\\Customer');
        $this->setPackage('Thelia.Model');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('ID', 'Id', 'INTEGER', true, null, null);
        $this->addColumn('REF', 'Ref', 'VARCHAR', false, 50, null);
        $this->addForeignKey('TITLE_ID', 'TitleId', 'INTEGER', 'customer_title', 'ID', true, null, null);
        $this->addColumn('FIRSTNAME', 'Firstname', 'VARCHAR', true, 255, null);
        $this->addColumn('LASTNAME', 'Lastname', 'VARCHAR', true, 255, null);
        $this->addColumn('EMAIL', 'Email', 'VARCHAR', false, 255, null);
        $this->addColumn('PASSWORD', 'Password', 'VARCHAR', false, 255, null);
        $this->addColumn('ALGO', 'Algo', 'VARCHAR', false, 128, null);
        $this->addColumn('RESELLER', 'Reseller', 'TINYINT', false, null, null);
        $this->addColumn('LANG', 'Lang', 'VARCHAR', false, 10, null);
        $this->addColumn('SPONSOR', 'Sponsor', 'VARCHAR', false, 50, null);
        $this->addColumn('DISCOUNT', 'Discount', 'DECIMAL', false, 16, 0);
        $this->addColumn('REMEMBER_ME_TOKEN', 'RememberMeToken', 'VARCHAR', false, 255, null);
        $this->addColumn('REMEMBER_ME_SERIAL', 'RememberMeSerial', 'VARCHAR', false, 255, null);
        $this->addColumn('CREATED_AT', 'CreatedAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('UPDATED_AT', 'UpdatedAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('VERSION', 'Version', 'INTEGER', false, null, 0);
        $this->addColumn('VERSION_CREATED_AT', 'VersionCreatedAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('VERSION_CREATED_BY', 'VersionCreatedBy', 'VARCHAR', false, 100, null);
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('CustomerTitle', '\\Thelia\\Model\\CustomerTitle', RelationMap::MANY_TO_ONE, array('title_id' => 'id', ), 'RESTRICT', 'RESTRICT');
        $this->addRelation('Address', '\\Thelia\\Model\\Address', RelationMap::ONE_TO_MANY, array('id' => 'customer_id', ), 'CASCADE', 'RESTRICT', 'Addresses');
        $this->addRelation('Order', '\\Thelia\\Model\\Order', RelationMap::ONE_TO_MANY, array('id' => 'customer_id', ), 'RESTRICT', 'RESTRICT', 'Orders');
        $this->addRelation('Cart', '\\Thelia\\Model\\Cart', RelationMap::ONE_TO_MANY, array('id' => 'customer_id', ), 'CASCADE', 'RESTRICT', 'Carts');
        $this->addRelation('CouponCustomerCount', '\\Thelia\\Model\\CouponCustomerCount', RelationMap::ONE_TO_MANY, array('id' => 'customer_id', ), 'CASCADE', 'RESTRICT', 'CouponCustomerCounts');
        $this->addRelation('CustomerVersion', '\\Thelia\\Model\\CustomerVersion', RelationMap::ONE_TO_MANY, array('id' => 'id', ), 'CASCADE', null, 'CustomerVersions');
        $this->addRelation('Coupon', '\\Thelia\\Model\\Coupon', RelationMap::MANY_TO_MANY, array(), 'CASCADE', 'RESTRICT', 'Coupons');
    } // buildRelations()

    /**
     *
     * Gets the list of behaviors registered for this table
     *
     * @return array Associative array (name => parameters) of behaviors
     */
    public function getBehaviors()
    {
        return array(
            'timestampable' => array('create_column' => 'created_at', 'update_column' => 'updated_at', ),
            'versionable' => array('version_column' => 'version', 'version_table' => '', 'log_created_at' => 'true', 'log_created_by' => 'true', 'log_comment' => 'false', 'version_created_at_column' => 'version_created_at', 'version_created_by_column' => 'version_created_by', 'version_comment_column' => 'version_comment', ),
        );
    } // getBehaviors()
    /**
     * Method to invalidate the instance pool of all tables related to customer     * by a foreign key with ON DELETE CASCADE
     */
    public static function clearRelatedInstancePool()
    {
        // Invalidate objects in ".$this->getClassNameFromBuilder($joinedTableTableMapBuilder)." instance pool,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
                AddressTableMap::clearInstancePool();
                CartTableMap::clearInstancePool();
                CouponCustomerCountTableMap::clearInstancePool();
                CustomerVersionTableMap::clearInstancePool();
            }

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     */
    public static function getPrimaryKeyHashFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        // If the PK cannot be derived from the row, return NULL.
        if ($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] === null) {
            return null;
        }

        return (string) $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
    }

    /**
     * Retrieves the primary key from the DB resultset row
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, an array of the primary key columns will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {

            return (int) $row[
                            $indexType == TableMap::TYPE_NUM
                            ? 0 + $offset
                            : self::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)
                        ];
    }

    /**
     * The class that the tableMap will make instances of.
     *
     * If $withPrefix is true, the returned path
     * uses a dot-path notation which is translated into a path
     * relative to a location on the PHP include_path.
     * (e.g. path.to.MyClass -> 'path/to/MyClass.php')
     *
     * @param boolean $withPrefix Whether or not to return the path with the class name
     * @return string path.to.ClassName
     */
    public static function getOMClass($withPrefix = true)
    {
        return $withPrefix ? CustomerTableMap::CLASS_DEFAULT : CustomerTableMap::OM_CLASS;
    }

    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param array  $row       row returned by DataFetcher->fetch().
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                 One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     * @return array (Customer object, last column rank)
     */
    public static function populateObject($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        $key = CustomerTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = CustomerTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + CustomerTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = CustomerTableMap::OM_CLASS;
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            CustomerTableMap::addInstanceToPool($obj, $key);
        }

        return array($obj, $col);
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @param DataFetcherInterface $dataFetcher
     * @return array
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
    public static function populateObjects(DataFetcherInterface $dataFetcher)
    {
        $results = array();

        // set the class once to avoid overhead in the loop
        $cls = static::getOMClass(false);
        // populate the object(s)
        while ($row = $dataFetcher->fetch()) {
            $key = CustomerTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = CustomerTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                CustomerTableMap::addInstanceToPool($obj, $key);
            } // if key exists
        }

        return $results;
    }
    /**
     * Add all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be added to the select list and only loaded
     * on demand.
     *
     * @param Criteria $criteria object containing the columns to add.
     * @param string   $alias    optional table alias
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
    public static function addSelectColumns(Criteria $criteria, $alias = null)
    {
        if (null === $alias) {
            $criteria->addSelectColumn(CustomerTableMap::ID);
            $criteria->addSelectColumn(CustomerTableMap::REF);
            $criteria->addSelectColumn(CustomerTableMap::TITLE_ID);
            $criteria->addSelectColumn(CustomerTableMap::FIRSTNAME);
            $criteria->addSelectColumn(CustomerTableMap::LASTNAME);
            $criteria->addSelectColumn(CustomerTableMap::EMAIL);
            $criteria->addSelectColumn(CustomerTableMap::PASSWORD);
            $criteria->addSelectColumn(CustomerTableMap::ALGO);
            $criteria->addSelectColumn(CustomerTableMap::RESELLER);
            $criteria->addSelectColumn(CustomerTableMap::LANG);
            $criteria->addSelectColumn(CustomerTableMap::SPONSOR);
            $criteria->addSelectColumn(CustomerTableMap::DISCOUNT);
            $criteria->addSelectColumn(CustomerTableMap::REMEMBER_ME_TOKEN);
            $criteria->addSelectColumn(CustomerTableMap::REMEMBER_ME_SERIAL);
            $criteria->addSelectColumn(CustomerTableMap::CREATED_AT);
            $criteria->addSelectColumn(CustomerTableMap::UPDATED_AT);
            $criteria->addSelectColumn(CustomerTableMap::VERSION);
            $criteria->addSelectColumn(CustomerTableMap::VERSION_CREATED_AT);
            $criteria->addSelectColumn(CustomerTableMap::VERSION_CREATED_BY);
        } else {
            $criteria->addSelectColumn($alias . '.ID');
            $criteria->addSelectColumn($alias . '.REF');
            $criteria->addSelectColumn($alias . '.TITLE_ID');
            $criteria->addSelectColumn($alias . '.FIRSTNAME');
            $criteria->addSelectColumn($alias . '.LASTNAME');
            $criteria->addSelectColumn($alias . '.EMAIL');
            $criteria->addSelectColumn($alias . '.PASSWORD');
            $criteria->addSelectColumn($alias . '.ALGO');
            $criteria->addSelectColumn($alias . '.RESELLER');
            $criteria->addSelectColumn($alias . '.LANG');
            $criteria->addSelectColumn($alias . '.SPONSOR');
            $criteria->addSelectColumn($alias . '.DISCOUNT');
            $criteria->addSelectColumn($alias . '.REMEMBER_ME_TOKEN');
            $criteria->addSelectColumn($alias . '.REMEMBER_ME_SERIAL');
            $criteria->addSelectColumn($alias . '.CREATED_AT');
            $criteria->addSelectColumn($alias . '.UPDATED_AT');
            $criteria->addSelectColumn($alias . '.VERSION');
            $criteria->addSelectColumn($alias . '.VERSION_CREATED_AT');
            $criteria->addSelectColumn($alias . '.VERSION_CREATED_BY');
        }
    }

    /**
     * Returns the TableMap related to this object.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
    public static function getTableMap()
    {
        return Propel::getServiceContainer()->getDatabaseMap(CustomerTableMap::DATABASE_NAME)->getTable(CustomerTableMap::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this tableMap class.
     */
    public static function buildTableMap()
    {
      $dbMap = Propel::getServiceContainer()->getDatabaseMap(CustomerTableMap::DATABASE_NAME);
      if (!$dbMap->hasTable(CustomerTableMap::TABLE_NAME)) {
        $dbMap->addTableObject(new CustomerTableMap());
      }
    }

    /**
     * Performs a DELETE on the database, given a Customer or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or Customer object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, ConnectionInterface $con = null)
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(CustomerTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Thelia\Model\Customer) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(CustomerTableMap::DATABASE_NAME);
            $criteria->add(CustomerTableMap::ID, (array) $values, Criteria::IN);
        }

        $query = CustomerQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) { CustomerTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) { CustomerTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the customer table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(ConnectionInterface $con = null)
    {
        return CustomerQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a Customer or Criteria object.
     *
     * @param mixed               $criteria Criteria or Customer object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(CustomerTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from Customer object
        }

        if ($criteria->containsKey(CustomerTableMap::ID) && $criteria->keyContainsValue(CustomerTableMap::ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.CustomerTableMap::ID.')');
        }


        // Set the correct dbName
        $query = CustomerQuery::create()->mergeWith($criteria);

        try {
            // use transaction because $criteria could contain info
            // for more than one table (I guess, conceivably)
            $con->beginTransaction();
            $pk = $query->doInsert($con);
            $con->commit();
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }

        return $pk;
    }

} // CustomerTableMap
// This is the static code needed to register the TableMap for this table with the main Propel class.
//
CustomerTableMap::buildTableMap();
