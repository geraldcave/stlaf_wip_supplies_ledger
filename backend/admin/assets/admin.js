function updateRequestStatus(req_id, status) {
  let reason = null;

  if (status === "Cancelled") {
    reason = prompt("Please enter a reason for cancellation:");
    if (!reason) {
      alert("Reason is required to cancel.");
      return;
    }
  }

  fetch("../../auth/oop/request_form.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams({
      method: "updateStatus",
      req_id: req_id,
      status: status,
      reason: reason,
    }),
  })
    .then((res) => res.json())
    .then((data) => {
      alert(data.message);
      if (data.status === "success") location.reload();
    })
    .catch((err) => console.error("Error:", err));
}
// Pagination setup
const rowsPerPage = 8;
const tableBody = document.getElementById("requestTableBody");
const pagination = document.getElementById("pagination");
const rows = tableBody.querySelectorAll("tr");
const totalPages = Math.ceil(rows.length / rowsPerPage);

function displayPage(page) {
  const start = (page - 1) * rowsPerPage;
  const end = start + rowsPerPage;

  rows.forEach((row, i) => {
    row.style.display = i >= start && i < end ? "" : "none";
  });
}

function setupPagination() {
  pagination.innerHTML = "";
  for (let i = 1; i <= totalPages; i++) {
    const li = document.createElement("li");
    li.className = "page-item";
    li.innerHTML = `<button class="page-link bg-light border-0 text-primary fw-bold">${i}</button>`;
    li.addEventListener("click", () => {
      displayPage(i);
      document
        .querySelectorAll(".page-item")
        .forEach((el) => el.classList.remove("active"));
      li.classList.add("active");
    });
    pagination.appendChild(li);
  }
  if (pagination.firstChild) pagination.firstChild.classList.add("active");
}

displayPage(1);
setupPagination();
