# Масштабирование и сжатие картинок на PHP

## PHP resize_image()
Функция для масштабирования картинок на PHP так же как в программах обработки изображений. Взял пример с программ ACDSee и FastStone Image Viewer.


## Для каких задач?
- Вы хотите, чтобы Google Pagespeed Insights пропускал Ваши изображения и давал сайту побольше баллов
- Вы делаете Интернет-магазины и не хотите загружать 10 разных размеров изображения на сайт
- Вы делаете адаптивные сайты и понимаете, что картинку нельзя делать автоматически масштабируемой с помощью max-width: 100%; из-за Retina (iPad) дисплеев и скорости работы сайта


## Как работает?
Очень просто - подключили файл, вызвали функцию с параметрами, получили путь в файловой системе к сохраненному изображению. Путь к файлу преобразовали в относительную ссылку, вывели в верстке.


## Подготовка к работе
Сначала мы задаем основную директорию MAIN_DIR - относительно нее будет создано изображение no-image.jpg и папка, в которую будут сохранятся временные изображения.
```
defined('MAIN_DIR') or define('MAIN_DIR', dirname(__FILE__));
resize_image($path = '', $width = 0, $height = 0, $mode = 'contain', $oversize = 0);
```


## Режимы работы
1. **'oneside'** -- Изменить размер по одной из сторон
2. **'hard'** -- Жестко изменить размер изображения, не учитывая соотношение сторон
3. **'contain'** -- Поместить все изображение в заданные размеры с сохранением соотношения сторон
4. **'cover'** -- Заполнить заданные размеры изображением, даже если часть изображения не поместиться
5. **'fill'** -- Вместить изображение в указанные размеры, и дополнить белым цветом до указанных размеров 

> Дополнительный параметр **$oversize** принимает 1 или 0 и запрещает или разрешает растягивать изображения больше начальной ширины. Если флаг стоит в 0, изображения у которых ширина ИЛИ высота требуемые больше заданных вообще не будут преобразовываться - вы получите ту же ссылку, что и передали в функцию.


## Пример кода
```
<?php
  defined('MAIN_DIR') or define('MAIN_DIR', dirname(__FILE__)); // задаем директорию, относительно которой скрипт сохраняет изображения
  require('resize_image.php'); // подключаем файл
  
  $dir = dirname(__FILE__); // запоминаем путь к папке, где лежит изображение
  $imgpath = $dir . '/1.jpg'; // указываем путь к изображению
  
  $path = resize_image($imgpath, 500, 150, 'contain'); // получаем изображение в папке "{$dir}/temp/img/xxx_xxx/image_{$hash}"
  // переменной $path уже можно пользоваться ИЛИ далее
  
  $src = str_replace($dir . DIRECTORY_SEPARATOR, '', $path); // заменяем путь в файловой системе на относительную ссылку для верстки
?>
```


## Выводим в верстке измененное изображение
```
<img src="<?=$src;?>">
<div style="background-image: url(<?=$src;?>);"></div>
```
