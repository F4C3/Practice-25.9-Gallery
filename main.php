<?php

define('URL', 'main.php'); // URL текущей страницы
define('UPLOAD_MAX_SIZE', 2097152); // 2mb
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('UPLOAD_DIR', 'images');

$errors = [];

if (!empty($_FILES)) {

    for ($i = 0; $i < count($_FILES['files']['name']); $i++) {

        $fileName = $_FILES['files']['name'][$i];

        if ($_FILES['files']['size'][$i] > UPLOAD_MAX_SIZE) {
            $errors[] = 'Недопостимый размер файла ' . $fileName;
            continue;
        }

        if (!in_array($_FILES['files']['type'][$i], ALLOWED_TYPES)) {
            $errors[] = 'Недопустимый формат файла ' . $fileName;
            continue;
        }

        $filePath = UPLOAD_DIR . '/' . basename($fileName);
        //Пытаемся загрузить файл, в случае ошибки переход к следующей итерации
        if (!move_uploaded_file($_FILES['files']['tmp_name'][$i], $filePath)) {
            $errors[] = 'Ошибка загрузки файла ' . $fileName;
            continue;
        }

        $pdo= new PDO(dsn:"mysql:host=localhost;dbname=example01", username:"root", password:""); //PDO - Представляет соединение между PHP и сервером базы данных
        $sql = "INSERT INTO posts (image) VALUES (:image)"; //Запись в базу данных. Параметрыв VALUE соответствуют названиям в html разметке
        $statement = $pdo->prepare($sql); //prepare - Подготавливает запрос к выполнению и возвращает связанный с этим запросом объект
        $statement->bindParam(":image", $fileName);//bindParam - Привязывает параметр запроса к переменной
        $statement->execute(); //execute - Запускает подготовленный запрос на выполнение

    
    }

}

function getPosts()
{

    $pdo= new PDO(dsn:"mysql:host=localhost;dbname=example01", username:"root", password:""); 
    $statement = $pdo->prepare("SELECT * FROM posts");
    $statement->execute();
    $posts = $statement->fetchALL(PDO::FETCH_ASSOC);
    return $posts;
}  

$posts = getPosts();


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
          integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <title>Загрузка файлов</title>
</head>
<body>

<div class="container pt-4">
    <h1 class="mb-4">Загрузка файлов</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($_FILES) && empty($errors)): ?>
        <div class="alert alert-success">Файлы успешно загружены</div>
    <?php endif; ?>

    <form action="<?php echo URL; ?>" method="post" enctype="multipart/form-data">
        <div class="custom-file">
            <input type="file" class="custom-file-input" name="files[]" id="customFile" multiple required>
            <label class="custom-file-label" for="customFile" data-browse="Выбрать">Выберите файлы</label>
            <small class="form-text text-muted">
                Максимальный размер файла: <?php echo UPLOAD_MAX_SIZE / 2097152; ?>Мб.
                Допустимые форматы: <?php echo implode(', ', ALLOWED_TYPES) ?>.
            </small>
        </div>
        <hr>
        <button type="submit" class="btn btn-primary" name="add">Загрузить</button>
        <a href="<?php echo URL; ?>" class="btn btn-secondary ml-3">Сброс</a>
        <a href="logout.php" class="btn btn-secondary ml-3">Выйти</a>
    </form>
    <br>

    <? if ($posts == null): ?>
    <p>Пока что ничего нет</p>
    <? else: ?>
    <?php foreach($posts as $post):?>
    <img src="images/<?=$post['image'];?>" width="500">
    <?php endforeach ?>

    <? endif ?>
</div>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"
        integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"
        integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input@1.3.4/dist/bs-custom-file-input.min.js"></script>
<script>
    $(() => {
        bsCustomFileInput.init();
    })
</script>
</body>
</html>

<?php

