

document.addEventListener("DOMContentLoaded", function () {


  // Role permissions toggle
  const roleSelect = document.getElementById("role-select");
  if (roleSelect) {
    roleSelect.addEventListener("change", function () {
      const selectedRole = this.value;

      // Hide all permission sections
      document.getElementById("admin-permissions").style.display = "none";
      document.getElementById("manager-permissions").style.display = "none";
      document.getElementById("user-permissions").style.display = "none";

      // Show selected role's permissions
      document.getElementById(`${selectedRole}-permissions`).style.display =
        "block";
    });
  }

  // Modal functionality
  const modals = document.querySelectorAll(".modal");
  const addCompanyBtn = document.getElementById("add-company-btn");
  const closeModalBtns = document.querySelectorAll(".close-modal");

  if (addCompanyBtn) {
    addCompanyBtn.addEventListener("click", function () {
      document.getElementById("company-modal").style.display = "flex";
    });
  }

  closeModalBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      modals.forEach((modal) => {
        modal.style.display = "none";
      });
    });
  });

  window.addEventListener("click", function (e) {
    modals.forEach((modal) => {
      if (e.target === modal) {
        modal.style.display = "none";
      }
    });
  });

  // Initialize Charts
  // const userActivityCtx = document
  //   .getElementById("userActivityChart")
  //   .getContext("2d");
  // const userActivityChart = new Chart(userActivityCtx, {
  //   type: "bar",
  //   data: {
  //     labels: ["Admin", "Managers", "Users"],
  //     datasets: [
  //       {
  //         label: "Active Users",
  //         data: [1, 3, 4],
  //         backgroundColor: ["#e74c3c", "#3498db", "#95a5a6"],
  //       },
  //     ],
  //   },
  //   options: {
  //     responsive: true,
  //     maintainAspectRatio: false,
  //     scales: {
  //       y: {
  //         beginAtZero: true,
  //         ticks: {
  //           stepSize: 1,
  //         },
  //       },
  //     },
  //   },
  // });

  // const systemUsageCtx = document
  //   .getElementById("systemUsageChart")
  //   .getContext("2d");
  // const systemUsageChart = new Chart(systemUsageCtx, {
  //   type: "doughnut",
  //   data: {
  //     labels: ["Active", "Idle", "Maintenance"],
  //     datasets: [
  //       {
  //         data: [85, 10, 5],
  //         backgroundColor: ["#27ae60", "#f39c12", "#e74c3c"],
  //       },
  //     ],
  //   },
  //   options: {
  //     responsive: true,
  //     maintainAspectRatio: false,
  //   },
  // });

  // const financialOverviewCtx = document
  //   .getElementById("financialOverviewChart")
  //   .getContext("2d");
  // const financialOverviewChart = new Chart(financialOverviewCtx, {
  //   type: "line",
  //   data: {
  //     labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
  //     datasets: [
  //       {
  //         label: "Income",
  //         data: [220000, 240000, 245000, 260000, 255000, 270000],
  //         borderColor: "#27ae60",
  //         backgroundColor: "rgba(39, 174, 96, 0.1)",
  //         fill: true,
  //       },
  //       {
  //         label: "Expenses",
  //         data: [180000, 190000, 187000, 195000, 200000, 205000],
  //         borderColor: "#e74c3c",
  //         backgroundColor: "rgba(231, 76, 60, 0.1)",
  //         fill: true,
  //       },
  //     ],
  //   },
  //   options: {
  //     responsive: true,
  //     maintainAspectRatio: false,
  //     scales: {
  //       y: {
  //         beginAtZero: true,
  //         ticks: {
  //           callback: function (value) {
  //             return "₹" + value.toLocaleString();
  //           },
  //         },
  //       },
  //     },
  //   },
  // });

  // const expenseDistributionCtx = document
  //   .getElementById("expenseDistributionChart")
  //   .getContext("2d");
  // const expenseDistributionChart = new Chart(expenseDistributionCtx, {
  //   type: "pie",
  //   data: {
  //     labels: ["Salaries", "Rent", "Utilities", "Marketing", "Other"],
  //     datasets: [
  //       {
  //         data: [45, 25, 15, 10, 5],
  //         backgroundColor: [
  //           "#3498db",
  //           "#e74c3c",
  //           "#f39c12",
  //           "#9b59b6",
  //           "#95a5a6",
  //         ],
  //       },
  //     ],
  //   },
  //   options: {
  //     responsive: true,
  //     maintainAspectRatio: false,
  //   },
  // });
});

$("#company-form").on("submit", function (e) {
  e.preventDefault();

  let form = $(this);
  let formData = new FormData(this);

  // Reset error messages
  $(".error-message").text("");

  // Disable button + show loader
  $("#save-company-btn").prop("disabled", true);
  $(".btn-text").hide();
  $(".btn-loading").show();
  let storeUrl = document.querySelector(
    'meta[name="companies-store-url"]'
  ).content;

  $.ajax({
    url: storeUrl,
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    headers: {
      "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
    success: function (response) {
      if (response.success) {
        // Close the modal
        $("#company-modal").hide();

        // Optional: reset the form
        form.trigger("reset");

        // Show success message
        alert("Company saved successfully!");
      }
    },

    error: function (xhr) {
      // Re-enable button
      $("#save-company-btn").prop("disabled", false);
      $(".btn-text").show();
      $(".btn-loading").hide();

      if (xhr.status === 422) {
        let errors = xhr.responseJSON.errors;

        // Apply errors to fields
        if (errors.code) {
          $("#code-error").text(errors.code[0]);
        }
        if (errors.name) {
          $("#name-error").text(errors.name[0]);
        }
        if (errors.email) {
          $("#email-error").text(errors.email[0]);
        }
        if (errors.website) {
          $("#website-error").text(errors.website[0]);
        }
        if (errors.currency) {
          $("#currency-error").text(errors.currency[0]);
        }
      } else {
        alert("Something went wrong. Please try again.");
      }
    },

    complete: function () {
      // Hide loader when request completes (success or error)
      $("#save-company-btn").prop("disabled", false);
      $(".btn-text").show();
      $(".btn-loading").hide();
    },
  });
});
$(".close-modal").on("click", function () {
  $("#company-modal").hide();
});
