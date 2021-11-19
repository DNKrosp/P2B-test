<?php

function load_users_data($user_ids)
{
    $user_ids = explode(',', $user_ids);
    foreach ($user_ids as $user_id) {
        $db = mysqli_connect("localhost", "root", "123123", "database");
        $sql = mysqli_query($db, "SELECT * FROM users WHERE id=$user_id");
        //явная sql инъекция, которой можно вызвать хранимую xss, позволяющую впоследствии злоумышленнику получить куки админа
        //а дальше уже доступ ко всему) иб-специалисту больно.
        while ($obj = $sql->fetch_object()) {
            $data[$user_id] = $obj->name;
        }
        mysqli_close($db);
    }
    return $data;
}

/**
 * !!!LEGACY!!!
 * Get links to users by IDs
 * @link https://legacy.doc/manual/en/function.load_users_data_my_variant
 * @param array $userIds <p>
 *   user ids...
 * </p>
 * @return array <p>
 *   assoc array where KEY is (int) ID, VALUE is (string) NAME of user
 * </p>
 * @throws Exception <p>
 *   code 8 is user_id is not int
 *   or read logs...
 * </p>
 */
function load_users_data_my_variant(array $userIds):array//тут также можно использовать ооп вариант mysqli_fetch_all, mysqli_close
{
    $result = [];
    $userIds = explode(',', $userIds);
    $countUserIds = count($userIds);
    if ($countUserIds==0)
        return [];
    $sql = "SELECT id, name FROM users WHERE id IN id in ".buildIn($countUserIds);//помимо id и name больше инфы не нужно
    //мне не нравится, что подключение к базе происходет в коде, но также я бы обернул еще и методы получения данных,
    // а лучше бы в обертке сдела pdo, чтобы было все равно откуда черпать данные (если такое потребуется в будущем)
    $mysqli = database/ConnectionSingleton->getMariaDbConnection();
    try {
        //не понял зачем foreach, если можно все в одном запросе сделать, думаю, это чем-то похоже на пробему n+1 у orm систем)
        $stmt = $mysqli->prepare($sql);

        $isBinded = $stmt->bind_param('i', ...$userIds);
        if (!$isBinded)
            return throw new Exception("user_id-is-not-int", 8);

        $stmt->execute();

        $records = mysqli_fetch_all($stmt);

        foreach ($records as $record)
            $result[$record["id"]] = $record["name"];
        return $result;//Выглядит монстром, но хоть так legacy не сломают черные капюшоны
    } catch (Exception $e) {
        file_put_contents(__LOGS_LEGACY_DIR__."/logs", $e->__toString());//сделать бы нормальное логирование на более высокой абстракции...
    } finally {
        mysqli_close($mysqli);//эх, автоматизировать бы закрытие в обертке...
    }
    return $result;
}

function buildIn(int $count): string
{
    return "(".implode(",", array_fill(0, $count, "?")).")";
}

//типа legacy прод
$data = load_users_data($_GET['user_ids']);//а где валидация user_ids...
foreach ($data as $user_id=>$name) {
    echo "<a href=\"/show_user.php?id=$user_id\">$name</a>";//а если в базу попали плохие данные...
}