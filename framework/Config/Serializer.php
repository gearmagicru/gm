<?php
/**
 * Этот файл является частью пакета GM Framework.
 * 
 * @see https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Config;

use Gm;
use Gm\Exception;

/**
 * Конфигуратор реализуется в классах приложений, модулей и служб для настройки их
 * свойств из файлов конфигурации.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Config
 * @since 2.0
 */
class Serializer extends BaseConfig
{
    /**
     * Название файла конфигурации.
     *
     * @var null|string
     */
    protected $filename;

    /**
     * Устанавливает имя файла конфигурации.
     * 
     * @param string $filename Имя файла конфигурации.
     * 
     * @return string Имя файла конфигурации.
     */
    public function setFilename(string $filename)
    {
        return $this->filename = $filename;
    }

    /**
     * Возвращает название файла конфигурации.
     * 
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * Загрузки файла конфигурации.
     * 
     * @param string $filename Имя файла конфигурации, если значение `null` используется 
     *    текущий файл конфигурации {@see Serializer::$filename} (по умолчанию `null`).
     * 
     * @return $this
     * 
     * @throws Exception\FormatException Невозможно выполнить сериализацию.
     */
    public function load(string $filename = null)
    {
        if ($filename === null) {
            $filename = $this->filename;
        }

        $serialized = require($filename);
        $vars = unserialize($serialized);
        if ($vars === false) {
            throw new Exception\FormatException(sprintf('Exception unserialize file "%s"', $filename));
        }
        $this->setAll($vars);
        return $this;
    }

    /**
     * Проверяет существование файла конфигурации.
     * 
     * @return bool
     */
    public function exists(string $filename = null): bool
    {
        if ($filename === null) {
            $filename = $this->filename;
        }
        return file_exists($filename);
    }

    /**
     * Сохраняет параметры конфигурации в файл.
     * 
     * @param string $filename Имя файла конфигурации, если значение `null` используется 
     *    текущий файл конфигурации {@see Serializer::$filename} (по умолчанию `null`).
     * 
     * @return $this
     * 
     * @throws Exception\FormatException Невозможно выполнить сериализацию.
     */
    public function save(string $filename = null)
    {
        if ($filename === null) {
            $filename = $this->filename;
        }

        try {
            $str = serialize($this->getAll());
            $str = "<?php return '$str' ?>";
            if (file_put_contents($filename, $str) === false) {
                throw new Exception\FormatException(sprintf('Unable to save configuration file "%s"', $filename));
            }
        } catch (\Exception $e) {
            Gm::error($e->getMessage());
        }
        return $this;
    }

    /**
     * Создаёт файл конфигурации с текущими параметрами.
     * 
     * @param string $filename Имя файла конфигурации, если значение `null` используется 
     *    текущий файл конфигурации {@see Serializer::$filename} (по умолчанию `null`).
     * 
     * @return $this
     * 
     * @throws Exception\FormatException Невозможно выполнить сериализацию.
     */
    public function create(string $filename = null)
    {
        if ($filename === null) {
            $filename = $this->filename;
        }
        try {
            $str = "<?php return array(); ?>";
            if (file_put_contents($filename, $str) === false) {
                throw new Exception\FormatException(sprintf('Unable to create configuration file "%s"', $filename));
            }
        } catch (\Exception $e) {
            Gm::error($e->getMessage());
        }
        return $this;
    }
}