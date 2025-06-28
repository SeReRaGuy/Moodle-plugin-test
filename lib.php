<?php

// Файл, содержащий функции плагина, которые moodle сможет вызывать сам (если функции названы по шаблону)

// В moodle файлы хранятся в moodledata/filedir с именем contenthash, информация о файле заносится в mdl_files.
// Доступ к изображению получается через: http://site/pluginfile.php/CONTEXTID/COMPONENT/FILEAREA/ITEMID/FILEPATH/FILENAME
// Например:                              http://localhost:8080/pluginfile.php/1/block_olympiads/image/7/изображение_2025-06-27_093835712.png
// В этом файле есть функция block_olympiads_pluginfile() - она шаблонна, вызывается pluginfile-ом тогда, когда нужно подгрузить изображение (когда в block_olympiads.php вызывается moodle_url::make_pluginfile_url() )

defined('MOODLE_INTERNAL') || die();

function block_olympiads_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    // Эта функция должна вернуть нужный файл по входным параметрам
    // $course - информация о курсе, в нашем случае null
    // $cm - модули курса
    // $context - контекст, в котором запрашивается файл (файл в курсе, в модуле, в системе), $context в block_olympiads.php содержит его
    // $filearea - файловая область искомого файла
    // $args - остаток пути к файлу, включает itemid, filepath, filename
    // $forcedownload - сохранить ли файл на компьютер
    // array $options = [] - доп. настройки передачи файлов


    if ($context->contextlevel !== CONTEXT_SYSTEM || $filearea !== 'image') { // ... или не изображение (!== учитывает типы (3!='3' false, 3!=='3' true))
        // $context - где в moodle происходит операция, contextlevel в нём определяет, к какому уровню привязан объект в иерархии moodle
        // CONTEXT_SYSTEM - контекст системы, проверяется, чтобы кто-то не запросил файлы курса или пользователя
        return false; // то закрыть доступ к файлу
    }

    $itemid = array_shift($args);       // Удаляет первый элемент массива $args и присваивает его $itemid
    $filename = array_pop($args);       // Удаляет последний элемент массива $args и присваивает его $itemid
    $filepath = '/';
    if (!empty($args)) { // Если $args не пустой (предполагаетсмя, что есть поддиректории)
        $filepath .= implode('/', $args) . '/'; // Склеивает все поддиректории в один путь (.= это = '/' .)
    }

    $fs = get_file_storage(); //$fs становится объектом файлового хранилища
    $file = $fs->get_file($context->id, 'block_olympiads', 'image', $itemid, $filepath, $filename); // Непосредственный поиск по mdl_files по предоставленным полям

    if (!$file || $file->is_directory()) { // Если файл не найден (пуст) ИЛИ запрашиваемый файл - директория (папка)
        return false; // Не отдавать
    }

    send_stored_file($file, 0, 0, $forcedownload, $options); // Чтение файла из filedir и его отправка браузеру (файл, кешировать?, фильтровать контент?, ...)
}
