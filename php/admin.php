<?php
session_start();
$conn = pg_connect("host=localhost port=5432 dbname=geppo_pub user=postgres password=password1");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $user = $_POST['username'];
        $pass = $_POST['password'];
        if ($user === 'admin' && $pass === '1234') {
            $_SESSION['admin'] = true;
        } else {
            $errore = "Credenziali errate.";
        }
    } elseif (isset($_SESSION['admin']) && isset($_POST['elimina'])) {
        $id = (int) $_POST['elimina'];

        $queryInfo = "SELECT id_disponibilita FROM prenotazione WHERE id = $id";
        $resultInfo = pg_query($conn, $queryInfo);
        if ($row = pg_fetch_assoc($resultInfo)) {
            $id_disp = $row['id_disponibilita'];
            pg_query($conn, "UPDATE disponibilita_tavolo SET prenotato = FALSE WHERE id = $id_disp");
        }

        pg_query($conn, "DELETE FROM prenotazione WHERE id = $id");
    } elseif (isset($_SESSION['admin']) && isset($_POST['modifica'])) {
        $id = (int) $_POST['id'];
        $nome = pg_escape_string($conn, $_POST['nome']);
        $cognome = pg_escape_string($conn, $_POST['cognome']);
        $telefono = pg_escape_string($conn, $_POST['telefono']);
        $email = pg_escape_string($conn, $_POST['email']);
        pg_query($conn, "UPDATE prenotazione SET nome='$nome', cognome='$cognome', telefono='$telefono', email='$email' WHERE id=$id");
    } elseif (isset($_SESSION['admin']) && isset($_POST['aggiungi'])) {
        $nome = pg_escape_string($conn, $_POST['nome']);
        $cognome = pg_escape_string($conn, $_POST['cognome']);
        $telefono = pg_escape_string($conn, $_POST['telefono']);
        $email = pg_escape_string($conn, $_POST['email']);
        $data = $_POST['data'];
        $ora = $_POST['ora'];
        $tavolo = (int) $_POST['tavolo'];

        $q = "SELECT id FROM disponibilita_tavolo WHERE numero_tavolo = $tavolo AND data = '$data' AND ora = '$ora'";
        $res = pg_query($conn, $q);
        $r = pg_fetch_assoc($res);

        if (!$r) {
            $insert_disp = "INSERT INTO disponibilita_tavolo (numero_tavolo, data, ora, prenotato) 
                            VALUES ($tavolo, '$data', '$ora', TRUE) RETURNING id";
            $res = pg_query($conn, $insert_disp);
            $r = pg_fetch_assoc($res);
        }

        if ($r) {
            $id_disp = $r['id'];
            pg_query($conn, "INSERT INTO prenotazione(nome, cognome, telefono, email, id_disponibilita, numero_tavolo)
                            VALUES ('$nome', '$cognome', '$telefono', '$email', $id_disp, $tavolo)");
            pg_query($conn, "UPDATE disponibilita_tavolo SET prenotato = TRUE WHERE id = $id_disp");
        } else {
            $errore = "Errore durante la creazione della disponibilità.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Gestione Prenotazioni</title>
  <link rel="stylesheet" href="../css/admin.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<?php if (!isset($_SESSION['admin'])): ?>
  <div class="container rounded p-5 shadow">
    <h2 class="text-center mb-4 testo">Login Admin</h2>
    <?php if (isset($errore)) echo "<p class='text-danger'>$errore</p>"; ?>
    <form method="POST">
      <input type="text" name="username" class="form-control mb-3" placeholder="Username">
      <input type="password" name="password" class="form-control mb-3" placeholder="Password">
      <button type="submit" name="login" class="btn btn-warning w-100">Login</button>
    </form>
  </div>
<?php else: ?>
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="testo">Gestione Prenotazioni</h2>
      <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <h4>Aggiungi nuova prenotazione</h4>
    <form method="POST" class="row g-3 mb-4">
      <input type="hidden" name="aggiungi" value="1">
      <div class="col-md-3"><input type="text" name="nome" class="form-control" placeholder="Nome" required></div>
      <div class="col-md-3"><input type="text" name="cognome" class="form-control" placeholder="Cognome" required></div>
      <div class="col-md-2"><input type="text" name="telefono" class="form-control" placeholder="Telefono" required></div>
      <div class="col-md-2"><input type="email" name="email" class="form-control" placeholder="Email"></div>

      <div class="col-md-2">
        <select name="data" class="form-select" required>
          <option value="2025-05-17">17/05</option>
          <option value="2025-05-18">18/05</option>
          <option value="2025-05-19">19/05</option>
          <option value="2025-05-20">20/05</option>
          <option value="2025-05-21">21/05</option>
          <option value="2025-05-22">22/05</option>
          <option value="2025-05-23">23/05</option>
          <option value="2025-05-24">24/05</option>
          <option value="2025-05-25">25/05</option>
        </select>
      </div>

      <div class="col-md-2">
        <select name="ora" class="form-select" required>
          <option value="19:00">19:00</option>
          <option value="20:30">20:30</option>
          <option value="22:00">22:00</option>
          <option value="23:30">23:30</option>
        </select>
      </div>

      <div class="col-md-1"><input type="number" name="tavolo" class="form-control" placeholder="T." required></div>
      <div class="col-12"><button type="submit" class="btn btn-success">Aggiungi</button></div>
    </form>

    <?php if (isset($errore)) echo "<p class='text-danger'>$errore</p>"; ?>

    <table class="table table-bordered table-hover">
      <thead class="table-dark">
        <tr><th>Nome</th><th>Cognome</th><th>Telefono</th><th>Email</th><th>Data</th><th>Ora</th><th>Tavolo</th><th>Azioni</th></tr>
      </thead>
      <tbody>
      <?php
        $result = pg_query($conn, "SELECT p.id, p.nome, p.cognome, p.telefono, p.email, d.data, d.ora, d.numero_tavolo
        FROM prenotazione p JOIN disponibilita_tavolo d ON p.id_disponibilita = d.id ORDER BY data, ora");
        while ($row = pg_fetch_assoc($result)) {
          echo "<tr>
            <form method='POST'>
            <td><input type='text' name='nome' value='{$row['nome']}' class='form-control'></td>
            <td><input type='text' name='cognome' value='{$row['cognome']}' class='form-control'></td>
            <td><input type='text' name='telefono' value='{$row['telefono']}' class='form-control'></td>
            <td><input type='email' name='email' value='{$row['email']}' class='form-control'></td>
            <td>{$row['data']}</td><td>{$row['ora']}</td><td>{$row['numero_tavolo']}</td>
            <td class='d-flex gap-1'>
              <input type='hidden' name='id' value='{$row['id']}'>
              <button name='modifica' class='btn btn-primary btn-sm'>Modifica</button>
            </form>
            <form method='POST'>
              <input type='hidden' name='elimina' value='{$row['id']}'>
              <button class='btn btn-danger btn-sm'>Elimina</button>
            </form>
            </td>
          </tr>";
        }
      ?>
      </tbody>
    </table>

    <h4 class="mt-5">Messaggi di Feedback</h4>
    <table class="table table-bordered table-hover mt-3">
      <thead class="table-secondary">
        <tr><th>Nome</th><th>Email</th><th>Telefono</th><th>Messaggio</th></tr>
      </thead>
      <tbody>
      <?php
        $res_feedback = pg_query($conn, "SELECT * FROM feedback ORDER BY id DESC");
        while ($f = pg_fetch_assoc($res_feedback)) {
          echo "<tr>
            <td>{$f['nome']}</td>
            <td>{$f['email']}</td>
            <td>{$f['telefono']}</td>
            <td>{$f['messaggio']}</td>
          </tr>";
        }
      ?>
      </tbody>
    </table>

  </div>
<?php endif; ?>
</body>
</html>
