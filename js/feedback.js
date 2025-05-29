$(document).ready(function () {
  $("#feedback-form").validate({
    // Regole di validazione
    rules: {
      nome: {
        required: true,
        minlength: 2,
        maxlength: 50
      },
      email: {
        required: true,
        email: true
      },
      messaggio: {
        required: true,
        minlength: 5
      }
    },
    // Messaggi di errore personalizzati
    messages: {
      nome: {
        required: "Inserisci il tuo nome",
        minlength: "Minimo 2 caratteri",
        maxlength: "Massimo 50 caratteri"
      },
      email: {
        required: "Inserisci l'email",
        email: "Email non valida"
      },
      messaggio: {
        required: "Scrivi un messaggio",
        minlength: "Almeno 5 caratteri"
      }
    },
    // In caso di validazione corretta, invio AJAX
    submitHandler: function () {
      invioFeedback(); 
    }
  });

  // Funzione di invio AJAX 
  function invioFeedback() {
    var nome = document.feedbackForm.nome.value;
    var email = document.feedbackForm.email.value;
    var telefono = document.feedbackForm.telefono.value;
    var messaggio = document.feedbackForm.messaggio.value;

    $.ajax({
      url: "php/feedback.php",
      type: "POST",
      data: { nome, email, telefono, messaggio },
      success: function (result) {
        if (result === "success") {
          alert("Messaggio inviato con successo!");
          document.feedbackForm.reset(); // resettiamo il form
        } else {
          alert("Errore durante l'invio. Riprova.");
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.log("Errore AJAX:", textStatus, errorThrown);
        alert("Errore durante la connessione. Riprova.");
      }
    });
  }
});
