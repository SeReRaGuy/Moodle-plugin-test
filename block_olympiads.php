<?php

// Этот файл - основной, он реализует логику и поведение блока.

defined('MOODLE_INTERNAL') || die();  // Если константа MOODLE_INTERNAL не определена, то файл не выполняется (значит, что этот php запущен отдельно от сайта, этого нельзя допускать)

// package - к какому плагину относится класс

/**
 * Блок "Олимпиады" — описание для этого блока. Это важный блок.
 *
 * @package   block_olympiads
 * @copyright 2025, Я
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


class block_olympiads extends block_base {   // block_имяПапки (строгий шаблон) наследуется от базового класса для всех блоков
                                             // $this относится к block_olympiads
    public function init() {   // Метод вызывается при инициализации плагина
        $this->title = get_string('pluginname', 'block_olympiads');   // Получает строку pluginname из языкового файла (по пути lang/en/block_olympiads.php) для заголовка блока
    }

    public function get_content() {   // Метод определяет что отобразится внутри блока
        global $OUTPUT;   // Подключение глобальной переменной, которая отвечает за вывод информации (определена в коде moodle, сама работает с HTML и CSS)

        if ($this->content !== null) {   // Если контент не пустой
            return $this->content;   // То получить его - не нужно пересоздавать и выполнять код ниже
        }

        $this->content = new stdClass();   // Пустой класс в PHP, куда добавляются footer, text и прочее - эти элементы определены в API плагина блока, они будут требоваться
        $this->content->footer = '';   // Пустой "футер" наполнения блока
        $this->content->text = get_string('olympiadsblocktext', 'block_olympiads');   // Текст для блока

        return $this->content;   // Возвращение контента (текст блока)
    }

    public function applicable_formats() {   // Метод указывает на каких страницах можно добавить этот блок
        return [
            'site-index' => true,   // На галвной странице можно
            'course-view' => false,   // А вот на просмотре курсов уже нельзя
            'mod' => false,
            'my' => true,
        ];
    }
}
