document.addEventListener("DOMContentLoaded", function () {
  var modal = document.getElementById("leader-modal");
  if (!modal) return;

  var overlay = modal.querySelector(".ldr-modal-overlay");
  var closeBtn = modal.querySelector(".ldr-modal-close");
  var cards = document.querySelectorAll(".ldr-card[data-leader]");
  var modalName = document.getElementById("ldr-modal-name");
  var modalRole = document.getElementById("ldr-modal-role");
  var modalText = document.getElementById("ldr-modal-text");

  var leaders = {
    ahmed: {
      name: "Ahmed Mohiuddin",
      role: "Chairman / Founder",
      text:
        "Every great enterprise is built twice — first in vision, then in perseverance.\n\nAt Group Delta, we started with a clear purpose: to deliver port services of the highest standard, with integrity at every step. Over the years, that purpose has expanded across industries and geographies, but the core of who we are has never changed. We build on trust. We grow through people. And we measure success not just by what we achieve, but by the value we create for those we serve.\n\nTo our clients, partners, and teams: you are the reason we strive for more. The journey ahead is our most exciting yet."
    },
    shamil: {
      name: "Shamil Ahmed",
      role: "Director",
      text:
        "The businesses that endure are not those that resist change, they are those that lead it.\n\nAt Group Delta, innovation is not a department or a strategy. It is a mindset that runs through everything we do. We invest in technology, in people, and in ideas that keep us ahead, so that our clients always have a partner who is ready for what comes next.\n\nWe are grateful for the trust that has brought us this far, and energised by the possibilities that lie ahead."
    },
    shahzeer: {
      name: "Mohammed Shahzeer",
      role: "Director",
      text:
        "Diversification is often seen as a business strategy. For us, it is a reflection of curiosity, a genuine desire to learn, adapt and contribute across new frontiers.\n\nPaired with a deep commitment to technology, it has allowed Group Delta to grow in ways that are both broad and meaningful. We are present across sectors and markets that matter and we are constantly asking how we can do more, serve better and reach further.\n\nThe future belongs to those willing to build it. We intend to be among them, and we are honoured to have you alongside us as we do."
    }
  };

  function openModal(key) {
    var data = leaders[key];
    if (!data) return;
    modalName.textContent = data.name;
    modalRole.textContent = data.role;
    modalText.textContent = data.text;
    modal.classList.add("is-open");
    document.body.classList.add("ldr-modal-open");
  }

  function closeModal() {
    modal.classList.remove("is-open");
    document.body.classList.remove("ldr-modal-open");
  }

  cards.forEach(function (card) {
    card.addEventListener("click", function () {
      openModal(card.getAttribute("data-leader"));
    });
    card.addEventListener("keydown", function (event) {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        openModal(card.getAttribute("data-leader"));
      }
    });
  });

  if (overlay) overlay.addEventListener("click", closeModal);
  if (closeBtn) closeBtn.addEventListener("click", closeModal);

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape" && modal.classList.contains("is-open")) {
      closeModal();
    }
  });
});
