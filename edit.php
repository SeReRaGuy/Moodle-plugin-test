<?php

// Страница формы — содержит логику обработки формы и отрисовки страницы

require_once('../../config.php'); // Подключение конфигурационного файла moodle — ядро системы (.. .. — подняться на 2 уровня выше)
require_login(); // Если пользователь не авторизован — редирект на страницу входа

$id = optional_param('id', 0, PARAM_INT); // Получение параметра id из URL (если не задан — 0)
$context = context_system::instance(); // Контекст системы для проверки прав
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/blocks/olympiads/edit.php', ['id' => $id]));
$PAGE->set_title($id ? 'Редактировать олимпиаду' : 'Добавить олимпиаду');
$PAGE->set_heading('Олимпиады');

require_capability('block/olympiads:addinstance', $context); // Проверка прав на добавление/редактирование
require_once($CFG->dirroot . '/blocks/olympiads/classes/form/edit_form.php'); // Подключение формы

if ($id) { // Если запись существует
    $record = $DB->get_record('olympiads', ['id' => $id], '*', MUST_EXIST); // Получение записи из БД
} else {
    $record = new stdClass();
    $record->name = '';
    $record->description = '';
    $record->date_start = 0;
    $record->date_end = 0;
}

$filedraftid = file_get_submitted_draft_itemid('image_file'); // Получение ID файлового черновика для поля формы "image_file" (создание черновика)
file_prepare_draft_area( // Инициализация временной формы (именно тут и подтягивается ранее сохранённое изображение)
    $filedraftid,              // ID черновика
    $context->id,              // Контекст
    'block_olympiads',         // Компонент (имя плагина)
    'image',                   // filearea (можно назвать как угодно, но одинаково везде)
    $id,                       // ID олимпиады (0, если новая)
    ['subdirs' => 0, 'maxfiles' => 1, 'maxbytes' => 0] // Параметры (разрешены ли вложенные папки?, максимально файлов, максимальный вес)
);
$record->image_file = $filedraftid; // Тепеь запись содержит черновик

$form = new \block_olympiads\form\edit_form(null, ['id' => $id]); // Создание экземпляра edit_form (url для отправки формы null - на этой же странице, передача id в экземпляр)
$form->set_data($record); // Установка значений в форму

if ($form->is_cancelled()) {
    redirect(new moodle_url('/my/')); // Отмена — возврат в ЛК
} else if ($data = $form->get_data()) {
    $draftitemid = file_get_submitted_draft_itemid('image_file'); // Получение изображения, которое прикреплено к записи

    if ($id) {
        $DB->update_record('olympiads', $data); // Обновление записи
    } else {
        $data->date_created = time();
        $data->created_by = $USER->id;
        $id = $DB->insert_record('olympiads', $data); // Запись создаётся — $id потребуется для загрузки файла
    }

    file_save_draft_area_files( // Сохранение файлов из черновика в основное хранилище
        $draftitemid, // ID черновика
        $context->id,
        'block_olympiads', // Привязка по плагину
        'image',
        $id, // Привязка по id записи
        ['subdirs' => 0, 'maxfiles' => 1, 'maxbytes' => 0]
    );

    redirect(new moodle_url('/my/'), 'Сохранено', 2);
}

// Вывод формы и страницы
echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
