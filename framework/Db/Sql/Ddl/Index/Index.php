<?php
/**
 * Этот файл является частью пакета GM Framework.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Db\Sql\Ddl\Index;

/**
 * Класс индекса таблицы.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @author Zend Framework (http://framework.zend.com/)
 * @package Gm\Db\Sql\Ddl\Index
 * @since 2.0
 */
class Index extends AbstractIndex
{
    /**
     * {@inheritdoc}
     */
    protected string $specification = 'INDEX %s(...)';

    /**
     * Длина.
     * 
     * @see Index::__construct()
     * 
     * @var array
     */
    protected array $lengths;

    /**
     * @param string $column Имя столбца таблицы.
     * @param string $name Имя индекса (по умолчанию '').
     * @param array $lengths Длина  (по умолчанию `[]`).
     */
    public function __construct(string $column, string $name = '', array $lengths = [])
    {
        $this->setColumns($column);
        $this->setName($name);
        $this->lengths = $lengths;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpressionData(): array
    {
        $colCount     = count($this->columns);
        $values       = [];
        $values[]     = $this->name ?: '';
        $newSpecTypes = [self::TYPE_IDENTIFIER];
        $newSpecParts = [];

        for ($i = 0; $i < $colCount; $i++) {
            $specPart = '%s';

            if (isset($this->lengths[$i])) {
                $specPart .= "({$this->lengths[$i]})";
            }

            $newSpecParts[] = $specPart;
            $newSpecTypes[] = self::TYPE_IDENTIFIER;
        }

        $newSpec = str_replace('...', implode(', ', $newSpecParts), $this->specification);
        return [
            [
                $newSpec,
                array_merge($values, $this->columns),
                $newSpecTypes,
            ]
        ];
    }
}
