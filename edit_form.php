<?php

// Настройка формы редактирования конкретного экземпляра блока олимпиады

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/edit_form.php');   // $CFG->dirroot - глобальная переменная, содержащая абсолютный путь до корневой директории Moodle
                                                         // после . и соединение с остальной частью дадут "/var/www/html/blocks/edit_form.php"
                                                         // require_once гарантирует, что файл будет подключён один раз, и не вызовет ошибок при повторном подключении.
                                                         // ??? НЕ ПОНЯЛ ОБЩУЮ ЛОГИКУ ЗАЧЕМ
/**
 * Форма редактирования блока олимпиады.
 *
 * @package   block_olympiads
 */
class block_olympiads_edit_form extends block_edit_form {   // block_olympiads_edit_form - строгое название
    protected function specific_definition($mform) {   // Переопределение метода для определения уникальных для блока полей, вызывается при редактировании блока
                                                       // $mform - объект класса MoodleQuickForm, представляет методы построения форм
        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block')); // Добавление элемента - тип, имя (config_имя - обязательно), наполнение, прочие HTML параметры
                                                                                             // 'block' - обращение к языковому пакету самого moodle
        $mform->addElement('text', 'config_text', get_string('blockstring', 'block_olympiads'));
        $mform->setDefault('config_text', get_string('blockstringdefault', 'block_olympiads')); // !!! СДЕЛАТЬ EN Установка значения по умолчанию для поля
        $mform->setType('config_text', PARAM_TEXT);  // Тип данных для поля
    }
}
