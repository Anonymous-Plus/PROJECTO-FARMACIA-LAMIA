// Funcionalidade simples para os cards: ao clicar no botão "Ver detalhes", abre modal com informação do remédio
// Isto torna o site interativo mas mantém o código fácil de entender e explicar.
const botoesDetalhe = document.querySelectorAll(".btn-detalhe");
const modal = document.getElementById("medModal");
const modalNome = document.getElementById("modalNomeMed");
const modalPrecoSpan = document.getElementById("modalPreco");
const closeBtn = document.getElementById("closeModalBtn");

// Função para abrir o modal com dados do medicamento
function abrirModal(nome, preco) {
  modalNome.innerText = nome;
  modalPrecoSpan.innerText = preco;
  modal.style.display = "flex";
}

// Adicionar evento para cada botão dos cards
botoesDetalhe.forEach((botao) => {
  botao.addEventListener("click", function () {
    const nomeMed = this.getAttribute("data-medicamento");
    const precoMed = this.getAttribute("data-preco");
    abrirModal(nomeMed, precoMed);
  });
});

// Fechar modal ao clicar no botão ou clicar fora da caixa (clique no overlay)
function fecharModal() {
  modal.style.display = "none";
}

closeBtn.addEventListener("click", fecharModal);
window.addEventListener("click", function (event) {
  if (event.target === modal) {
    fecharModal();
  }
});

// Pequeno efeito de navegação suave (torna o site mais agradável, porém simples)
document.querySelectorAll(".nav-links a").forEach((link) => {
  link.addEventListener("click", function (e) {
    const href = this.getAttribute("href");
    if (href && href.startsWith("#")) {
      e.preventDefault();
      const targetId = href.substring(1); // remove o #
      const targetElement = document.getElementById(targetId);
      if (targetElement) targetElement.scrollIntoView({ behavior: "smooth" });
    }
  });
});

const footerLinks = document.querySelectorAll(".footer-col a");
footerLinks.forEach((link) => {
  link.addEventListener("click", function (e) {
    const href = this.getAttribute("href");
    if (href && href.startsWith("#")) {
      e.preventDefault();
      const rawId = href.substring(1);
      const el = document.getElementById(rawId);
      if (el) el.scrollIntoView({ behavior: "smooth" });
    }
  });
});

// Menu hamburguer para telas móveis
const menuToggle = document.querySelector(".menu-toggle");
const navLinks = document.querySelector(".nav-links");

if (menuToggle && navLinks) {
  menuToggle.addEventListener("click", () => {
    navLinks.classList.toggle("active");
    menuToggle.classList.toggle("open");
  });
}
