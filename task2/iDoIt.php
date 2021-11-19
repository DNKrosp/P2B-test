<?php

/**
 * @param string $url
 * @return string url reformatted
 */
//удалить параметры со значением “3”; +
//отсортировать параметры по значению; +
//добавит параметр url со значением из переданной ссылки без параметров (в примере: /test/index.html); //путь вынесу в как отдельный параметр+
//сформирует и вернёт валидный URL на корень указанного в ссылке хоста. //удалю путь из домена+
function getBeautifulUrl(string $url): string
{
    //todo: валидация url
    $explodedUrl = parse_url($url);
    //parse query
    $params = getParamsAssoc(explode("&", $explodedUrl["query"]));
    $params = delValuesThree($params);//удаляю ненужные по тз параметры со значением 3
    $params = sortValues($params);//сортирую
    $params["url"] = urlencode($explodedUrl["path"]);//На всякий случай, вдруг что-то плохое положат в url...
    $params = http_build_query($params);

    return  $explodedUrl["scheme"]."://".$explodedUrl["host"]."/".(($params!=="")?"?".$params:"");//можно воспользоваться http_build_url, но уже лень
}

/**
 * @param array $params
 * @return array sorted params
 */
function sortValues(array $params):array
{
    var_dump($params);
    uasort($params, function ($one, $two){
        return $one<=>$two;
    });
    return $params;
}

/**
 * @param array $params
 * @return array delted params with value 3
 */
function delValuesThree(array $params):array
{//можно было по ссылке unset'ать в foreach
    $deleteKeys = [];
    foreach ($params as $param => $value)
    {
        if ($value == 3)
            $deleteKeys[] = $param;
    }
    foreach ($deleteKeys as $key)
        unset($params[$key]);
    return $params;
}

/**
 * @param array $paramsNotExploded
 * @return array
 */
function getParamsAssoc(array $paramsNotExploded):array
{
    $params = [];
    foreach ($paramsNotExploded as $paramNotExploded)
    {
        $explodedByEqually = explode("=", $paramNotExploded)??null;
        if (isset($explodedByEqually))
            $params[$explodedByEqually[0]] = $explodedByEqually[1];
    }
    return $params;
}

$url = getBeautifulUrl("https://www.somehost.com/test/index.html?param1=4&param2=3&param3=2&param4=1&param5=3");
echo $url;//Задание выполнено
