<?php
// Controlliamo che siano stati inviati i campi essenziali dal form
if (isset($_POST["nome"]) && isset($_POST["email"]) && isset($_POST["messaggio"])) {

    // Connessione al database
    $db_conn = pg_connect("host=localhost port=5432 dbname=geppo_pub user=postgres password=Fulvietto1964.")
        or die("Connessione al database fallita: " . pg_last_error());

    // Recupero dei dati dal form
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $telefono = isset($_POST["telefono"]) ? $_POST["telefono"] : '';
    $messaggio = $_POST["messaggio"];

    // Validazione lato server
    if (strlen($nome) < 2 || strlen($nome) > 50) {
        die("nome non valido");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("email non valida");
    }

    if (strlen($messaggio) < 5) {
        die("messaggio troppo corto");
    }

    // Inserimento nel database
    $query = "INSERT INTO feedback (nome, email, telefono, messaggio)
              VALUES ('$nome', '$email', '$telefono', '$messaggio')";
    $result = pg_query($db_conn, $query);
    if (!$result) {
        die("Errore nell'inserimento feedback: " . pg_last_error());
    }

    echo "success";
    
} else {
    echo "dati incompleti";
}
?>
