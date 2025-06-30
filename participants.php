<?php

require('../../config.php');
require_login();

$id = required_param('id', PARAM_INT);
$context = context_system::instance();

$olympiad = $DB->get_record('olympiads', ['id' => $id], '*', MUST_EXIST);

if (!has_capability('moodle/site:config', $context) && !has_capability('moodle/course:update', $context)) { // has_capability (право, контекст) - проверка прав данного пользователя, ...
    print_error('accessdenied', 'admin'); // ... если не может конфигурировать сайт и обновлять курсы, то это не администратор и не учитель, то выдать ошибку "Доступ запрещён"
}

$PAGE->set_url(new moodle_url('/blocks/olympiads/participants.php', ['id' => $id]));
$PAGE->set_context($context);
$PAGE->set_title("Участники олимпиады");
$PAGE->set_heading("Участники олимпиады");

echo $OUTPUT->header();
echo $OUTPUT->heading("Участники олимпиады: " . format_string($olympiad->name));

$students = $DB->get_records_sql(" 
    SELECT u.id, u.firstname, u.lastname, u.email, u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename
    FROM {user} u
    JOIN {olympiads_and_students} oas ON oas.id_student = u.id
    WHERE oas.id_olympiad = ?
", [$id]); // Функция выполняет сырой sql запрос (запрос, подставновка значения за место ?), {} нужны для автоматической подстановки mdl_
            // Выбор полей из таблицы u (сокращение), соединение с таблицей записанных студентов по условию совпадения id пользователя в двух таблицах, где id олимпиады - ?

if ($students) { // Если не пусто
    $table = new html_table();
    $table->head = ['ФИО', 'Email'];
    foreach ($students as $s) {
        $fullname = fullname($s); // fullname() функция moodle, возвращает полное имя пользователя
        $table->data[] = [$fullname, $s->email];
    }
    echo html_writer::table($table);
} else {
    echo $OUTPUT->notification("Нет записанных студентов.");
}

echo $OUTPUT->footer();
