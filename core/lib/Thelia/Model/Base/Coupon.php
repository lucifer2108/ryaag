<?php

namespace Thelia\Model\Base;

use \DateTime;
use \Exception;
use \PDO;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;
use Propel\Runtime\Util\PropelDateTime;
use Thelia\Model\Coupon as ChildCoupon;
use Thelia\Model\CouponI18n as ChildCouponI18n;
use Thelia\Model\CouponI18nQuery as ChildCouponI18nQuery;
use Thelia\Model\CouponQuery as ChildCouponQuery;
use Thelia\Model\CouponVersion as ChildCouponVersion;
use Thelia\Model\CouponVersionQuery as ChildCouponVersionQuery;
use Thelia\Model\Map\CouponTableMap;
use Thelia\Model\Map\CouponVersionTableMap;

abstract class Coupon implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\Thelia\\Model\\Map\\CouponTableMap';


    /**
     * attribute to determine if this object has previously been saved.
     * @var boolean
     */
    protected $new = true;

    /**
     * attribute to determine whether this object has been deleted.
     * @var boolean
     */
    protected $deleted = false;

    /**
     * The columns that have been modified in current object.
     * Tracking modified columns allows us to only update modified columns.
     * @var array
     */
    protected $modifiedColumns = array();

    /**
     * The (virtual) columns that are added at runtime
     * The formatters can add supplementary columns based on a resultset
     * @var array
     */
    protected $virtualColumns = array();

    /**
     * The value for the id field.
     * @var        int
     */
    protected $id;

    /**
     * The value for the code field.
     * @var        string
     */
    protected $code;

    /**
     * The value for the type field.
     * @var        string
     */
    protected $type;

    /**
     * The value for the amount field.
     * @var        double
     */
    protected $amount;

    /**
     * The value for the is_enabled field.
     * @var        boolean
     */
    protected $is_enabled;

    /**
     * The value for the expiration_date field.
     * @var        string
     */
    protected $expiration_date;

    /**
     * The value for the max_usage field.
     * @var        int
     */
    protected $max_usage;

    /**
     * The value for the is_cumulative field.
     * @var        boolean
     */
    protected $is_cumulative;

    /**
     * The value for the is_removing_postage field.
     * @var        boolean
     */
    protected $is_removing_postage;

    /**
     * The value for the is_available_on_special_offers field.
     * @var        boolean
     */
    protected $is_available_on_special_offers;

    /**
     * The value for the is_used field.
     * @var        boolean
     */
    protected $is_used;

    /**
     * The value for the serialized_conditions field.
     * @var        string
     */
    protected $serialized_conditions;

    /**
     * The value for the created_at field.
     * @var        string
     */
    protected $created_at;

    /**
     * The value for the updated_at field.
     * @var        string
     */
    protected $updated_at;

    /**
     * The value for the version field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $version;

    /**
     * @var        ObjectCollection|ChildCouponI18n[] Collection to store aggregation of ChildCouponI18n objects.
     */
    protected $collCouponI18ns;
    protected $collCouponI18nsPartial;

    /**
     * @var        ObjectCollection|ChildCouponVersion[] Collection to store aggregation of ChildCouponVersion objects.
     */
    protected $collCouponVersions;
    protected $collCouponVersionsPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var boolean
     */
    protected $alreadyInSave = false;

    // i18n behavior

    /**
     * Current locale
     * @var        string
     */
    protected $currentLocale = 'en_US';

    /**
     * Current translation objects
     * @var        array[ChildCouponI18n]
     */
    protected $currentTranslations;

    // versionable behavior


    /**
     * @var bool
     */
    protected $enforceVersion = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection
     */
    protected $couponI18nsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection
     */
    protected $couponVersionsScheduledForDeletion = null;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see __construct()
     */
    public function applyDefaultValues()
    {
        $this->version = 0;
    }

    /**
     * Initializes internal state of Thelia\Model\Base\Coupon object.
     * @see applyDefaults()
     */
    public function __construct()
    {
        $this->applyDefaultValues();
    }

    /**
     * Returns whether the object has been modified.
     *
     * @return boolean True if the object has been modified.
     */
    public function isModified()
    {
        return !empty($this->modifiedColumns);
    }

    /**
     * Has specified column been modified?
     *
     * @param  string  $col column fully qualified name (TableMap::TYPE_COLNAME), e.g. Book::AUTHOR_ID
     * @return boolean True if $col has been modified.
     */
    public function isColumnModified($col)
    {
        return in_array($col, $this->modifiedColumns);
    }

    /**
     * Get the columns that have been modified in this object.
     * @return array A unique list of the modified column names for this object.
     */
    public function getModifiedColumns()
    {
        return array_unique($this->modifiedColumns);
    }

    /**
     * Returns whether the object has ever been saved.  This will
     * be false, if the object was retrieved from storage or was created
     * and then saved.
     *
     * @return true, if the object has never been persisted.
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * Setter for the isNew attribute.  This method will be called
     * by Propel-generated children and objects.
     *
     * @param boolean $b the state of the object.
     */
    public function setNew($b)
    {
        $this->new = (Boolean) $b;
    }

    /**
     * Whether this object has been deleted.
     * @return boolean The deleted state of this object.
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Specify whether this object has been deleted.
     * @param  boolean $b The deleted state of this object.
     * @return void
     */
    public function setDeleted($b)
    {
        $this->deleted = (Boolean) $b;
    }

    /**
     * Sets the modified state for the object to be false.
     * @param  string $col If supplied, only the specified column is reset.
     * @return void
     */
    public function resetModified($col = null)
    {
        if (null !== $col) {
            while (false !== ($offset = array_search($col, $this->modifiedColumns))) {
                array_splice($this->modifiedColumns, $offset, 1);
            }
        } else {
            $this->modifiedColumns = array();
        }
    }

    /**
     * Compares this with another <code>Coupon</code> instance.  If
     * <code>obj</code> is an instance of <code>Coupon</code>, delegates to
     * <code>equals(Coupon)</code>.  Otherwise, returns <code>false</code>.
     *
     * @param      obj The object to compare to.
     * @return Whether equal to the object specified.
     */
    public function equals($obj)
    {
        $thisclazz = get_class($this);
        if (!is_object($obj) || !($obj instanceof $thisclazz)) {
            return false;
        }

        if ($this === $obj) {
            return true;
        }

        if (null === $this->getPrimaryKey()
            || null === $obj->getPrimaryKey())  {
            return false;
        }

        return $this->getPrimaryKey() === $obj->getPrimaryKey();
    }

    /**
     * If the primary key is not null, return the hashcode of the
     * primary key. Otherwise, return the hash code of the object.
     *
     * @return int Hashcode
     */
    public function hashCode()
    {
        if (null !== $this->getPrimaryKey()) {
            return crc32(serialize($this->getPrimaryKey()));
        }

        return crc32(serialize(clone $this));
    }

    /**
     * Get the associative array of the virtual columns in this object
     *
     * @param string $name The virtual column name
     *
     * @return array
     */
    public function getVirtualColumns()
    {
        return $this->virtualColumns;
    }

    /**
     * Checks the existence of a virtual column in this object
     *
     * @return boolean
     */
    public function hasVirtualColumn($name)
    {
        return array_key_exists($name, $this->virtualColumns);
    }

    /**
     * Get the value of a virtual column in this object
     *
     * @return mixed
     */
    public function getVirtualColumn($name)
    {
        if (!$this->hasVirtualColumn($name)) {
            throw new PropelException(sprintf('Cannot get value of inexistent virtual column %s.', $name));
        }

        return $this->virtualColumns[$name];
    }

    /**
     * Set the value of a virtual column in this object
     *
     * @param string $name  The virtual column name
     * @param mixed  $value The value to give to the virtual column
     *
     * @return Coupon The current object, for fluid interface
     */
    public function setVirtualColumn($name, $value)
    {
        $this->virtualColumns[$name] = $value;

        return $this;
    }

    /**
     * Logs a message using Propel::log().
     *
     * @param  string  $msg
     * @param  int     $priority One of the Propel::LOG_* logging levels
     * @return boolean
     */
    protected function log($msg, $priority = Propel::LOG_INFO)
    {
        return Propel::log(get_class($this) . ': ' . $msg, $priority);
    }

    /**
     * Populate the current object from a string, using a given parser format
     * <code>
     * $book = new Book();
     * $book->importFrom('JSON', '{"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param mixed $parser A AbstractParser instance,
     *                       or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param string $data The source data to import from
     *
     * @return Coupon The current object, for fluid interface
     */
    public function importFrom($parser, $data)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        return $this->fromArray($parser->toArray($data), TableMap::TYPE_PHPNAME);
    }

    /**
     * Export the current object properties to a string, using a given parser format
     * <code>
     * $book = BookQuery::create()->findPk(9012);
     * echo $book->exportTo('JSON');
     *  => {"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param  mixed   $parser                 A AbstractParser instance, or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param  boolean $includeLazyLoadColumns (optional) Whether to include lazy load(ed) columns. Defaults to TRUE.
     * @return string  The exported data
     */
    public function exportTo($parser, $includeLazyLoadColumns = true)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        return $parser->fromArray($this->toArray(TableMap::TYPE_PHPNAME, $includeLazyLoadColumns, array(), true));
    }

    /**
     * Clean up internal collections prior to serializing
     * Avoids recursive loops that turn into segmentation faults when serializing
     */
    public function __sleep()
    {
        $this->clearAllReferences();

        return array_keys(get_object_vars($this));
    }

    /**
     * Get the [id] column value.
     *
     * @return   int
     */
    public function getId()
    {

        return $this->id;
    }

    /**
     * Get the [code] column value.
     *
     * @return   string
     */
    public function getCode()
    {

        return $this->code;
    }

    /**
     * Get the [type] column value.
     *
     * @return   string
     */
    public function getType()
    {

        return $this->type;
    }

    /**
     * Get the [amount] column value.
     *
     * @return   double
     */
    public function getAmount()
    {

        return $this->amount;
    }

    /**
     * Get the [is_enabled] column value.
     *
     * @return   boolean
     */
    public function getIsEnabled()
    {

        return $this->is_enabled;
    }

    /**
     * Get the [optionally formatted] temporal [expiration_date] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw \DateTime object will be returned.
     *
     * @return mixed Formatted date/time value as string or \DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getExpirationDate($format = NULL)
    {
        if ($format === null) {
            return $this->expiration_date;
        } else {
            return $this->expiration_date !== null ? $this->expiration_date->format($format) : null;
        }
    }

    /**
     * Get the [max_usage] column value.
     *
     * @return   int
     */
    public function getMaxUsage()
    {

        return $this->max_usage;
    }

    /**
     * Get the [is_cumulative] column value.
     *
     * @return   boolean
     */
    public function getIsCumulative()
    {

        return $this->is_cumulative;
    }

    /**
     * Get the [is_removing_postage] column value.
     *
     * @return   boolean
     */
    public function getIsRemovingPostage()
    {

        return $this->is_removing_postage;
    }

    /**
     * Get the [is_available_on_special_offers] column value.
     *
     * @return   boolean
     */
    public function getIsAvailableOnSpecialOffers()
    {

        return $this->is_available_on_special_offers;
    }

    /**
     * Get the [is_used] column value.
     *
     * @return   boolean
     */
    public function getIsUsed()
    {

        return $this->is_used;
    }

    /**
     * Get the [serialized_conditions] column value.
     *
     * @return   string
     */
    public function getSerializedConditions()
    {

        return $this->serialized_conditions;
    }

    /**
     * Get the [optionally formatted] temporal [created_at] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw \DateTime object will be returned.
     *
     * @return mixed Formatted date/time value as string or \DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getCreatedAt($format = NULL)
    {
        if ($format === null) {
            return $this->created_at;
        } else {
            return $this->created_at !== null ? $this->created_at->format($format) : null;
        }
    }

    /**
     * Get the [optionally formatted] temporal [updated_at] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw \DateTime object will be returned.
     *
     * @return mixed Formatted date/time value as string or \DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getUpdatedAt($format = NULL)
    {
        if ($format === null) {
            return $this->updated_at;
        } else {
            return $this->updated_at !== null ? $this->updated_at->format($format) : null;
        }
    }

    /**
     * Get the [version] column value.
     *
     * @return   int
     */
    public function getVersion()
    {

        return $this->version;
    }

    /**
     * Set the value of [id] column.
     *
     * @param      int $v new value
     * @return   \Thelia\Model\Coupon The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[] = CouponTableMap::ID;
        }


        return $this;
    } // setId()

    /**
     * Set the value of [code] column.
     *
     * @param      string $v new value
     * @return   \Thelia\Model\Coupon The current object (for fluent API support)
     */
    public function setCode($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->code !== $v) {
            $this->code = $v;
            $this->modifiedColumns[] = CouponTableMap::CODE;
        }


        return $this;
    } // setCode()

    /**
     * Set the value of [type] column.
     *
     * @param      string $v new value
     * @return   \Thelia\Model\Coupon The current object (for fluent API support)
     */
    public function setType($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->type !== $v) {
            $this->type = $v;
            $this->modifiedColumns[] = CouponTableMap::TYPE;
        }


        return $this;
    } // setType()

    /**
     * Set the value of [amount] column.
     *
     * @param      double $v new value
     * @return   \Thelia\Model\Coupon The current object (for fluent API support)
     */
    public function setAmount($v)
    {
        if ($v !== null) {
            $v = (double) $v;
        }

        if ($this->amount !== $v) {
            $this->amount = $v;
            $this->modifiedColumns[] = CouponTableMap::AMOUNT;
        }


        return $this;
    } // setAmount()

    /**
     * Sets the value of the [is_enabled] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     *
     * @param      boolean|integer|string $v The new value
     * @return   \Thelia\Model\Coupon The current object (for fluent API support)
     */
    public function setIsEnabled($v)
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                $v = (boolean) $v;
            }
        }

        if ($this->is_enabled !== $v) {
            $this->is_enabled = $v;
            $this->modifiedColumns[] = CouponTableMap::IS_ENABLED;
        }


        return $this;
    } // setIsEnabled()

    /**
     * Sets the value of [expiration_date] column to a normalized version of the date/time value specified.
     *
     * @param      mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return   \Thelia\Model\Coupon The current object (for fluent API support)
     */
    public function setExpirationDate($v)
    {
        $dt = PropelDateTime::newInstance($v, null, '\DateTime');
        if ($this->expiration_date !== null || $dt !== null) {
            if ($dt !== $this->expiration_date) {
                $this->expiration_date = $dt;
                $this->modifiedColumns[] = CouponTableMap::EXPIRATION_DATE;
            }
        } // if either are not null


        return $this;
    } // setExpirationDate()

    /**
     * Set the value of [max_usage] column.
     *
     * @param      int $v new value
     * @return   \Thelia\Model\Coupon The current object (for fluent API support)
     */
    public function setMaxUsage($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->max_usage !== $v) {
            $this->max_usage = $v;
            $this->modifiedColumns[] = CouponTableMap::MAX_USAGE;
        }


        return $this;
    } // setMaxUsage()

    /**
     * Sets the value of the [is_cumulative] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     *
     * @param      boolean|integer|string $v The new value
     * @return   \Thelia\Model\Coupon The current object (for fluent API support)
     */
    public function setIsCumulative($v)
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                $v = (boolean) $v;
            }
        }

        if ($this->is_cumulative !== $v) {
            $this->is_cumulative = $v;
            $this->modifiedColumns[] = CouponTableMap::IS_CUMULATIVE;
        }


        return $this;
    } // setIsCumulative()

    /**
     * Sets the value of the [is_removing_postage] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     *
     * @param      boolean|integer|string $v The new value
     * @return   \Thelia\Model\Coupon The current object (for fluent API support)
     */
    public function setIsRemovingPostage($v)
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                $v = (boolean) $v;
            }
        }

        if ($this->is_removing_postage !== $v) {
            $this->is_removing_postage = $v;
            $this->modifiedColumns[] = CouponTableMap::IS_REMOVING_POSTAGE;
        }


        return $this;
    } // setIsRemovingPostage()

    /**
     * Sets the value of the [is_available_on_special_offers] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     *
     * @param      boolean|integer|string $v The new value
     * @return   \Thelia\Model\Coupon The current object (for fluent API support)
     */
    public function setIsAvailableOnSpecialOffers($v)
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                $v = (boolean) $v;
            }
        }

        if ($this->is_available_on_special_offers !== $v) {
            $this->is_available_on_special_offers = $v;
            $this->modifiedColumns[] = CouponTableMap::IS_AVAILABLE_ON_SPECIAL_OFFERS;
        }


        return $this;
    } // setIsAvailableOnSpecialOffers()

    /**
     * Sets the value of the [is_used] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     *
     * @param      boolean|integer|string $v The new value
     * @return   \Thelia\Model\Coupon The current object (for fluent API support)
     */
    public function setIsUsed($v)
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                $v = (boolean) $v;
            }
        }

        if ($this->is_used !== $v) {
            $this->is_used = $v;
            $this->modifiedColumns[] = CouponTableMap::IS_USED;
        }


        return $this;
    } // setIsUsed()

    /**
     * Set the value of [serialized_conditions] column.
     *
     * @param      string $v new value
     * @return   \Thelia\Model\Coupon The current object (for fluent API support)
     */
    public function setSerializedConditions($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->serialized_conditions !== $v) {
            $this->serialized_conditions = $v;
            $this->modifiedColumns[] = CouponTableMap::SERIALIZED_CONDITIONS;
        }


        return $this;
    } // setSerializedConditions()

    /**
     * Sets the value of [created_at] column to a normalized version of the date/time value specified.
     *
     * @param      mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return   \Thelia\Model\Coupon The current object (for fluent API support)
     */
    public function setCreatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, '\DateTime');
        if ($this->created_at !== null || $dt !== null) {
            if ($dt !== $this->created_at) {
                $this->created_at = $dt;
                $this->modifiedColumns[] = CouponTableMap::CREATED_AT;
            }
        } // if either are not null


        return $this;
    } // setCreatedAt()

    /**
     * Sets the value of [updated_at] column to a normalized version of the date/time value specified.
     *
     * @param      mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return   \Thelia\Model\Coupon The current object (for fluent API support)
     */
    public function setUpdatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, '\DateTime');
        if ($this->updated_at !== null || $dt !== null) {
            if ($dt !== $this->updated_at) {
                $this->updated_at = $dt;
                $this->modifiedColumns[] = CouponTableMap::UPDATED_AT;
            }
        } // if either are not null


        return $this;
    } // setUpdatedAt()

    /**
     * Set the value of [version] column.
     *
     * @param      int $v new value
     * @return   \Thelia\Model\Coupon The current object (for fluent API support)
     */
    public function setVersion($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->version !== $v) {
            $this->version = $v;
            $this->modifiedColumns[] = CouponTableMap::VERSION;
        }


        return $this;
    } // setVersion()

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return boolean Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues()
    {
            if ($this->version !== 0) {
                return false;
            }

        // otherwise, everything was equal, so return TRUE
        return true;
    } // hasOnlyDefaultValues()

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (0-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param array   $row       The row returned by DataFetcher->fetch().
     * @param int     $startcol  0-based offset column which indicates which restultset column to start with.
     * @param boolean $rehydrate Whether this object is being re-hydrated from the database.
     * @param string  $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                  One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                            TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @return int             next starting column
     * @throws PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false, $indexType = TableMap::TYPE_NUM)
    {
        try {


            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : CouponTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : CouponTableMap::translateFieldName('Code', TableMap::TYPE_PHPNAME, $indexType)];
            $this->code = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : CouponTableMap::translateFieldName('Type', TableMap::TYPE_PHPNAME, $indexType)];
            $this->type = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : CouponTableMap::translateFieldName('Amount', TableMap::TYPE_PHPNAME, $indexType)];
            $this->amount = (null !== $col) ? (double) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : CouponTableMap::translateFieldName('IsEnabled', TableMap::TYPE_PHPNAME, $indexType)];
            $this->is_enabled = (null !== $col) ? (boolean) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : CouponTableMap::translateFieldName('ExpirationDate', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->expiration_date = (null !== $col) ? PropelDateTime::newInstance($col, null, '\DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 6 + $startcol : CouponTableMap::translateFieldName('MaxUsage', TableMap::TYPE_PHPNAME, $indexType)];
            $this->max_usage = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 7 + $startcol : CouponTableMap::translateFieldName('IsCumulative', TableMap::TYPE_PHPNAME, $indexType)];
            $this->is_cumulative = (null !== $col) ? (boolean) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 8 + $startcol : CouponTableMap::translateFieldName('IsRemovingPostage', TableMap::TYPE_PHPNAME, $indexType)];
            $this->is_removing_postage = (null !== $col) ? (boolean) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 9 + $startcol : CouponTableMap::translateFieldName('IsAvailableOnSpecialOffers', TableMap::TYPE_PHPNAME, $indexType)];
            $this->is_available_on_special_offers = (null !== $col) ? (boolean) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 10 + $startcol : CouponTableMap::translateFieldName('IsUsed', TableMap::TYPE_PHPNAME, $indexType)];
            $this->is_used = (null !== $col) ? (boolean) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 11 + $startcol : CouponTableMap::translateFieldName('SerializedConditions', TableMap::TYPE_PHPNAME, $indexType)];
            $this->serialized_conditions = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 12 + $startcol : CouponTableMap::translateFieldName('CreatedAt', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->created_at = (null !== $col) ? PropelDateTime::newInstance($col, null, '\DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 13 + $startcol : CouponTableMap::translateFieldName('UpdatedAt', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->updated_at = (null !== $col) ? PropelDateTime::newInstance($col, null, '\DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 14 + $startcol : CouponTableMap::translateFieldName('Version', TableMap::TYPE_PHPNAME, $indexType)];
            $this->version = (null !== $col) ? (int) $col : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 15; // 15 = CouponTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating \Thelia\Model\Coupon object", 0, $e);
        }
    }

    /**
     * Checks and repairs the internal consistency of the object.
     *
     * This method is executed after an already-instantiated object is re-hydrated
     * from the database.  It exists to check any foreign keys to make sure that
     * the objects related to the current object are correct based on foreign key.
     *
     * You can override this method in the stub class, but you should always invoke
     * the base method from the overridden method (i.e. parent::ensureConsistency()),
     * in case your model changes.
     *
     * @throws PropelException
     */
    public function ensureConsistency()
    {
    } // ensureConsistency

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param      boolean $deep (optional) Whether to also de-associated any related objects.
     * @param      ConnectionInterface $con (optional) The ConnectionInterface connection to use.
     * @return void
     * @throws PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload($deep = false, ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(CouponTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildCouponQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->collCouponI18ns = null;

            $this->collCouponVersions = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see Coupon::setDeleted()
     * @see Coupon::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(CouponTableMap::DATABASE_NAME);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = ChildCouponQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                $con->commit();
                $this->setDeleted(true);
            } else {
                $con->commit();
            }
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Persists this object to the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All modified related objects will also be persisted in the doSave()
     * method.  This method wraps all precipitate database operations in a
     * single transaction.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see doSave()
     */
    public function save(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(CouponTableMap::DATABASE_NAME);
        }

        $con->beginTransaction();
        $isInsert = $this->isNew();
        try {
            $ret = $this->preSave($con);
            // versionable behavior
            if ($this->isVersioningNecessary()) {
                $this->setVersion($this->isNew() ? 1 : $this->getLastVersionNumber($con) + 1);
                $createVersion = true; // for postSave hook
            }
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
                // timestampable behavior
                if (!$this->isColumnModified(CouponTableMap::CREATED_AT)) {
                    $this->setCreatedAt(time());
                }
                if (!$this->isColumnModified(CouponTableMap::UPDATED_AT)) {
                    $this->setUpdatedAt(time());
                }
            } else {
                $ret = $ret && $this->preUpdate($con);
                // timestampable behavior
                if ($this->isModified() && !$this->isColumnModified(CouponTableMap::UPDATED_AT)) {
                    $this->setUpdatedAt(time());
                }
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                // versionable behavior
                if (isset($createVersion)) {
                    $this->addVersion($con);
                }
                CouponTableMap::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Performs the work of inserting or updating the row in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see save()
     */
    protected function doSave(ConnectionInterface $con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                } else {
                    $this->doUpdate($con);
                }
                $affectedRows += 1;
                $this->resetModified();
            }

            if ($this->couponI18nsScheduledForDeletion !== null) {
                if (!$this->couponI18nsScheduledForDeletion->isEmpty()) {
                    \Thelia\Model\CouponI18nQuery::create()
                        ->filterByPrimaryKeys($this->couponI18nsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->couponI18nsScheduledForDeletion = null;
                }
            }

                if ($this->collCouponI18ns !== null) {
            foreach ($this->collCouponI18ns as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->couponVersionsScheduledForDeletion !== null) {
                if (!$this->couponVersionsScheduledForDeletion->isEmpty()) {
                    \Thelia\Model\CouponVersionQuery::create()
                        ->filterByPrimaryKeys($this->couponVersionsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->couponVersionsScheduledForDeletion = null;
                }
            }

                if ($this->collCouponVersions !== null) {
            foreach ($this->collCouponVersions as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    } // doSave()

    /**
     * Insert the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @throws PropelException
     * @see doSave()
     */
    protected function doInsert(ConnectionInterface $con)
    {
        $modifiedColumns = array();
        $index = 0;

        $this->modifiedColumns[] = CouponTableMap::ID;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . CouponTableMap::ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(CouponTableMap::ID)) {
            $modifiedColumns[':p' . $index++]  = 'ID';
        }
        if ($this->isColumnModified(CouponTableMap::CODE)) {
            $modifiedColumns[':p' . $index++]  = 'CODE';
        }
        if ($this->isColumnModified(CouponTableMap::TYPE)) {
            $modifiedColumns[':p' . $index++]  = 'TYPE';
        }
        if ($this->isColumnModified(CouponTableMap::AMOUNT)) {
            $modifiedColumns[':p' . $index++]  = 'AMOUNT';
        }
        if ($this->isColumnModified(CouponTableMap::IS_ENABLED)) {
            $modifiedColumns[':p' . $index++]  = 'IS_ENABLED';
        }
        if ($this->isColumnModified(CouponTableMap::EXPIRATION_DATE)) {
            $modifiedColumns[':p' . $index++]  = 'EXPIRATION_DATE';
        }
        if ($this->isColumnModified(CouponTableMap::MAX_USAGE)) {
            $modifiedColumns[':p' . $index++]  = 'MAX_USAGE';
        }
        if ($this->isColumnModified(CouponTableMap::IS_CUMULATIVE)) {
            $modifiedColumns[':p' . $index++]  = 'IS_CUMULATIVE';
        }
        if ($this->isColumnModified(CouponTableMap::IS_REMOVING_POSTAGE)) {
            $modifiedColumns[':p' . $index++]  = 'IS_REMOVING_POSTAGE';
        }
        if ($this->isColumnModified(CouponTableMap::IS_AVAILABLE_ON_SPECIAL_OFFERS)) {
            $modifiedColumns[':p' . $index++]  = 'IS_AVAILABLE_ON_SPECIAL_OFFERS';
        }
        if ($this->isColumnModified(CouponTableMap::IS_USED)) {
            $modifiedColumns[':p' . $index++]  = 'IS_USED';
        }
        if ($this->isColumnModified(CouponTableMap::SERIALIZED_CONDITIONS)) {
            $modifiedColumns[':p' . $index++]  = 'SERIALIZED_CONDITIONS';
        }
        if ($this->isColumnModified(CouponTableMap::CREATED_AT)) {
            $modifiedColumns[':p' . $index++]  = 'CREATED_AT';
        }
        if ($this->isColumnModified(CouponTableMap::UPDATED_AT)) {
            $modifiedColumns[':p' . $index++]  = 'UPDATED_AT';
        }
        if ($this->isColumnModified(CouponTableMap::VERSION)) {
            $modifiedColumns[':p' . $index++]  = 'VERSION';
        }

        $sql = sprintf(
            'INSERT INTO coupon (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case 'ID':
                        $stmt->bindValue($identifier, $this->id, PDO::PARAM_INT);
                        break;
                    case 'CODE':
                        $stmt->bindValue($identifier, $this->code, PDO::PARAM_STR);
                        break;
                    case 'TYPE':
                        $stmt->bindValue($identifier, $this->type, PDO::PARAM_STR);
                        break;
                    case 'AMOUNT':
                        $stmt->bindValue($identifier, $this->amount, PDO::PARAM_STR);
                        break;
                    case 'IS_ENABLED':
                        $stmt->bindValue($identifier, (int) $this->is_enabled, PDO::PARAM_INT);
                        break;
                    case 'EXPIRATION_DATE':
                        $stmt->bindValue($identifier, $this->expiration_date ? $this->expiration_date->format("Y-m-d H:i:s") : null, PDO::PARAM_STR);
                        break;
                    case 'MAX_USAGE':
                        $stmt->bindValue($identifier, $this->max_usage, PDO::PARAM_INT);
                        break;
                    case 'IS_CUMULATIVE':
                        $stmt->bindValue($identifier, (int) $this->is_cumulative, PDO::PARAM_INT);
                        break;
                    case 'IS_REMOVING_POSTAGE':
                        $stmt->bindValue($identifier, (int) $this->is_removing_postage, PDO::PARAM_INT);
                        break;
                    case 'IS_AVAILABLE_ON_SPECIAL_OFFERS':
                        $stmt->bindValue($identifier, (int) $this->is_available_on_special_offers, PDO::PARAM_INT);
                        break;
                    case 'IS_USED':
                        $stmt->bindValue($identifier, (int) $this->is_used, PDO::PARAM_INT);
                        break;
                    case 'SERIALIZED_CONDITIONS':
                        $stmt->bindValue($identifier, $this->serialized_conditions, PDO::PARAM_STR);
                        break;
                    case 'CREATED_AT':
                        $stmt->bindValue($identifier, $this->created_at ? $this->created_at->format("Y-m-d H:i:s") : null, PDO::PARAM_STR);
                        break;
                    case 'UPDATED_AT':
                        $stmt->bindValue($identifier, $this->updated_at ? $this->updated_at->format("Y-m-d H:i:s") : null, PDO::PARAM_STR);
                        break;
                    case 'VERSION':
                        $stmt->bindValue($identifier, $this->version, PDO::PARAM_INT);
                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), 0, $e);
        }

        try {
            $pk = $con->lastInsertId();
        } catch (Exception $e) {
            throw new PropelException('Unable to get autoincrement id.', 0, $e);
        }
        $this->setId($pk);

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @return Integer Number of updated rows
     * @see doSave()
     */
    protected function doUpdate(ConnectionInterface $con)
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();

        return $selectCriteria->doUpdate($valuesCriteria, $con);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param      string $name name
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return mixed Value of field.
     */
    public function getByName($name, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = CouponTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @return mixed Value of field at $pos
     */
    public function getByPosition($pos)
    {
        switch ($pos) {
            case 0:
                return $this->getId();
                break;
            case 1:
                return $this->getCode();
                break;
            case 2:
                return $this->getType();
                break;
            case 3:
                return $this->getAmount();
                break;
            case 4:
                return $this->getIsEnabled();
                break;
            case 5:
                return $this->getExpirationDate();
                break;
            case 6:
                return $this->getMaxUsage();
                break;
            case 7:
                return $this->getIsCumulative();
                break;
            case 8:
                return $this->getIsRemovingPostage();
                break;
            case 9:
                return $this->getIsAvailableOnSpecialOffers();
                break;
            case 10:
                return $this->getIsUsed();
                break;
            case 11:
                return $this->getSerializedConditions();
                break;
            case 12:
                return $this->getCreatedAt();
                break;
            case 13:
                return $this->getUpdatedAt();
                break;
            case 14:
                return $this->getVersion();
                break;
            default:
                return null;
                break;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param     string  $keyType (optional) One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME,
     *                    TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                    Defaults to TableMap::TYPE_PHPNAME.
     * @param     boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to TRUE.
     * @param     array $alreadyDumpedObjects List of objects to skip to avoid recursion
     * @param     boolean $includeForeignObjects (optional) Whether to include hydrated related objects. Default to FALSE.
     *
     * @return array an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false)
    {
        if (isset($alreadyDumpedObjects['Coupon'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['Coupon'][$this->getPrimaryKey()] = true;
        $keys = CouponTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getCode(),
            $keys[2] => $this->getType(),
            $keys[3] => $this->getAmount(),
            $keys[4] => $this->getIsEnabled(),
            $keys[5] => $this->getExpirationDate(),
            $keys[6] => $this->getMaxUsage(),
            $keys[7] => $this->getIsCumulative(),
            $keys[8] => $this->getIsRemovingPostage(),
            $keys[9] => $this->getIsAvailableOnSpecialOffers(),
            $keys[10] => $this->getIsUsed(),
            $keys[11] => $this->getSerializedConditions(),
            $keys[12] => $this->getCreatedAt(),
            $keys[13] => $this->getUpdatedAt(),
            $keys[14] => $this->getVersion(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach($virtualColumns as $key => $virtualColumn)
        {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->collCouponI18ns) {
                $result['CouponI18ns'] = $this->collCouponI18ns->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collCouponVersions) {
                $result['CouponVersions'] = $this->collCouponVersions->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
        }

        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param      string $name
     * @param      mixed  $value field value
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return void
     */
    public function setByName($name, $value, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = CouponTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @param      mixed $value field value
     * @return void
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setCode($value);
                break;
            case 2:
                $this->setType($value);
                break;
            case 3:
                $this->setAmount($value);
                break;
            case 4:
                $this->setIsEnabled($value);
                break;
            case 5:
                $this->setExpirationDate($value);
                break;
            case 6:
                $this->setMaxUsage($value);
                break;
            case 7:
                $this->setIsCumulative($value);
                break;
            case 8:
                $this->setIsRemovingPostage($value);
                break;
            case 9:
                $this->setIsAvailableOnSpecialOffers($value);
                break;
            case 10:
                $this->setIsUsed($value);
                break;
            case 11:
                $this->setSerializedConditions($value);
                break;
            case 12:
                $this->setCreatedAt($value);
                break;
            case 13:
                $this->setUpdatedAt($value);
                break;
            case 14:
                $this->setVersion($value);
                break;
        } // switch()
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param      array  $arr     An array to populate the object from.
     * @param      string $keyType The type of keys the array uses.
     * @return void
     */
    public function fromArray($arr, $keyType = TableMap::TYPE_PHPNAME)
    {
        $keys = CouponTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setCode($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setType($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setAmount($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setIsEnabled($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setExpirationDate($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setMaxUsage($arr[$keys[6]]);
        if (array_key_exists($keys[7], $arr)) $this->setIsCumulative($arr[$keys[7]]);
        if (array_key_exists($keys[8], $arr)) $this->setIsRemovingPostage($arr[$keys[8]]);
        if (array_key_exists($keys[9], $arr)) $this->setIsAvailableOnSpecialOffers($arr[$keys[9]]);
        if (array_key_exists($keys[10], $arr)) $this->setIsUsed($arr[$keys[10]]);
        if (array_key_exists($keys[11], $arr)) $this->setSerializedConditions($arr[$keys[11]]);
        if (array_key_exists($keys[12], $arr)) $this->setCreatedAt($arr[$keys[12]]);
        if (array_key_exists($keys[13], $arr)) $this->setUpdatedAt($arr[$keys[13]]);
        if (array_key_exists($keys[14], $arr)) $this->setVersion($arr[$keys[14]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(CouponTableMap::DATABASE_NAME);

        if ($this->isColumnModified(CouponTableMap::ID)) $criteria->add(CouponTableMap::ID, $this->id);
        if ($this->isColumnModified(CouponTableMap::CODE)) $criteria->add(CouponTableMap::CODE, $this->code);
        if ($this->isColumnModified(CouponTableMap::TYPE)) $criteria->add(CouponTableMap::TYPE, $this->type);
        if ($this->isColumnModified(CouponTableMap::AMOUNT)) $criteria->add(CouponTableMap::AMOUNT, $this->amount);
        if ($this->isColumnModified(CouponTableMap::IS_ENABLED)) $criteria->add(CouponTableMap::IS_ENABLED, $this->is_enabled);
        if ($this->isColumnModified(CouponTableMap::EXPIRATION_DATE)) $criteria->add(CouponTableMap::EXPIRATION_DATE, $this->expiration_date);
        if ($this->isColumnModified(CouponTableMap::MAX_USAGE)) $criteria->add(CouponTableMap::MAX_USAGE, $this->max_usage);
        if ($this->isColumnModified(CouponTableMap::IS_CUMULATIVE)) $criteria->add(CouponTableMap::IS_CUMULATIVE, $this->is_cumulative);
        if ($this->isColumnModified(CouponTableMap::IS_REMOVING_POSTAGE)) $criteria->add(CouponTableMap::IS_REMOVING_POSTAGE, $this->is_removing_postage);
        if ($this->isColumnModified(CouponTableMap::IS_AVAILABLE_ON_SPECIAL_OFFERS)) $criteria->add(CouponTableMap::IS_AVAILABLE_ON_SPECIAL_OFFERS, $this->is_available_on_special_offers);
        if ($this->isColumnModified(CouponTableMap::IS_USED)) $criteria->add(CouponTableMap::IS_USED, $this->is_used);
        if ($this->isColumnModified(CouponTableMap::SERIALIZED_CONDITIONS)) $criteria->add(CouponTableMap::SERIALIZED_CONDITIONS, $this->serialized_conditions);
        if ($this->isColumnModified(CouponTableMap::CREATED_AT)) $criteria->add(CouponTableMap::CREATED_AT, $this->created_at);
        if ($this->isColumnModified(CouponTableMap::UPDATED_AT)) $criteria->add(CouponTableMap::UPDATED_AT, $this->updated_at);
        if ($this->isColumnModified(CouponTableMap::VERSION)) $criteria->add(CouponTableMap::VERSION, $this->version);

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @return Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = new Criteria(CouponTableMap::DATABASE_NAME);
        $criteria->add(CouponTableMap::ID, $this->id);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return   int
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * Generic method to set the primary key (id column).
     *
     * @param       int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setId($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {

        return null === $this->getId();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of \Thelia\Model\Coupon (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setCode($this->getCode());
        $copyObj->setType($this->getType());
        $copyObj->setAmount($this->getAmount());
        $copyObj->setIsEnabled($this->getIsEnabled());
        $copyObj->setExpirationDate($this->getExpirationDate());
        $copyObj->setMaxUsage($this->getMaxUsage());
        $copyObj->setIsCumulative($this->getIsCumulative());
        $copyObj->setIsRemovingPostage($this->getIsRemovingPostage());
        $copyObj->setIsAvailableOnSpecialOffers($this->getIsAvailableOnSpecialOffers());
        $copyObj->setIsUsed($this->getIsUsed());
        $copyObj->setSerializedConditions($this->getSerializedConditions());
        $copyObj->setCreatedAt($this->getCreatedAt());
        $copyObj->setUpdatedAt($this->getUpdatedAt());
        $copyObj->setVersion($this->getVersion());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getCouponI18ns() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addCouponI18n($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getCouponVersions() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addCouponVersion($relObj->copy($deepCopy));
                }
            }

        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setId(NULL); // this is a auto-increment column, so set to default value
        }
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return                 \Thelia\Model\Coupon Clone of current object.
     * @throws PropelException
     */
    public function copy($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);

        return $copyObj;
    }


    /**
     * Initializes a collection based on the name of a relation.
     * Avoids crafting an 'init[$relationName]s' method name
     * that wouldn't work when StandardEnglishPluralizer is used.
     *
     * @param      string $relationName The name of the relation to initialize
     * @return void
     */
    public function initRelation($relationName)
    {
        if ('CouponI18n' == $relationName) {
            return $this->initCouponI18ns();
        }
        if ('CouponVersion' == $relationName) {
            return $this->initCouponVersions();
        }
    }

    /**
     * Clears out the collCouponI18ns collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addCouponI18ns()
     */
    public function clearCouponI18ns()
    {
        $this->collCouponI18ns = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collCouponI18ns collection loaded partially.
     */
    public function resetPartialCouponI18ns($v = true)
    {
        $this->collCouponI18nsPartial = $v;
    }

    /**
     * Initializes the collCouponI18ns collection.
     *
     * By default this just sets the collCouponI18ns collection to an empty array (like clearcollCouponI18ns());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initCouponI18ns($overrideExisting = true)
    {
        if (null !== $this->collCouponI18ns && !$overrideExisting) {
            return;
        }
        $this->collCouponI18ns = new ObjectCollection();
        $this->collCouponI18ns->setModel('\Thelia\Model\CouponI18n');
    }

    /**
     * Gets an array of ChildCouponI18n objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildCoupon is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return Collection|ChildCouponI18n[] List of ChildCouponI18n objects
     * @throws PropelException
     */
    public function getCouponI18ns($criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collCouponI18nsPartial && !$this->isNew();
        if (null === $this->collCouponI18ns || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collCouponI18ns) {
                // return empty collection
                $this->initCouponI18ns();
            } else {
                $collCouponI18ns = ChildCouponI18nQuery::create(null, $criteria)
                    ->filterByCoupon($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collCouponI18nsPartial && count($collCouponI18ns)) {
                        $this->initCouponI18ns(false);

                        foreach ($collCouponI18ns as $obj) {
                            if (false == $this->collCouponI18ns->contains($obj)) {
                                $this->collCouponI18ns->append($obj);
                            }
                        }

                        $this->collCouponI18nsPartial = true;
                    }

                    $collCouponI18ns->getInternalIterator()->rewind();

                    return $collCouponI18ns;
                }

                if ($partial && $this->collCouponI18ns) {
                    foreach ($this->collCouponI18ns as $obj) {
                        if ($obj->isNew()) {
                            $collCouponI18ns[] = $obj;
                        }
                    }
                }

                $this->collCouponI18ns = $collCouponI18ns;
                $this->collCouponI18nsPartial = false;
            }
        }

        return $this->collCouponI18ns;
    }

    /**
     * Sets a collection of CouponI18n objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $couponI18ns A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return   ChildCoupon The current object (for fluent API support)
     */
    public function setCouponI18ns(Collection $couponI18ns, ConnectionInterface $con = null)
    {
        $couponI18nsToDelete = $this->getCouponI18ns(new Criteria(), $con)->diff($couponI18ns);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->couponI18nsScheduledForDeletion = clone $couponI18nsToDelete;

        foreach ($couponI18nsToDelete as $couponI18nRemoved) {
            $couponI18nRemoved->setCoupon(null);
        }

        $this->collCouponI18ns = null;
        foreach ($couponI18ns as $couponI18n) {
            $this->addCouponI18n($couponI18n);
        }

        $this->collCouponI18ns = $couponI18ns;
        $this->collCouponI18nsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related CouponI18n objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related CouponI18n objects.
     * @throws PropelException
     */
    public function countCouponI18ns(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collCouponI18nsPartial && !$this->isNew();
        if (null === $this->collCouponI18ns || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collCouponI18ns) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getCouponI18ns());
            }

            $query = ChildCouponI18nQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByCoupon($this)
                ->count($con);
        }

        return count($this->collCouponI18ns);
    }

    /**
     * Method called to associate a ChildCouponI18n object to this object
     * through the ChildCouponI18n foreign key attribute.
     *
     * @param    ChildCouponI18n $l ChildCouponI18n
     * @return   \Thelia\Model\Coupon The current object (for fluent API support)
     */
    public function addCouponI18n(ChildCouponI18n $l)
    {
        if ($l && $locale = $l->getLocale()) {
            $this->setLocale($locale);
            $this->currentTranslations[$locale] = $l;
        }
        if ($this->collCouponI18ns === null) {
            $this->initCouponI18ns();
            $this->collCouponI18nsPartial = true;
        }

        if (!in_array($l, $this->collCouponI18ns->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddCouponI18n($l);
        }

        return $this;
    }

    /**
     * @param CouponI18n $couponI18n The couponI18n object to add.
     */
    protected function doAddCouponI18n($couponI18n)
    {
        $this->collCouponI18ns[]= $couponI18n;
        $couponI18n->setCoupon($this);
    }

    /**
     * @param  CouponI18n $couponI18n The couponI18n object to remove.
     * @return ChildCoupon The current object (for fluent API support)
     */
    public function removeCouponI18n($couponI18n)
    {
        if ($this->getCouponI18ns()->contains($couponI18n)) {
            $this->collCouponI18ns->remove($this->collCouponI18ns->search($couponI18n));
            if (null === $this->couponI18nsScheduledForDeletion) {
                $this->couponI18nsScheduledForDeletion = clone $this->collCouponI18ns;
                $this->couponI18nsScheduledForDeletion->clear();
            }
            $this->couponI18nsScheduledForDeletion[]= clone $couponI18n;
            $couponI18n->setCoupon(null);
        }

        return $this;
    }

    /**
     * Clears out the collCouponVersions collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addCouponVersions()
     */
    public function clearCouponVersions()
    {
        $this->collCouponVersions = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collCouponVersions collection loaded partially.
     */
    public function resetPartialCouponVersions($v = true)
    {
        $this->collCouponVersionsPartial = $v;
    }

    /**
     * Initializes the collCouponVersions collection.
     *
     * By default this just sets the collCouponVersions collection to an empty array (like clearcollCouponVersions());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initCouponVersions($overrideExisting = true)
    {
        if (null !== $this->collCouponVersions && !$overrideExisting) {
            return;
        }
        $this->collCouponVersions = new ObjectCollection();
        $this->collCouponVersions->setModel('\Thelia\Model\CouponVersion');
    }

    /**
     * Gets an array of ChildCouponVersion objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildCoupon is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return Collection|ChildCouponVersion[] List of ChildCouponVersion objects
     * @throws PropelException
     */
    public function getCouponVersions($criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collCouponVersionsPartial && !$this->isNew();
        if (null === $this->collCouponVersions || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collCouponVersions) {
                // return empty collection
                $this->initCouponVersions();
            } else {
                $collCouponVersions = ChildCouponVersionQuery::create(null, $criteria)
                    ->filterByCoupon($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collCouponVersionsPartial && count($collCouponVersions)) {
                        $this->initCouponVersions(false);

                        foreach ($collCouponVersions as $obj) {
                            if (false == $this->collCouponVersions->contains($obj)) {
                                $this->collCouponVersions->append($obj);
                            }
                        }

                        $this->collCouponVersionsPartial = true;
                    }

                    $collCouponVersions->getInternalIterator()->rewind();

                    return $collCouponVersions;
                }

                if ($partial && $this->collCouponVersions) {
                    foreach ($this->collCouponVersions as $obj) {
                        if ($obj->isNew()) {
                            $collCouponVersions[] = $obj;
                        }
                    }
                }

                $this->collCouponVersions = $collCouponVersions;
                $this->collCouponVersionsPartial = false;
            }
        }

        return $this->collCouponVersions;
    }

    /**
     * Sets a collection of CouponVersion objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $couponVersions A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return   ChildCoupon The current object (for fluent API support)
     */
    public function setCouponVersions(Collection $couponVersions, ConnectionInterface $con = null)
    {
        $couponVersionsToDelete = $this->getCouponVersions(new Criteria(), $con)->diff($couponVersions);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->couponVersionsScheduledForDeletion = clone $couponVersionsToDelete;

        foreach ($couponVersionsToDelete as $couponVersionRemoved) {
            $couponVersionRemoved->setCoupon(null);
        }

        $this->collCouponVersions = null;
        foreach ($couponVersions as $couponVersion) {
            $this->addCouponVersion($couponVersion);
        }

        $this->collCouponVersions = $couponVersions;
        $this->collCouponVersionsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related CouponVersion objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related CouponVersion objects.
     * @throws PropelException
     */
    public function countCouponVersions(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collCouponVersionsPartial && !$this->isNew();
        if (null === $this->collCouponVersions || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collCouponVersions) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getCouponVersions());
            }

            $query = ChildCouponVersionQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByCoupon($this)
                ->count($con);
        }

        return count($this->collCouponVersions);
    }

    /**
     * Method called to associate a ChildCouponVersion object to this object
     * through the ChildCouponVersion foreign key attribute.
     *
     * @param    ChildCouponVersion $l ChildCouponVersion
     * @return   \Thelia\Model\Coupon The current object (for fluent API support)
     */
    public function addCouponVersion(ChildCouponVersion $l)
    {
        if ($this->collCouponVersions === null) {
            $this->initCouponVersions();
            $this->collCouponVersionsPartial = true;
        }

        if (!in_array($l, $this->collCouponVersions->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddCouponVersion($l);
        }

        return $this;
    }

    /**
     * @param CouponVersion $couponVersion The couponVersion object to add.
     */
    protected function doAddCouponVersion($couponVersion)
    {
        $this->collCouponVersions[]= $couponVersion;
        $couponVersion->setCoupon($this);
    }

    /**
     * @param  CouponVersion $couponVersion The couponVersion object to remove.
     * @return ChildCoupon The current object (for fluent API support)
     */
    public function removeCouponVersion($couponVersion)
    {
        if ($this->getCouponVersions()->contains($couponVersion)) {
            $this->collCouponVersions->remove($this->collCouponVersions->search($couponVersion));
            if (null === $this->couponVersionsScheduledForDeletion) {
                $this->couponVersionsScheduledForDeletion = clone $this->collCouponVersions;
                $this->couponVersionsScheduledForDeletion->clear();
            }
            $this->couponVersionsScheduledForDeletion[]= clone $couponVersion;
            $couponVersion->setCoupon(null);
        }

        return $this;
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id = null;
        $this->code = null;
        $this->type = null;
        $this->amount = null;
        $this->is_enabled = null;
        $this->expiration_date = null;
        $this->max_usage = null;
        $this->is_cumulative = null;
        $this->is_removing_postage = null;
        $this->is_available_on_special_offers = null;
        $this->is_used = null;
        $this->serialized_conditions = null;
        $this->created_at = null;
        $this->updated_at = null;
        $this->version = null;
        $this->alreadyInSave = false;
        $this->clearAllReferences();
        $this->applyDefaultValues();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);
    }

    /**
     * Resets all references to other model objects or collections of model objects.
     *
     * This method is a user-space workaround for PHP's inability to garbage collect
     * objects with circular references (even in PHP 5.3). This is currently necessary
     * when using Propel in certain daemon or large-volume/high-memory operations.
     *
     * @param      boolean $deep Whether to also clear the references on all referrer objects.
     */
    public function clearAllReferences($deep = false)
    {
        if ($deep) {
            if ($this->collCouponI18ns) {
                foreach ($this->collCouponI18ns as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collCouponVersions) {
                foreach ($this->collCouponVersions as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        // i18n behavior
        $this->currentLocale = 'en_US';
        $this->currentTranslations = null;

        if ($this->collCouponI18ns instanceof Collection) {
            $this->collCouponI18ns->clearIterator();
        }
        $this->collCouponI18ns = null;
        if ($this->collCouponVersions instanceof Collection) {
            $this->collCouponVersions->clearIterator();
        }
        $this->collCouponVersions = null;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(CouponTableMap::DEFAULT_STRING_FORMAT);
    }

    // timestampable behavior

    /**
     * Mark the current object so that the update date doesn't get updated during next save
     *
     * @return     ChildCoupon The current object (for fluent API support)
     */
    public function keepUpdateDateUnchanged()
    {
        $this->modifiedColumns[] = CouponTableMap::UPDATED_AT;

        return $this;
    }

    // i18n behavior

    /**
     * Sets the locale for translations
     *
     * @param     string $locale Locale to use for the translation, e.g. 'fr_FR'
     *
     * @return    ChildCoupon The current object (for fluent API support)
     */
    public function setLocale($locale = 'en_US')
    {
        $this->currentLocale = $locale;

        return $this;
    }

    /**
     * Gets the locale for translations
     *
     * @return    string $locale Locale to use for the translation, e.g. 'fr_FR'
     */
    public function getLocale()
    {
        return $this->currentLocale;
    }

    /**
     * Returns the current translation for a given locale
     *
     * @param     string $locale Locale to use for the translation, e.g. 'fr_FR'
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ChildCouponI18n */
    public function getTranslation($locale = 'en_US', ConnectionInterface $con = null)
    {
        if (!isset($this->currentTranslations[$locale])) {
            if (null !== $this->collCouponI18ns) {
                foreach ($this->collCouponI18ns as $translation) {
                    if ($translation->getLocale() == $locale) {
                        $this->currentTranslations[$locale] = $translation;

                        return $translation;
                    }
                }
            }
            if ($this->isNew()) {
                $translation = new ChildCouponI18n();
                $translation->setLocale($locale);
            } else {
                $translation = ChildCouponI18nQuery::create()
                    ->filterByPrimaryKey(array($this->getPrimaryKey(), $locale))
                    ->findOneOrCreate($con);
                $this->currentTranslations[$locale] = $translation;
            }
            $this->addCouponI18n($translation);
        }

        return $this->currentTranslations[$locale];
    }

    /**
     * Remove the translation for a given locale
     *
     * @param     string $locale Locale to use for the translation, e.g. 'fr_FR'
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return    ChildCoupon The current object (for fluent API support)
     */
    public function removeTranslation($locale = 'en_US', ConnectionInterface $con = null)
    {
        if (!$this->isNew()) {
            ChildCouponI18nQuery::create()
                ->filterByPrimaryKey(array($this->getPrimaryKey(), $locale))
                ->delete($con);
        }
        if (isset($this->currentTranslations[$locale])) {
            unset($this->currentTranslations[$locale]);
        }
        foreach ($this->collCouponI18ns as $key => $translation) {
            if ($translation->getLocale() == $locale) {
                unset($this->collCouponI18ns[$key]);
                break;
            }
        }

        return $this;
    }

    /**
     * Returns the current translation
     *
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ChildCouponI18n */
    public function getCurrentTranslation(ConnectionInterface $con = null)
    {
        return $this->getTranslation($this->getLocale(), $con);
    }


        /**
         * Get the [title] column value.
         *
         * @return   string
         */
        public function getTitle()
        {
        return $this->getCurrentTranslation()->getTitle();
    }


        /**
         * Set the value of [title] column.
         *
         * @param      string $v new value
         * @return   \Thelia\Model\CouponI18n The current object (for fluent API support)
         */
        public function setTitle($v)
        {    $this->getCurrentTranslation()->setTitle($v);

        return $this;
    }


        /**
         * Get the [short_description] column value.
         *
         * @return   string
         */
        public function getShortDescription()
        {
        return $this->getCurrentTranslation()->getShortDescription();
    }


        /**
         * Set the value of [short_description] column.
         *
         * @param      string $v new value
         * @return   \Thelia\Model\CouponI18n The current object (for fluent API support)
         */
        public function setShortDescription($v)
        {    $this->getCurrentTranslation()->setShortDescription($v);

        return $this;
    }


        /**
         * Get the [description] column value.
         *
         * @return   string
         */
        public function getDescription()
        {
        return $this->getCurrentTranslation()->getDescription();
    }


        /**
         * Set the value of [description] column.
         *
         * @param      string $v new value
         * @return   \Thelia\Model\CouponI18n The current object (for fluent API support)
         */
        public function setDescription($v)
        {    $this->getCurrentTranslation()->setDescription($v);

        return $this;
    }

    // versionable behavior

    /**
     * Enforce a new Version of this object upon next save.
     *
     * @return \Thelia\Model\Coupon
     */
    public function enforceVersioning()
    {
        $this->enforceVersion = true;

        return $this;
    }

    /**
     * Checks whether the current state must be recorded as a version
     *
     * @return  boolean
     */
    public function isVersioningNecessary($con = null)
    {
        if ($this->alreadyInSave) {
            return false;
        }

        if ($this->enforceVersion) {
            return true;
        }

        if (ChildCouponQuery::isVersioningEnabled() && ($this->isNew() || $this->isModified()) || $this->isDeleted()) {
            return true;
        }

        return false;
    }

    /**
     * Creates a version of the current object and saves it.
     *
     * @param   ConnectionInterface $con the connection to use
     *
     * @return  ChildCouponVersion A version object
     */
    public function addVersion($con = null)
    {
        $this->enforceVersion = false;

        $version = new ChildCouponVersion();
        $version->setId($this->getId());
        $version->setCode($this->getCode());
        $version->setType($this->getType());
        $version->setAmount($this->getAmount());
        $version->setIsEnabled($this->getIsEnabled());
        $version->setExpirationDate($this->getExpirationDate());
        $version->setMaxUsage($this->getMaxUsage());
        $version->setIsCumulative($this->getIsCumulative());
        $version->setIsRemovingPostage($this->getIsRemovingPostage());
        $version->setIsAvailableOnSpecialOffers($this->getIsAvailableOnSpecialOffers());
        $version->setIsUsed($this->getIsUsed());
        $version->setSerializedConditions($this->getSerializedConditions());
        $version->setCreatedAt($this->getCreatedAt());
        $version->setUpdatedAt($this->getUpdatedAt());
        $version->setVersion($this->getVersion());
        $version->setCoupon($this);
        $version->save($con);

        return $version;
    }

    /**
     * Sets the properties of the current object to the value they had at a specific version
     *
     * @param   integer $versionNumber The version number to read
     * @param   ConnectionInterface $con The connection to use
     *
     * @return  ChildCoupon The current object (for fluent API support)
     */
    public function toVersion($versionNumber, $con = null)
    {
        $version = $this->getOneVersion($versionNumber, $con);
        if (!$version) {
            throw new PropelException(sprintf('No ChildCoupon object found with version %d', $version));
        }
        $this->populateFromVersion($version, $con);

        return $this;
    }

    /**
     * Sets the properties of the current object to the value they had at a specific version
     *
     * @param ChildCouponVersion $version The version object to use
     * @param ConnectionInterface   $con the connection to use
     * @param array                 $loadedObjects objects that been loaded in a chain of populateFromVersion calls on referrer or fk objects.
     *
     * @return ChildCoupon The current object (for fluent API support)
     */
    public function populateFromVersion($version, $con = null, &$loadedObjects = array())
    {
        $loadedObjects['ChildCoupon'][$version->getId()][$version->getVersion()] = $this;
        $this->setId($version->getId());
        $this->setCode($version->getCode());
        $this->setType($version->getType());
        $this->setAmount($version->getAmount());
        $this->setIsEnabled($version->getIsEnabled());
        $this->setExpirationDate($version->getExpirationDate());
        $this->setMaxUsage($version->getMaxUsage());
        $this->setIsCumulative($version->getIsCumulative());
        $this->setIsRemovingPostage($version->getIsRemovingPostage());
        $this->setIsAvailableOnSpecialOffers($version->getIsAvailableOnSpecialOffers());
        $this->setIsUsed($version->getIsUsed());
        $this->setSerializedConditions($version->getSerializedConditions());
        $this->setCreatedAt($version->getCreatedAt());
        $this->setUpdatedAt($version->getUpdatedAt());
        $this->setVersion($version->getVersion());

        return $this;
    }

    /**
     * Gets the latest persisted version number for the current object
     *
     * @param   ConnectionInterface $con the connection to use
     *
     * @return  integer
     */
    public function getLastVersionNumber($con = null)
    {
        $v = ChildCouponVersionQuery::create()
            ->filterByCoupon($this)
            ->orderByVersion('desc')
            ->findOne($con);
        if (!$v) {
            return 0;
        }

        return $v->getVersion();
    }

    /**
     * Checks whether the current object is the latest one
     *
     * @param   ConnectionInterface $con the connection to use
     *
     * @return  Boolean
     */
    public function isLastVersion($con = null)
    {
        return $this->getLastVersionNumber($con) == $this->getVersion();
    }

    /**
     * Retrieves a version object for this entity and a version number
     *
     * @param   integer $versionNumber The version number to read
     * @param   ConnectionInterface $con the connection to use
     *
     * @return  ChildCouponVersion A version object
     */
    public function getOneVersion($versionNumber, $con = null)
    {
        return ChildCouponVersionQuery::create()
            ->filterByCoupon($this)
            ->filterByVersion($versionNumber)
            ->findOne($con);
    }

    /**
     * Gets all the versions of this object, in incremental order
     *
     * @param   ConnectionInterface $con the connection to use
     *
     * @return  ObjectCollection A list of ChildCouponVersion objects
     */
    public function getAllVersions($con = null)
    {
        $criteria = new Criteria();
        $criteria->addAscendingOrderByColumn(CouponVersionTableMap::VERSION);

        return $this->getCouponVersions($criteria, $con);
    }

    /**
     * Compares the current object with another of its version.
     * <code>
     * print_r($book->compareVersion(1));
     * => array(
     *   '1' => array('Title' => 'Book title at version 1'),
     *   '2' => array('Title' => 'Book title at version 2')
     * );
     * </code>
     *
     * @param   integer             $versionNumber
     * @param   string              $keys Main key used for the result diff (versions|columns)
     * @param   ConnectionInterface $con the connection to use
     * @param   array               $ignoredColumns  The columns to exclude from the diff.
     *
     * @return  array A list of differences
     */
    public function compareVersion($versionNumber, $keys = 'columns', $con = null, $ignoredColumns = array())
    {
        $fromVersion = $this->toArray();
        $toVersion = $this->getOneVersion($versionNumber, $con)->toArray();

        return $this->computeDiff($fromVersion, $toVersion, $keys, $ignoredColumns);
    }

    /**
     * Compares two versions of the current object.
     * <code>
     * print_r($book->compareVersions(1, 2));
     * => array(
     *   '1' => array('Title' => 'Book title at version 1'),
     *   '2' => array('Title' => 'Book title at version 2')
     * );
     * </code>
     *
     * @param   integer             $fromVersionNumber
     * @param   integer             $toVersionNumber
     * @param   string              $keys Main key used for the result diff (versions|columns)
     * @param   ConnectionInterface $con the connection to use
     * @param   array               $ignoredColumns  The columns to exclude from the diff.
     *
     * @return  array A list of differences
     */
    public function compareVersions($fromVersionNumber, $toVersionNumber, $keys = 'columns', $con = null, $ignoredColumns = array())
    {
        $fromVersion = $this->getOneVersion($fromVersionNumber, $con)->toArray();
        $toVersion = $this->getOneVersion($toVersionNumber, $con)->toArray();

        return $this->computeDiff($fromVersion, $toVersion, $keys, $ignoredColumns);
    }

    /**
     * Computes the diff between two versions.
     * <code>
     * print_r($book->computeDiff(1, 2));
     * => array(
     *   '1' => array('Title' => 'Book title at version 1'),
     *   '2' => array('Title' => 'Book title at version 2')
     * );
     * </code>
     *
     * @param   array     $fromVersion     An array representing the original version.
     * @param   array     $toVersion       An array representing the destination version.
     * @param   string    $keys            Main key used for the result diff (versions|columns).
     * @param   array     $ignoredColumns  The columns to exclude from the diff.
     *
     * @return  array A list of differences
     */
    protected function computeDiff($fromVersion, $toVersion, $keys = 'columns', $ignoredColumns = array())
    {
        $fromVersionNumber = $fromVersion['Version'];
        $toVersionNumber = $toVersion['Version'];
        $ignoredColumns = array_merge(array(
            'Version',
        ), $ignoredColumns);
        $diff = array();
        foreach ($fromVersion as $key => $value) {
            if (in_array($key, $ignoredColumns)) {
                continue;
            }
            if ($toVersion[$key] != $value) {
                switch ($keys) {
                    case 'versions':
                        $diff[$fromVersionNumber][$key] = $value;
                        $diff[$toVersionNumber][$key] = $toVersion[$key];
                        break;
                    default:
                        $diff[$key] = array(
                            $fromVersionNumber => $value,
                            $toVersionNumber => $toVersion[$key],
                        );
                        break;
                }
            }
        }

        return $diff;
    }
    /**
     * retrieve the last $number versions.
     *
     * @param Integer $number the number of record to return.
     * @return PropelCollection|array \Thelia\Model\CouponVersion[] List of \Thelia\Model\CouponVersion objects
     */
    public function getLastVersions($number = 10, $criteria = null, $con = null)
    {
        $criteria = ChildCouponVersionQuery::create(null, $criteria);
        $criteria->addDescendingOrderByColumn(CouponVersionTableMap::VERSION);
        $criteria->limit($number);

        return $this->getCouponVersions($criteria, $con);
    }
    /**
     * Code to be run before persisting the object
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preSave(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after persisting the object
     * @param ConnectionInterface $con
     */
    public function postSave(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before inserting to database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preInsert(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after inserting to database
     * @param ConnectionInterface $con
     */
    public function postInsert(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before updating the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preUpdate(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after updating the object in database
     * @param ConnectionInterface $con
     */
    public function postUpdate(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before deleting the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preDelete(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after deleting the object in database
     * @param ConnectionInterface $con
     */
    public function postDelete(ConnectionInterface $con = null)
    {

    }


    /**
     * Derived method to catches calls to undefined methods.
     *
     * Provides magic import/export method support (fromXML()/toXML(), fromYAML()/toYAML(), etc.).
     * Allows to define default __call() behavior if you overwrite __call()
     *
     * @param string $name
     * @param mixed  $params
     *
     * @return array|string
     */
    public function __call($name, $params)
    {
        if (0 === strpos($name, 'get')) {
            $virtualColumn = substr($name, 3);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }

            $virtualColumn = lcfirst($virtualColumn);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }
        }

        if (0 === strpos($name, 'from')) {
            $format = substr($name, 4);

            return $this->importFrom($format, reset($params));
        }

        if (0 === strpos($name, 'to')) {
            $format = substr($name, 2);
            $includeLazyLoadColumns = isset($params[0]) ? $params[0] : true;

            return $this->exportTo($format, $includeLazyLoadColumns);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method: %s.', $name));
    }

}
