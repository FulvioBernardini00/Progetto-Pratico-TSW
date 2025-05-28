$(document).ready(function () {
  let tavoloSelezionato = null;

  // Disattiva il pulsante inizialmente
  $("#prenota-btn").prop("disabled", true);

  // Validazione form
  $("#formPrenotazione").validate({
    rules: {
      nome: { required: true, minlength: 2, maxlength: 20 },
      cognome: { required: true, minlength: 2, maxlength: 20 },
      telefono: { required: true, minlength: 10, maxlength: 10, number: true },
      email: { email: true }
    },
    messages: {
      nome: { required: "Inserisci il tuo nome!", minlength: "Il nome è troppo corto.", maxlength: "Il nome è troppo lungo." },
      cognome: { required: "Inserisci il tuo cognome!", minlength: "Il cognome è troppo corto.", maxlength: "Il cognome è troppo lungo." },
      telefono: { required: "Inserisci il tuo numero!", minlength: "Numero troppo corto.", maxlength: "Numero troppo lungo.", number: "Numero non valido." },
      email: { email: "Inserisci un'e-mail valida." }
    },
    
  });
  // controllo per aggiornare lo stato del bottone
  $("#formPrenotazione input, #formPrenotazione select").on("input change", checkFormAndTavolo);


  function checkFormAndTavolo() {
    const isFormValid = $("#formPrenotazione").valid();
    $("#prenota-btn").prop("disabled", !(isFormValid && tavoloSelezionato));
  }

  // Selezione tavolo
  $(document).on("click", ".tavolo-cliccabile", function () {
    $(".tavolo-cliccabile").removeClass("tavolo-selezionato");
    $(this).addClass("tavolo-selezionato");
    tavoloSelezionato = $(this).text().trim();
    checkFormAndTavolo();
  });

  // Gestione invio form
  $("#formPrenotazione").on("submit", function (e) {
    e.preventDefault();

    if (!tavoloSelezionato) {
      alert("Seleziona un tavolo!");
      return;
    }

    const nome = $("#nome").val();
    const cognome = $("#cognome").val();
    const telefono = $("#telefono").val();
    const email = $("#email").val();

    const dataInput = $("#data").val();
    const [gg, mm] = dataInput.split("/");
    const data = `2025-${mm}-${gg}`;

    const ora = $("#ora").val();

    if (confirm(`Hai scelto il tavolo ${tavoloSelezionato}, alle ore ${ora} del ${data}.\nConfermi la prenotazione?`)) {
      $.ajax({
        url: "php/prenotazione.php",
        type: "POST",
        data: {
          tav_num: tavoloSelezionato,
          nome,
          cognome,
          telefono,
          email,
          data,
          ora
        },
        success: function (response) {
          if (response.trim() === "ok") {
            alert("Prenotazione effettuata!");
            location.reload();
          } else {
            alert("Errore: " + response);
          }
        },
        error: function (jqXHR, textStatus, errorThrown) {
          console.error("Errore AJAX:", textStatus, errorThrown);
          alert("Errore di comunicazione con il server.");
        }
      });
    }
  });

  // Disabilita i tavoli già prenotati
  function disabilitaTavoliPrenotati() {
    const dataInput = $("#data").val();
    const [gg, mm] = dataInput.split("/");
    const data = `2025-${mm}-${gg}`;
    const ora = $("#ora").val();

    $.ajax({
      url: "php/tavoli_prenotati.php",
      type: "GET",
      data: { data, ora },
      success: function (tavoliPrenotati) {
        $(".tavolo-cliccabile").each(function () {
          const numero = $(this).text().trim();
          if (tavoliPrenotati.includes(numero)) {
            $(this)
              .addClass("tavolo-disabilitato")
              .removeClass("tavolo-cliccabile tavolo-selezionato")
              .off("click")
              .data("numero", numero) 
              .text("");              
          }
        });
      },
      error: function () {
        console.error("Errore nel recupero tavoli prenotati.");
      }
    });
  }

  // Quando si cambia data o ora, aggiorna tavoli prenotati
  $("#data, #ora").on("change", function () {
    // Riattiva tutti i tavoli
    $(".tavolo-disabilitato").each(function () {
      $(this)
        .removeClass("tavolo-disabilitato")
        .addClass("tavolo-cliccabile")
        .off("click")
        .text($(this).data("numero")); // ripristina il numero se lo salvi prima
    });

    tavoloSelezionato = null;
    $(".tavolo-cliccabile").removeClass("tavolo-selezionato");
    $("#prenota-btn").prop("disabled", true);

    disabilitaTavoliPrenotati();
  });

  // Switch mappa
  $("#switch-mappa").click(function () {
    $("#mappa-interna, #mappa-giardino").toggleClass("mappa-attiva");
    const isInternaAttiva = $("#mappa-interna").hasClass("mappa-attiva");
    $(this).text(isInternaAttiva ? "Passa al Giardino" : "Torna all'interno");
  });

  // carica tavoli già prenotati
  disabilitaTavoliPrenotati();
});
