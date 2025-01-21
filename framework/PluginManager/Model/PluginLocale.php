<?php
/**
 * Этот файл является частью пакета GM Framework.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\PluginManager\Model;

use Gm;
use Closure;
use Gm\Db\Sql\Where;
use Gm\Db\Sql\Select;
use Gm\Db\ActiveRecord;

/**
 * PluginLocale класс шаблона активной записи, предназначен для хранения локализации 
 * объекта плагина.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\PluginManager\Model
 * @since 2.0
 */
class PluginLocale extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function primaryKey(): string
    {
        return 'plugin_id';
    }

    /**
     * {@inheritdoc}
     */
    public function tableName(): string
    {
        return '{{plugin_locale}}';
    }

    /**
     * {@inheritdoc}
     */
    public function maskedAttributes(): array
    {
        return [
            'pluginId'    => 'plugin_id', // идентификатор плагина
            'languageId'  => 'language_id', // идентификатор языка
            'name'        => 'name', // название
            'description' => 'description' // описание
        ];
    }

    /**
     * Возвращает запись по указанному идентификатору плагина и коду языка.
     * 
     * @see ActiveRecord::selectOne()
     * 
     * @param int $pluginId Идентификатор плагина.
     * @param null|int $languageId Код языка. Если `null`, текуший код языка (по умолчанию `null`).
     * 
     * @return null|ActiveRecord Активная запись при успешном запросе, иначе `null`.
     */
    public function get(int $pluginId, int $languageId = null): ?ActiveRecord
    {
        return $this->selectOne([
            'plugin_id'   => $pluginId,
            'language_id' => $languageId === null ? Gm::$app->language->code : $languageId
        ]);
    }

    /**
     * {@inheritdoc}
     * 
     * Условие обновления записи если используется составной первичный ключ.
     */
    protected function updateProcessCondition(array &$where): void
    {
        $where['plugin_id']   = $this->pluginId;
        $where['language_id'] = $this->languageId;
    }

    /**
     * {@inheritdoc}
     * 
     * Условие удаления записи если используется составной первичный ключ.
     */
    protected function deleteProcessCondition(array &$where): void
    {
        $where['plugin_id']   = $this->pluginId;
        $where['language_id'] = $this->languageId;
    }

    /**
     * Удаляет все записи из таблицы.
     * 
     * @return bool|int Если `false`, ошибка выполнения запроса. Иначе, количество удалённых записей.
     */
    public function deleteAll()
    {
        return $this->deleteRecord([]);
    }

    /**
     * Удаляет записи из таблицы по указаному виджету.
     * 
     * @return bool|int Если `false`, ошибка выполнения запроса. Иначе, количество удалённых записей.
     */
    public function deleteFromPlugin(int $pluginId)
    {
        return $this->deleteRecord(['plugin_id' => $pluginId]);
    }

    /**
     * Возвращает все записи для текущего языка (если не указано условие запроса).
     * 
     * {@inheritdoc}
     */
    public function fetchAll(
        string $fetchKey = null, 
        array $columns = ['*'], 
        Where|Closure|string|array|null $where = null, 
        string|array|null $order = null
    ): array
    {
        if ($where === null) {
            $where = ['language_id' => Gm::$app->language->code];
        }
        /** @var Select $select */
        $select = $this->select($columns, $where);
        if ($order === null)
            $order = ['name' => 'ASC'];
        $select->order($order);
        return $this
            ->getDb()
                ->createCommand($select)
                    ->queryAll($fetchKey);
    }

    /**
     * Возвращает имена виджетов.
     * 
     * @param string $attribute Название атрибута ('name', 'description') возвращаемого 
     *     для каждого идентификатора. Если значение `null`, возвратит все атрибуты 
     *     (по умолчанию `null`).
     * @param int $languageCode Идентификатор языка. Если значение `null`, то идентификатор 
     *     текущего языка (по умолчанию `null`).
     * 
     * @return array<int, array{name:string, description:string}>
     */
    public function fetchNames(string $attribute = null, int $languageCode = null): array
    {
        $db = $this->getDb();
        $sql = 'SELECT IF(`l`.`name` IS NULL, `m`.`name`, `l`.`name`) `name`,  '
             . 'IF(`l`.`description` IS NULL, `m`.`description`, `l`.`description`) `description`, `m`.`id` '
             . 'FROM `{{plugin}}` `m` LEFT JOIN `{{plugin_locale}}` `l` '
             . 'ON `m`.`id`=`l`.`plugin_id` AND `l`.`language_id`=:language';
        /** @var \Gm\Db\Adapter\Driver\AbstractCommand $command */ 
        $command = $db->createCommand($sql);
        $command->bindValues([
            ':language' => $languageCode ?: Gm::$app->language->code
        ]);
        if ($attribute)
            return $command->queryToColumn('id', $attribute);
        else
            return $command->queryAll('id');
    }

    /**
     * Возвращает атрибуты локализации плагина.
     * 
     * @param integer $pluginId Идентификатор плагина.
     * 
     * @return array{name:string, description:string}|null
     */
    public function fetchLocale(int $pluginId): ?array
    {
        /** @var Select $select */
        $select = $this->select(
            ['name', 'description'],
            [
                'language_id' => Gm::$app->language->code,
                'plugin_id'   => $pluginId
            ]
        );
        return $this
            ->getDb()
                ->createCommand($select)
                    ->queryOne();
    }

    /**
     * Возвращает набор всех строк (ассоциативные массивы) текущей таблицы.
     * 
     * Ключом каждой строки является значение первичного ключа {@see ActiveRecord::tableName()} 
     * текущей таблицы.
     * 
     * @param bool $caching Указывает на принудительное кэширование. Если служба кэширования 
     *     отключена, кэширование не будет выполнено (по умолчанию `true`).
     * 
     * @return array
     */
    public function getAll(bool $caching = true): ?array
    {
        if ($caching)
            return $this->cache(
                function () { return $this->fetchAll($this->primaryKey(), $this->maskedAttributes()); },
                null,
                true
            );
        else
            return $this->fetchAll($this->primaryKey(), $this->maskedAttributes());
    }
}
