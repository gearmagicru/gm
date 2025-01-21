<?php
/**
 * Этот файл является частью пакета GM Framework.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Db\Sql;

use Gm;
use Gm\Db\Adapter\Platform\PlatformInterface;
use Gm\Db\Adapter\Driver\DriverInterface;
use Gm\Db\Adapter\ParameterContainer;

/**
 * Оператор Replace (замены) инструкции SQL.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @author Zend Framework (http://framework.zend.com/)
 * @package Gm\Db\Sql
 * @since 2.0
 */
class Replace extends AbstractSql
{
    /**#@+
     * @const Константы.
     */
    public const SPECIFICATION_REPLACE = 'replace';
    public const SPECIFICATION_SELECT = 'select';
    public const VALUES_MERGE = 'merge';
    public const VALUES_SET   = 'set';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    protected array $specifications = [
        self::SPECIFICATION_REPLACE => 'REPLACE INTO %1$s (%2$s) VALUES (%3$s)',
        self::SPECIFICATION_SELECT  => 'INSERT INTO %1$s %2$s %3$s',
    ];

    /**
     * Имя или идентификатор таблицы.
     * 
     * @see Replace::into()
     * 
     * @var TableIdentifier|string
     */
    protected TableIdentifier|string $table = '';

    /**
     * Столбцы.
     * 
     * @see Replace::columns()
     * @see Replace::values()
     * 
     * @var array
     */
    protected array $columns = [];

    /**
     * Оператор Select.
     * 
     * @var Select|array|null
     */
    protected Select|array|null $select = null;

    /**
     * Конструктор класса.
     *
     * @param TableIdentifier|string|null $table Имя или идентификатор таблицы (по умолчанию `null`).
     */
    public function __construct(TableIdentifier|string|null $table = null)
    {
        if ($table) {
            $this->into($table);
        }
    }

    /**
     * Добавляет таблицу в выражение INTO инструкции SQL.
     *
     * @param TableIdentifier|string $table Имя или идентификатор таблицы.
     * 
     * @return $this
     */
    public function into(TableIdentifier|string $table): static
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Устанавливает столбцы.
     *
     * @param array $columns Столбцы.
     * 
     * @return $this
     */
    public function columns(array $columns): static
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * Устанавливает значения для добавления записи.
     *
     * @param Select|array $values
     * @param  string $flag Константа: `VALUES_MERGE`, `VALUES_SET` (по умолчачанию `VALUES_SET`).
     * 
     * @return $this
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function values(Select|array $values, string $flag = self::VALUES_SET): static
    {
        if ($values instanceof Select) {
            if ($flag == self::VALUES_MERGE) {
                throw new Exception\InvalidArgumentException(
                    'A Gm\Db\Sql\Select instance cannot be provided with the merge flag'
                );
            }
            $this->select = $values;
            return $this;
        }

        if (!is_array($values)) {
            throw new Exception\InvalidArgumentException(
                'values() expects an array of values or Gm\Db\Sql\Select instance'
            );
        }
        if ($this->select && $flag == self::VALUES_MERGE) {
            throw new Exception\InvalidArgumentException(
                'An array of values cannot be provided with the merge flag when a Gm\Db\Sql\Select instance already exists as the value source'
            );
        }

        if ($flag == self::VALUES_SET) {
            $this->columns = $values;
        } else {
            foreach ($values as $column=>$value) {
                $this->columns[$column] = $value;
            }
        }
        return $this;
    }

    /**
     * Создаёт выражение INTO SELECT для инструкции SQL.
     *
     * @param Select $select Оператор Select.
     * 
     * @return $this
     */
    public function select(Select $select): static
    {
        return $this->values($select);
    }

    /**
     * Возвращает необработанное состояние.
     *
     * @param string|null $key Ключ: 'table', 'columns', 'values'.
     * 
     * @return mixed
     */
    public function getRawState(string $key = null): mixed
    {
        $rawState = [
            'table'   => $this->table,
            'columns' => array_keys($this->columns),
            'values'  => array_values($this->columns)
        ];
        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }

