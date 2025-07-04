<?php

// Этот файл - основной, он реализует логику и поведение блока.

defined('MOODLE_INTERNAL') || die();  // Если константа MOODLE_INTERNAL не определена, то файл не выполняется (значит, что этот php запущен отдельно от сайта, этого нельзя допускать)

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

    public function get_content() {
        global $OUTPUT, $DB, $USER, $PAGE;

        if ($this->content !== null) {
            return $this->content;
        }

        $systemcontext = context_system::instance(); // Получение контекста
        $roles = get_user_roles($systemcontext, $USER->id); // Получение ролей пользователя по контексту
        $excludedroles = ['manager', 'editingteacher']; // Массив ролей (далее - кто будет получать таблицы)
        $hasexcludedrole = false; // По умолчанию пользователь не имеет тех 2-х ролей

        foreach ($roles as $role) { // Для каждого элемента (роли) в $roles
            $shortname = $DB->get_field('role', 'shortname', ['id' => $role->roleid]); // Получить из таблицы ролей столбец shortname с записью id пользователя (SELECT shortname FROM mdl_role WHERE id = $role->roleid LIMIT 1;)
            if (in_array($shortname, $excludedroles)) { // Если в $shortname есть хоть одна из $excludedroles ...
                $hasexcludedrole = true; // ... то пользователь имеет роль
                break; // Завершить foreach принудительно
            }
        }

        if (!$hasexcludedrole) { // Если у пользователя не обнаружена особая роль
            $this->content = new stdClass(); // Мы наследуем класс от block_base (помним, $this - это обращение к block_olympiads), content - свойство block_base, описывает то, что будет отражено в блоке
            $PAGE->requires->css('/blocks/olympiads/styles.css'); // Запрашиваем css файл

            $olympiads = $DB->get_records('olympiads'); // Получить все олимпиады
            $data = []; // Пустой массив с данными

            $fs = get_file_storage(); // Запись объекта файлового хранилища, который позволяет работать с файлами moodle
            $contextid = context_system::instance()->id; // Получение id контекста

            foreach ($olympiads as $olympiad) {
                $imageurl = '';

                $files = $fs->get_area_files($contextid, 'block_olympiads', 'image', $olympiad->id, 'itemid, filepath, filename', false); // Получение всех файлов из заданной области хранения (контекст, компонент, область хранения, id сущности к которой привязаны файлы, сортировка, включать ли директории?)

                foreach ($files as $file) {
                    if ($file->get_filename() === '.') { // Если имя файла пусто то ... (=== строгое сравнение: 3 == '3' - true, 3 === '3' - false)
                        continue; // ... пропускаем
                    }

                    // Постройка URL к изображению олимпиады
                    // Помним: http://site/pluginfile.php/CONTEXTID/COMPONENT/FILEAREA/ITEMID/FILEPATH/FILENAME
                    $imageurl = moodle_url::make_pluginfile_url(
                        $file->get_contextid(),
                        $file->get_component(),
                        $file->get_filearea(),
                        $file->get_itemid(),
                        $file->get_filepath(),
                        $file->get_filename()
                    )->out();

                    break; // Используем только один файл на олимпиаду
                }

                $data[] = [
                    'name' => $olympiad->name,
                    'image' => $imageurl,
                    'url' => new moodle_url('/blocks/olympiads/view.php', ['id' => $olympiad->id]) // Формирование нового url к странице записи
                ];
            }


            $context = context_system::instance(); // Получение контекста
            $this->content->text = $OUTPUT->render_from_template('block_olympiads/student_view', ['olympiads' => $data]); // Используя шаблон, рендерим карточку, передавая туда массив $data в качестве "olympiads"

            $this->content->footer = '';
            return $this->content;
        }


        // Блок удаления записи из БД
        $deleteid = optional_param('delete', 0, PARAM_INT);
        if ($deleteid) {
            $record = $DB->get_record('olympiads', ['id' => $deleteid, 'created_by' => $USER->id]);
            if ($record) {
                $DB->delete_records('olympiads', ['id' => $deleteid]);
                $DB->delete_records('olympiads_and_students', ['id_olympiad' => $deleteid]);
                
                $returnurl = clone($PAGE->url);
                $returnurl->remove_params(['delete']);
                redirect($returnurl, get_string('deleted'), 1); // Перенаправление по (url, сообщение, задержка)
            }
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        $addurl = new moodle_url('/blocks/olympiads/edit.php');
        $addbutton = html_writer::link(
            $addurl,
            get_string('addolympiad', 'block_olympiads'), // Ещё вариант написания get_string, где второй параметр - имя плагина, где искать перевод
            ['class' => 'btn btn-primary'] // Использование класса, делающий из ссылки кнопку "btn" и преобразующий её в синюю (по умолчанию) "btn-primary"
        );

        $olympiads = $DB->get_records('olympiads', ['created_by' => $USER->id]);

        if (!$olympiads) { // Если пользователь не создавал олимпиады - вывести "Олимпиад нет"
            $this->content->text = $addbutton . html_writer::empty_tag('br') . get_string('nolympiads', 'block_olympiads');
            return $this->content;
        }

        $table = new html_table(); // Генерация таблицы для блока
        $table->head = ['Название', 'Дата начала', 'Дата окончания', 'Участники', 'Действия'];

        foreach ($olympiads as $olympiad) {
            $start = userdate($olympiad->date_start);
            $end = userdate($olympiad->date_end);

            $editurl = new moodle_url('/blocks/olympiads/edit.php', ['id' => $olympiad->id]);
            $deleteurl = new moodle_url($PAGE->url, ['delete' => $olympiad->id]); // Ссылка на текущую страницы с параметром delete

            $editicon = $OUTPUT->action_icon($editurl, new pix_icon('t/edit', get_string('edit'))); // action_icon - иконка-ссылка (url куда перейти, (картинка иконки, выводимый текст при наведении на неё))
            $deleteicon = html_writer::link( // html_writer - класс для генерации HTML
                $deleteurl,
                $OUTPUT->pix_icon('t/delete', get_string('delete')),
                ['onclick' => "return confirm('Вы уверены, что хотите удалить олимпиаду?');"] // Вызов диалога подтверждения при клике
            );

            $viewurl = new moodle_url('/blocks/olympiads/participants.php', ['id' => $olympiad->id]);
            $viewlink = html_writer::link($viewurl, get_string('viewparticipants', 'block_olympiads'), ['class' => 'btn btn-info']); // Формирует "<a href="...">Текст ссылки</a>" (ссылка, текст, HTML атрибуты (в нашем случае - класс кнопки))

            $table->data[] = [$olympiad->name, $start, $end, $viewlink, $editicon . ' ' . $deleteicon];
        }

        $this->content->text = $addbutton . html_writer::empty_tag('br') . html_writer::table($table); // html_writer::table($table) - преобразование в HTML код

        return $this->content;
    }


    public function applicable_formats() {   // Метод указывает на каких страницах можно добавить этот блок
        return [
            'site-index' => true,       // На главной странице — можно
            'course-view' => false,     // На странице курса — нельзя
            'mod' => false,             // На модульных страницах — нельзя
            'my' => true,               // В личном кабинете — можно
        ];
    }
}
