const openBtn = document.getElementById("openModal");
const closeBtn = document.getElementById("closeModal");
const closeRegBtn = document.getElementById("closeModalReg");
const modal = document.getElementById("modal");
const modalReg = document.getElementById("modalReg");
const regFormBtn = document.querySelector('#regFormBtn');



openBtn.addEventListener("click", () => {
    modal.style.display = "flex";
});

closeBtn.addEventListener("click", () => {
    modal.style.display = "none";
});

closeRegBtn.addEventListener("click", () => {
    modalReg.style.display = "none";
});

regFormBtn.addEventListener("click", () => {
    modal.style.display = "none";
    modalReg.style.display = "flex";
});
