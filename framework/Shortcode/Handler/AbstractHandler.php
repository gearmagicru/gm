<?php
/**
 * Этот файл является частью пакета GM Framework.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Shortcode\Handler;

use Gm\Shortcode\ShortcodeManager;

/**
 * Абстрактный класс обработчика шорткодов.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Shortcode\Handler
 * @since 2.0
 */
class AbstractHandler
{
    /**
     * Указатель на экземпляр текущего класса.
     * 
     * @see AbstractHandler::factory()
     * 
     * @var AbstractHandler
     */
    protected static AbstractHandler $instance;

    /**
     * Имена шорткодов и компонентов (модулей, расширений модулей) реализующих их 
     * работу.
     * 
     * Например: `['html-title' => 'gm.fe.site', 'html-meta'  => 'gm.fe.site', ...]`.
     *
     * @var array<string, string>
     */
    protected array $shortcodes = [];

    /**
     * Обработчик шорткодов.
     * 
     * @see AbstractHandler::createProcessor()
     *
     * @var mixed
     */
    protected mixed $processor = null;

    /**
     * Менеджер шорткодов.
     *
     * @var ShortcodeManager
     */
    protected ShortcodeManager $manager;

    /**
     * Конструктор класса
     * 
     * @param ShortcodeManager $manager Менеджер шорткодов.
     * @param array $shortcodes Шорткоды.
     * 
     * @return void
     */
    public function __construct(ShortcodeManager $manager, array $shortcodes)
    {
        $this->shortcodes = $shortcodes;
        $this->manager = $manager;
        $this->createProcessor();
        $this->registerShortcodes($shortcodes);
    }

    /**
     * Создаёт обработчик шорткодов.
     * 
     * @param ShortcodeManager $manager Менеджер шорткодов.
     * @param array $shortcodes Шорткоды.
     * 
     * @return $this
     */
    public static function factory(ShortcodeManager $manager, array $shortcodes): static
    {
        if (!isset(static::$instance)) {
            static::$instance = new static($manager, $shortcodes);
        }
        return static::$instance;
    }

    /**
     * Создание процессор шорткодов.
     * 
     * @return $this
     */
    protected function createProcessor(): static
    {
        return $this;
    }

    /**
     * Регистрирует шорткоды по умолчанию.
     * 
     * @return $this
     */
    protected function defaultShortcodes(): static
    {
        return $this;
    }

    /**
     * Регистрирует шорткоды.
     * 
     * @param array $shortcodes Шорткоды.
     * 
     * @return $this
     */
    public function registerShortcodes(array $shortcodes): static
    {
        return $this;
    }

    /**
     * Выполняет обработку шорткодов в тексте.
     * 
     * @param string $content Текст с шорткодами.
     * 
     * @return string
     */
    public function process(string $text): string
    {
        return $text;
    }
}
