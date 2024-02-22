"use strict";

$(function () {
  $(document).ready(function () {
    $(window).on("scroll", function () {
      $(this).scrollTop() > 50
        ? $(".back-to-top").fadeIn()
        : $(".back-to-top").fadeOut();
    }),
      $(".back-to-top").on("click", function () {
        return (
          $("html, body").animate(
            {
              scrollTop: 0,
            },
            10
          ),
          !1
        );
      }),
      $(".toggle-password").on("focus click keydown", function (e) {
        let code = e.keyCode || e.which;
        if ((e.type === "click" || code === 13) && e.type !== "keydown") {
          e.preventDefault();
          $(this).find(".toggle-password-icon").toggleClass("bx-show bx-hide");
          let input = $(this).find(".toggle-password-icon").attr("toggle");
          if ($(input).attr("type") === "password") {
            $(input).attr("type", "text");
          } else {
            $(input).attr("type", "password");
          }
        }
      });
  });
});

/**
 * Adds a specified class to the elements with the given IDs, if the class is not already present.
 *
 * @param {Array<string>} ids - An array of element IDs
 * @param {string} className - The class name to be added
 * @return {void}
 */
function addClassForIds({ ids, className }) {
  ids.forEach((id) => {
    const element = document.getElementById(id);
    if (element && !element.classList.contains(className)) {
      element.classList.add(className);
    }
  });
}

/**
 * Removes the specified class from the elements with the given IDs.
 *
 * @param {Array<string>} ids - An array of element IDs
 * @param {string} className - The class name to be removed
 * @return {void}
 */
function removeClassForIds({ ids, className }) {
  ids.forEach((id) => {
    const element = document.getElementById(id);
    if (element && element.classList.contains(className)) {
      element.classList.remove(className);
    }
  });
}

/**
 * Opens a confirmation modal with the specified type, title, description, and button text.
 *
 * @param {object} options - Object containing the modal options
 * @param {string | HTMLElement } options.type - The type of the modal (allowed values: primary, secondary, tertiary, success, danger, warning, info, empty string)
 * @param {string | HTMLElement} options.title - The title of the modal
 * @param {string | HTMLElement} options.description - The description shown in the modal
 * @param {string | HTMLElement} options.confirmBtnText - The text shown on the confirmation button
 * @param {string} [options.btnType=options.type] - The type of the confirmation button (allowed values: primary, secondary, tertiary, success, danger, warning, info)
 */
function confirmationModal({
  type = "",
  title,
  description,
  confirmBtnText,
  btnType = type,
}) {
  const allowedTypes = [
    "primary",
    "secondary",
    "tertiary",
    "success",
    "danger",
    "warning",
    "info",
    "",
  ];

  if (!allowedTypes.includes(type)) {
    console.error("Invalid type. Allowed types are: ", allowedTypes);
    return;
  }

  const modal = document.getElementById("confirmation-modal");
  const modalHeader = modal.querySelector(".modal-header");
  const modalTitle = modal.querySelector(".modal-title");
  const modalBody = modal.querySelector(".modal-body");
  const modalCloseOnX = modal.querySelector(".btn-close");
  const confirmBtn = modal.querySelector("#confirm-btn");

  type && modalHeader.classList.add("text-bg-" + type);
  modalTitle.innerHTML = title;
  modalBody.innerHTML = description;
  ["primary", "secondary", "success", "danger"].includes(type) &&
    modalCloseOnX.classList.add("btn-close-white");
  confirmBtn.classList.add("btn-" + (btnType ? btnType : "primary"));
  confirmBtn.innerHTML = confirmBtnText;
  confirmBtn.classList.remove("d-none");

  modal.addEventListener("hidden.bs.modal", () => {
    // reset modal classes. content will be overwritten in next modal show
    modalHeader.classList.remove("text-bg-" + type);
    modalCloseOnX.classList.remove("btn-close-white");
    confirmBtn.classList.add("d-none");
    confirmBtn.classList.remove("btn-" + (btnType ? btnType : "primary"));
  });
}

/**
 * Capitalizes the first letter of a string.
 *
 * @param {string} string - The input string
 * @return {string} The string with the first letter capitalized
 */
function ucFirst(string) {
  return string ? string.charAt(0).toUpperCase() + string.slice(1) : "";
}

//* remove all lobibox notifications
function removeAllLobiboxNotifications() {
  let wrapper = document.querySelector(".lobibox-notify-wrapper");
  wrapper && wrapper.remove();
  // with jquery is not collecting all lobibox notifications so we use js to collect them
  let elements = document.querySelectorAll(".lobibox-notify");
  elements.forEach(function (element) {
    // data lobibox is stored with jquery data() method in lobibox-notify. not in data-lobibox attribute
    let lobiboxData = $(element).data("lobibox");
    // process lobiboxData
    lobiboxData.remove();
  });
}

const tooltipTriggerList = document.querySelectorAll(
  '[data-bs-toggle="tooltip"]'
);
const tooltipList = [...tooltipTriggerList].map(
  (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
);
