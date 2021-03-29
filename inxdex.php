<?php
namespace LisDev\Delivery;
use mysqli;

require 'nova-poshta-api-2/src/Delivery/NovaPoshtaApi2.php';

set_time_limit(500);
$np = new NovaPoshtaApi2
(
    'bd48f6b0065cc35839b7a4dbbc3c67f3',
    'ru', // Язык возвращаемых данных: ru (default) | ua | en
    TRUE, // При ошибке в запросе выбрасывать Exception: FALSE (default) | TRUE
    'curl' // Используемый механизм запроса: curl (defalut) | file_get_content
);
$all_cities = $np->model('Address')->getCities();
//var_dump($all_cities['data'][0]);

$column_names = array_keys($all_cities['data'][0]);
//var_dump($column_names);
$types = array();
foreach ($all_cities['data'][0] as $elem)
{
    $a_type = gettype($elem);
    if($a_type == "NULL" || $a_type == "string")
    {
        $a_type = "VARCHAR(255)";
    }
    array_push($types, $a_type);
}
$names_and_types = array();

for ($i = 0; $i < count($column_names); $i++)
{
    array_push($names_and_types, $column_names[$i]." ".$types[$i]);
}
//var_dump($names_and_types);
$names_and_types = implode(",", $names_and_types);

$create_table = "CREATE TABLE IF NOT EXISTS cities(
    ID INTEGER not null primary key auto_increment,
".$names_and_types.");";
$servername = "localhost";
$username = "root";
$password = "";
$db_name = "cities";
// Create connection
$conn = new mysqli($servername, $username, $password, $db_name);

// Check connection
if ($conn->connect_error)
{
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
//$conn->query($create_table);
//var_dump($conn->error);

function past_all_data($all_cities, $column_names, $connection)
{

    foreach ($all_cities['data'] as $city)
    {
        $insert_cities = "INSERT INTO cities (".$column_names.") VALUES (";
        $str_data = "";
        $vals = array_values($city);
        foreach ($vals as $val)
        {
            $val = mysqli_real_escape_string($connection, $val);
            $mon_val = "'".$val."'";
            $str_data .=$mon_val.",";
        }
        $insert_cities .=$str_data;
        $insert_cities = rtrim($insert_cities, ",");
        $insert_cities .= ");";
        //echo('<pre>'.$insert_cities.'</pre>');
        usleep(1000000);
        //$insert_cities = mysqli_real_escape_string($connection, $insert_cities);
        $connection->query($insert_cities);
        var_dump($connection->error);
    }

}
past_all_data($all_cities, implode(",",$column_names), $conn);
?>