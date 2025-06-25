<?php

// Создание формы, добавление её элементов

namespace block_olympiads\form; // namespace - пространство имён, указывает к какой группе принадлежит класс (moodle может найти этот класс), а так же отделяет классы с одинаковыми именами

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php'); // Подключение файла, что поможет формировать форму

use moodleform; // Используется класс, от которого будет наследолвание

class edit_form extends moodleform {
    public function definition() {
        $mform = $this->_form; // Вместо того, чтобы везде писать "$this->_form->addElement(...)", _form - объект формы

        $mform->addElement('hidden', 'id'); // Добавление поля id, скрытого на форме. Нужно для передачи в $data (edit.php) полной записи
        $mform->setType('id', PARAM_INT);

        $mform->addElement('header', 'olympiadheader', 'Добавление олимпиады'); // Добавление элемента в форму (тип поля, имя поля, подпись поля, [доп. опции])

        $mform->addElement('text', 'name', 'Название');
        $mform->setType('name', PARAM_TEXT); // Для поля name устанавливается ограничение типа "текст"
        $mform->addRule('name', 'Введите название олимпиады', 'required', null, 'client'); // Валидация поля (имя поля, сообщение при ошибке, правила (обязательное поле, например), доп. аргумент для правила (при maxlength было бы не null, а 50 например) валидация (на какой стороне проверяется))

        $mform->addElement('textarea', 'description', 'Описание');
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement('date_selector', 'date_start', 'Дата начала');
        $mform->addElement('date_selector', 'date_end', 'Дата окончания');

        $this->add_action_buttons(true, 'Сохранить'); // Добавить кнопку сохртанения (добавлять ли кнопку отмены?, подпись для кнопки сохранения)
    }
}
