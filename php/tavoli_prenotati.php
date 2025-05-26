<?php
if (isset($_GET["data"]) && isset($_GET["ora"])) {
    $db_conn = pg_connect("host=localhost port=5432 dbname=geppo_pub user=postgres password=password1")
        or die('Connessione fallita: ' . pg_last_error());

    $data = $_GET["data"];
    $ora = $_GET["ora"];

    $query = "SELECT numero_tavolo FROM disponibilita_tavolo
              WHERE data = '$data' AND ora = '$ora' AND prenotato = TRUE";

    $result = pg_query($db_conn, $query) or die('Errore nella query: ' . pg_last_error());

    $tavoli_prenotati = array();
    while ($row = pg_fetch_assoc($result)) {
        $tavoli_prenotati[] = $row["numero_tavolo"];
    }

    header("Content-Type: application/json");
    echo json_encode($tavoli_prenotati);
}
?>
