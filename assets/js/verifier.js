document
  .getElementById("human-verifier-form")
  .addEventListener("submit", function (e) {
    e.preventDefault();

    let codigo = document.getElementById("codigo").value;

    fetch(human_ajax.ajax_url, {
      method: "POST",

      body: new URLSearchParams({
        action: "verificar_diploma",
        codigo: codigo,
        security: human_ajax.nonce,
      }),
    })
      .then((res) => res.json())
      .then((data) => {
        console.log(data);
      });
  });
