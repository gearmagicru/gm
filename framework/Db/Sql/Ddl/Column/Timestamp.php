<?php
/**
 * Этот файл является частью пакета GM Framework.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Db\Sql\Ddl\Column;

/**
 * Класс столбца с типом данных "TIMESTAMP" (дата и время в диапазоне: от 
 * "1970-01-01 00:00:01" UTC до "2038-01-19 03:14:07" UTC).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @author Zend Framework (http://framework.zend.com/)
 * @package Gm\Db\Sql\Ddl
 * @since 2.0
 */
class Timestamp extends AbstractTimestampColumn
{
    /**
     * {@inheritdoc}
     */
    protected string $type = 'TIMESTAMP';
}
