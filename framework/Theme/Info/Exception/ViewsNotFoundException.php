<?php
/**
 * Этот файл является частью пакета GM Framework.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Theme\Info\Exception;

use Throwable;
use Gm\Exception\NotFoundException;

/**
 * Исключение возникающие при отсутствии файла шаблона.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Theme\Info\Exception
 * @since 2.0
 */
class ViewsNotFoundException extends NotFoundException
{
    /**
     * Имя файла шаблона.
     * 
     * @var string
     */
    public string $filename = '';

    /**
     * {@inheritdoc}
     * 
     * @param string $filename Имя файла шаблона.
     */
    public function __construct(string $filename = '', string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->filename = $filename;

        parent::__construct(200, $message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatchMessage(): string
    {
        return sprintf('Views description file "%s" not found', $this->filename ?: 'unknow');
    }
}