    /**
     * Возвращает выражение REPLACE для инструкции SQL.
     * 
     * @param PlatformInterface $platform Платформа адаптера.
     * @param DriverInterface|null $driver Драйвера подключения (по умолчанию `null`).
     * @param ParameterContainer|null $parameterContainer Контейнер параметров (по умолчанию `null`).
     * 
     * @return string
     * 
     * @throws Exception\InvalidArgumentException
     */
    protected function processReplace(
        PlatformInterface $platform, 
        ?DriverInterface $driver = null, 
        ?ParameterContainer $parameterContainer = null
    ): string
    {
        if ($this->select) return '';

        if (!$this->columns) {
            throw new Exception\InvalidArgumentException(
                Gm::t('app', 'values or select should be present')
            );
        }

        $columns = [];
        $values  = [];
        foreach ($this->columns as $column=>$value) {
            $columns[] = $platform->quoteIdentifier($column);
            if (is_scalar($value) && $parameterContainer) {
                $values[] = $driver->formatParameterName($column);
                $parameterContainer->offsetSet($column, $value);
            } else {
                $values[] = $this->resolveColumnValue(
                    $value,
                    $platform,
                    $driver,
                    $parameterContainer
                );
            }
        }
        return sprintf(
            $this->specifications[static::SPECIFICATION_REPLACE],
            $this->resolveTable($this->table, $platform, $driver, $parameterContainer),
            implode(', ', $columns),
            implode(', ', $values)
        );
    }

    /**
     * Возвращает выражение SELECT для инструкции SQL.
     * 
     * @param PlatformInterface $platform Платформа адаптера.
     * @param DriverInterface|null $driver Драйвера подключения (по умолчанию `null`).
     * @param ParameterContainer|null $parameterContainer Контейнер параметров (по умолчанию `null`).
     * 
     * @return string
     * 
     * @throws Exception\InvalidArgumentException
     */
    protected function processSelect(
        PlatformInterface $platform, 
        ?DriverInterface $driver = null, 
        ?ParameterContainer $parameterContainer = null
    ): string
    {
        if (!$this->select) return '';

        $selectSql = $this->processSubSelect($this->select, $platform, $driver, $parameterContainer);

        $columns = array_map(array($platform, 'quoteIdentifier'), array_keys($this->columns));
        $columns = implode(', ', $columns);

        return sprintf(
            $this->specifications[static::SPECIFICATION_SELECT],
            $this->resolveTable($this->table, $platform, $driver, $parameterContainer),
            $columns ? "($columns)" : "",
            $selectSql
        );
    }

    /**
     * Устанавливает значение столбцу, когда к столбцу обращаются как к свойству 
     * объекта. 
     * 
     * Применяется VALUES_MERGE.
     * 
     * @param string $column Столбец.
     * @param mixed $value Значение.
     * 
     * @return void
     */
    public function __set(string $column, mixed $value): void
    {
        $this->columns[$column] = $value;
    }

    /**
     * Удаляет столбец, когда к столбцу обращаются как к свойству объекта.
     *
     * @param string $column Столбец.
     * 
     * @return void
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function __unset(string $column): void
    {
        if (!isset($this->columns[$column])) {
            throw new Exception\InvalidArgumentException(Gm::t('app', 'The key {0} was not found in this objects column list', [$column]));
        }

        unset($this->columns[$column]);
    }

    /**
     * Проверяет, существует ли столбец, когда к столбцу обращаются как к свойству 
     * объекта. 
     *
     * @param string $column Столбец.
     * 
     * @return bool
     */
    public function __isset(string $column): bool
    {
        return isset($this->columns[$column]);
    }

    /**
     * Возращает значение по указанному столбцу.
     *
     * @param string $column Столбец.
     * 
     * @return mixed
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function __get(string $column): mixed
    {
        if (!isset($this->columns[$column])) {
            throw new Exception\InvalidArgumentException(
                Gm::t('app', 'The key {0} was not found in this objects column list', [$column])
            );
        }
        return $this->columns[$column];
    }
}
