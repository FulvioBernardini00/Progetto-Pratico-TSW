<?php
if (isset($_POST["tav_num"])) {
    $db_conn = pg_connect("host=localhost port=5432 dbname=geppo_pub user=postgres password=password1")
        or die('Could not connect: ' . pg_last_error());

    // recupero dati form
    $numero_tavolo = (int) trim($_POST["tav_num"]);
    $data = $_POST["data"];
    $ora = $_POST["ora"];


    // pulisco stringhe per evitare problemi nel DB
    $nome = pg_escape_string($db_conn, $_POST["nome"]);
    $cognome = pg_escape_string($db_conn, $_POST["cognome"]);
    $telefono = pg_escape_string($db_conn, $_POST["telefono"]);
    $email = pg_escape_string($db_conn, $_POST["email"]);


    // controllo disponibilità tavolo
    $query1 = "SELECT id FROM disponibilita_tavolo WHERE numero_tavolo = $numero_tavolo AND data = '$data' AND ora = '$ora'";
    $result = pg_query($db_conn, $query1) or die("Errore ricerca disponibilità: " . pg_last_error());
    $row = pg_fetch_assoc($result);

    if (!$row) {
        die("Disponibilità non trovata per tavolo $numero_tavolo alle $ora del $data.");
    }

    $id_disponibilita = $row['id'];


    // inserisco dati prenotazione
    $query2 = "INSERT INTO prenotazione(nome, cognome, telefono, email, id_disponibilita, numero_tavolo)
               VALUES ('$nome', '$cognome', '$telefono', '$email', $id_disponibilita, $numero_tavolo)";
    $result2 = pg_query($db_conn, $query2);
    if (!$result2) {
        die("Errore nella INSERT prenotazione: " . pg_last_error());
    }

    //  Aggiorna la disponibilità 
    $query3 = "UPDATE disponibilita_tavolo SET prenotato = TRUE WHERE id = $id_disponibilita";
    $result3 = pg_query($db_conn, $query3);
    if (!$result3) {
        die("Errore nell'UPDATE disponibilità: " . pg_last_error());
    }

    echo "ok";
}
?>
