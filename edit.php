<?php

// Страница формы - содержит логику обработки формы и отрисовки страницы
require_once('../../config.php'); // Подключение конфигурационного файла moodle - ядро системы (.. .. - значит подняться на 2 уровня вверх от текущей директории)
require_login(); // Если пользователь не авторизован - редирект на страницу входа

                                            // Записи с $ - переменные в PHP. Переменные с большими буквами - шлобальные переменные самого moodle
$id = optional_param('id', 0, PARAM_INT); // Читает ссылку и выделяет от-туда параметр id, проверяя его на int, если его нет, то id=0
$context = context_system::instance(); // Получает контекст системы, где пользоатель находится в данный момент - для правильной проверки прав доступа, роли и прочего
                                       // С помощью контекста, moodle определяет на каком уровне находится пользователь (вся система, или например ЛК)
$PAGE->set_context($context); // Установка контекста для данной страницы
$PAGE->set_url(new moodle_url('/blocks/olympiads/edit.php', ['id' => $id])); // Записывает текущий URL для moodle
$PAGE->set_title($id ? 'Редактировать олимпиаду' : 'Добавить олимпиаду'); // Если id != 0, то редактировать, если нет, то добавить - установка имени страницы
$PAGE->set_heading('Олимпиады'); // Установка главного заголовка на странице (head)

require_capability('block/olympiads:addinstance', $context); // Проверка права (addinstance) пользователя касаемо добавления новой олимпиады (в установленном контексте!)

require_once($CFG->dirroot . '/blocks/olympiads/classes/form/edit_form.php'); // Подключение непосредственно формы
                                                                              // $CFG->dirroot - абсолютный путь до корня moodle
if ($id) { // Если != 0
    $record = $DB->get_record('olympiads', ['id' => $id], '*', MUST_EXIST); // В БД поступит такой запрос: SELECT * FROM mdl_olympiads WHERE id = $id LIMIT 1;
                                                                            // MUST_EXIST - константа moodle: если запись не найдена - выбросить исключение
} else { // Иначе заполняем переменную полями вручную
    $record = new stdClass();
    $record->name = '';
    $record->description = '';
    $record->date_start = 0;
    $record->date_end = 0;
}

$form = new \block_olympiads\form\edit_form(null, ['id' => $id]); // form - экземпляр edit_form.php, туда передаётся id, место null значит URL для отправки формы, при передаче null используется текущая страница
$form->set_data($record); // Записать данные из $record

if ($form->is_cancelled()) { // Если прожата отмена, возвращение в ЛК
    redirect(new moodle_url('/my/'));
} else if ($data = $form->get_data()) { // Если нет, подтягивание информации из формы
    if ($id) { // Если != 0 - запись редактируется
        $data->id = $id; // Записывается id в запись
        $DB->update_record('olympiads', $data); // Обновление записи в БД (таблица, информация о записи)
    } else { // Если = 0 - запись новая
        $data->date_created = time(); // В запись добавляется поле текущего времени ...
        $data->created_by = $USER->id; // ... а так же id автора олимпиады
        $DB->insert_record('olympiads', $data); // Добавление в БД новой записи
    }
    redirect(new moodle_url('/my/'), 'Сохранено', 2); // Перенаправление (URL, сообщение при редиректе, задержка редиректа)
}

// Генерация  шапки страницы, вывод формы и генерация подвала страницы
echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
