<?php

require_once('../../config.php');
require_login(); // Авторизован ли пользователь? Если нет - на страницу входа

$id = required_param('id', PARAM_INT); // Получение обязательного параметра типа int с именем id из url
$action = optional_param('action', '', PARAM_ALPHA); // Получение необязательного параметра с именем action из url, где он должен иметь только латинские буквы, если такой параметр не найден - то пустота ('')

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/blocks/olympiads/view.php', ['id' => $id]));
$PAGE->set_title('Олимпиада');
$PAGE->set_heading('Олимпиада');

global $DB, $USER, $OUTPUT;

$olympiad = $DB->get_record('olympiads', ['id' => $id], '*', MUST_EXIST); // ??? Напомнить парамиетры функции

$fs = get_file_storage();
$files = $fs->get_area_files($context->id, 'block_olympiads', 'image', $id, 'itemid, filepath, filename', false);
$imageurl = '';
foreach ($files as $file) {
    if ($file->get_filename() !== '.') {
        $imageurl = moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename()
        );
        break;
    }
}

// Проверка роли пользователя (запрещаем для teacher и manager)
$roles = get_user_roles($context, $USER->id);
$excludedroles = ['manager', 'editingteacher'];
$can_enroll = true;
foreach ($roles as $role) {
    $shortname = $DB->get_field('role', 'shortname', ['id' => $role->roleid]); // Чтение ролей по id, поиск исключающей роли
    if (in_array($shortname, $excludedroles)) {
        $can_enroll = false;
        break;
    }
}

$enrolled = $DB->record_exists('olympiads_and_students', [ // Существует ли запись студента на эту олимпиаду
    'id_olympiad' => $id,
    'id_student' => $USER->id
]);

if ($action === 'enroll' && $can_enroll && !$enrolled) { // Если страница обновилась с подпиской и может быть записан, и не записан
    $DB->insert_record('olympiads_and_students', [
        'id_olympiad' => $id,
        'id_student' => $USER->id,
        'date_record' => time()
    ]);
    redirect(new moodle_url('/blocks/olympiads/view.php', ['id' => $id]), 'Вы успешно записались', 1);
}

if ($action === 'unenroll' && $can_enroll && $enrolled) { // Если страница обновилась с отпиской и может быть записан, и записан
    $DB->delete_records('olympiads_and_students', [ // Удалить информацию о записи
        'id_olympiad' => $id,
        'id_student' => $USER->id
    ]);
    redirect(new moodle_url('/blocks/olympiads/view.php', ['id' => $id]), 'Вы отписались от олимпиады', 1); // Обновить страницу с сообщением
}

echo $OUTPUT->header(); // echo - вывод информации на страницу

echo html_writer::start_div('olympiad-view-container', ['style' => 'display: flex; gap: 20px;']);

if ($imageurl) { // Если изображение олимпиады есть, обрабатывать его вставку на страницу
    echo html_writer::div(
        html_writer::empty_tag('img', ['src' => $imageurl, 'alt' => 'Изображение олимпиады', 'style' => 'max-width: 300px; height: auto;']),
        '',
        ['style' => 'flex: 1']
    );
}

// Отсальные элементы
echo html_writer::start_div('', ['style' => 'flex: 2']);
echo html_writer::tag('h2', $olympiad->name);
echo html_writer::tag('p', 'Дата начала: ' . userdate($olympiad->date_start));
echo html_writer::tag('p', 'Дата окончания: ' . userdate($olympiad->date_end));
echo html_writer::tag('div', format_text($olympiad->description), ['style' => 'margin-bottom: 20px;']);

if ($can_enroll) { // Если может записаться
    if ($enrolled) { // Если записан
        $unenrollurl = new moodle_url('/blocks/olympiads/view.php', ['id' => $id, 'action' => 'unenroll']); // Формирование ссылки с отпиской
        echo html_writer::link($unenrollurl, 'Отписаться', ['class' => 'btn btn-danger']);
    } else { // Если не записан
        $enrollurl = new moodle_url('/blocks/olympiads/view.php', ['id' => $id, 'action' => 'enroll']); // Формирование ссылки с подпиской
        echo html_writer::link($enrollurl, 'Записаться', ['class' => 'btn btn-primary']);
    }
} else { // Если роль не может записаться (вывод сообщения)
    echo html_writer::div('Вы не можете записываться на олимпиады.', 'alert alert-warning');
}

echo html_writer::end_div();
echo html_writer::end_div();

echo $OUTPUT->footer();
