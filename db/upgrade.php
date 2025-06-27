<?php

function xmldb_block_olympiads_upgrade($oldversion): bool { // Тут $oldversion подтягивается из moodle - это текущая версия плагина
    global $DB;

    $dbman = $DB->get_manager(); // Менеджер для работы с структурой БД

    if ($oldversion < 2025062606) { // Если текущая версия старая

        $table = new xmldb_table('olympiads');
        $field = new xmldb_field('image'); // (имя поля, тип, длина,(для int - кол-во знаков после запятой), null, автоинкремент, значение  по умолчанию)

        /* Блок кода, добавляющий это поле
        if (!$dbman->field_exists($table, $field)) { // Если такого поля нет
            $dbman->add_field($table, $field); // Добавляем его
        }*/

        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field); // Удаление поля из таблицы
        }

        upgrade_block_savepoint(true, 2025062606, 'olympiads'); // Для какой версии было это обновление (успешно ли, номер новой версии, имя плагина)
    }

    return true;
}
