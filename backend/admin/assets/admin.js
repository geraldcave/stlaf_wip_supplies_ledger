const toggler = document.querySelector(".toggler-btn");
toggler.addEventListener("click", function () {
  document.querySelector("#sidebar").classList.toggle("collapsed");
});

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
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
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

document.addEventListener("DOMContentLoaded", function () {
  const tabButtons = document.querySelectorAll(".tab-button");
  const tabContents = document.querySelectorAll(".tab-pane");
  const lastTab = localStorage.getItem("activeTab");

  if (lastTab) {
    tabButtons.forEach((btn) => {
      btn.classList.remove("active");
      const target = btn.dataset.bsTarget;
      if (target === lastTab) {
        btn.classList.add("active");
        const tab = document.querySelector(target);
        if (tab) {
          tabContents.forEach((t) => t.classList.remove("show", "active"));
          tab.classList.add("show", "active");
        }
      }
    });
  }

  tabButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      tabButtons.forEach((b) => b.classList.remove("active"));
      tabContents.forEach((tab) => tab.classList.remove("show", "active"));
      btn.classList.add("active");
      const target = document.querySelector(btn.dataset.bsTarget);
      if (target) {
        target.classList.add("show", "active");
        localStorage.setItem("activeTab", btn.dataset.bsTarget);
      }
    });
  });
});

const rowsPerPage = 10;
const pagination = document.getElementById("pagination");

function setupPagination(tableBody) {
  const rows = tableBody.querySelectorAll("tr");
  const totalPages = Math.ceil(rows.length / rowsPerPage);

  function displayPage(page) {
    const start = (page - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    rows.forEach((row, i) => {
      row.style.display = i >= start && i < end ? "" : "none";
    });
  }

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
  displayPage(1);
}

document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("searchInput");
  if (!searchInput) return;

  searchInput.addEventListener("keyup", function () {
    const searchValue = searchInput.value.toLowerCase().trim();
    const activeTab = document.querySelector(".tab-pane.active.show");
    if (!activeTab) return;

    const table = activeTab.querySelector("table");
    if (!table) return;

    const rows = table.querySelectorAll("tbody tr");
    let visibleCount = 0;

    rows.forEach((row) => {
      if (row.classList.contains("no-results-row")) return;
      const text = row.textContent.toLowerCase();
      if (text.includes(searchValue)) {
        row.style.display = "";
        visibleCount++;
      } else {
        row.style.display = "none";
      }
    });

    let noResultRow = table.querySelector(".no-results-row");
    if (visibleCount === 0) {
      if (!noResultRow) {
        const tr = document.createElement("tr");
        const colCount = table.querySelectorAll("thead th").length;
        tr.classList.add("no-results-row");
        tr.innerHTML = `<td colspan="${colCount}" class="text-center text-muted py-3">No matching requests found.</td>`;
        table.querySelector("tbody").appendChild(tr);
      }
    } else if (noResultRow) {
      noResultRow.remove();
    }
  });
});
