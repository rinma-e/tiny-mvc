"use strict";

// set new Lobibox defaults options
Lobibox.notify.DEFAULTS = $.extend({}, Lobibox.notify.DEFAULTS, {
  width: 500,
  messageHeight: 300,
  delay: false,
  position: "top center",
  iconSource: "boxIcons",
  sound: false,
});

// set new Lobibox additional options
Lobibox.notify.OPTIONS = $.extend({}, Lobibox.notify.OPTIONS, {
  icons: {
    boxIcons: {
      success: "bx bx-check-circle",
      error: "bx bx bx-x-circle",
      warning: "bx bx-error",
      info: "bx bx-info-circle",
    },
  },
});

document.addEventListener("DOMContentLoaded", () => {
  /**
   * Retrieves the initial state of a form based on the given form ID.
   *
   * @param {string} formId - The ID of the form to retrieve the initial state from
   * @return {object} The initial state of the form as an object
   */
  function getInitialFormState(formId) {
    const formElements = document.querySelector(formId).elements;

    return Array.from(formElements)
      .filter((el) => el.type !== "hidden" && el.type !== "submit")
      .reduce((acc, el) => {
        acc[el.name] = el.value;
        return acc;
      }, {});
  }

  // get initial state of user info
  let initialStateUserInfo = getInitialFormState("#updateUserInfoForm");

  // preview avatar when file is selected
  document
    .querySelector("#avatar-upload")
    .addEventListener("change", (event) => {
      removeAllLobiboxNotifications();

      if (event.target.files.length > 0) {
        let avatar = document.getElementById("avatar");

        // backup old avatar src if it's not a blob
        if (!avatar.src.startsWith("blob:")) avatar.dataset.old = avatar.src;

        // update avatar src to preview selected avatar
        avatar.src = URL.createObjectURL(event.target.files[0]);

        addClassForIds({
          ids: ["remove-avatar-btn"],
          className: "d-none",
        });

        removeClassForIds({
          ids: ["upload-avatar-btn", "reset-avatar-btn"],
          className: "d-none",
        });
      }
    });

  // function to cancel avatar preview and restore old avatar
  function cancelAvatar() {
    const avatar = document.getElementById("avatar");

    // detach blob url
    URL.revokeObjectURL(avatar.src);

    avatar.src = avatar.dataset.old;

    if (!avatar.src.includes("default-user.png")) {
      removeClassForIds({
        ids: ["remove-avatar-btn"],
        className: "d-none",
      });
    }

    addClassForIds({
      ids: ["upload-avatar-btn", "reset-avatar-btn"],
      className: "d-none",
    });
  }

  // call cancel avatar function when reset avatar button is clicked
  document.querySelector("#reset-avatar-btn").addEventListener("click", () => {
    cancelAvatar();
  });

  // open confirmation modal when upload avatar button is clicked
  $("#upload-avatar-btn").on("click", () => {
    removeAllLobiboxNotifications();

    // update confirmation modal content
    confirmationModal({
      type: "warning",
      title: "Update Avatar",
      description: "This action will change your avatar. Are you sure?",
      confirmBtnText: "Confirm",
    });
  });

  $("#remove-avatar-btn").on("click", () => {
    removeAllLobiboxNotifications();

    // update confirmation modal content
    confirmationModal({
      type: "danger",
      title: "Remove Avatar",
      description:
        "This action will remove your avatar and set it to the default avatar. Are you sure?",
      confirmBtnText: "Confirm",
    });
  });

  // update user avatar when confirm button is clicked
  document.querySelector("#confirm-btn").addEventListener("click", (e) => {
    // get modal instance
    const modal = bootstrap.Modal.getInstance(
      e.target.closest("#confirmation-modal")
    );

    const allAvatars = document.querySelectorAll(".avatar"); // get all '.avatar' element's
    const defaultAvatar = "default-user.png"; // default avatar

    modal.hide();

    // collect data for form
    const avatarImg = document.getElementById("avatar-upload").files[0];
    const csrf_token = document.querySelector("input[name=csrf_token]").value;

    // create form with data
    let formData = new FormData();
    avatarImg && formData.append("avatar", avatarImg); // if there is a file selected append it
    formData.append("csrf_token", JSON.stringify(csrf_token));

    fetch("updateUserAvatar", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then((result) => {
        if (result["errors"].length === 0) {
          const action = result["avatar"] ? "update" : "remove";
          switch (action) {
            case "update":
              // update all avatars src to new avatar
              allAvatars.forEach((element) => {
                const newSrc = element.dataset.old
                  ? element.dataset.old.replace(
                      /[^/\\&\?]+$/,
                      result.avatar.new_name
                    )
                  : element.src.replace(/[^/\\&\?]+$/, result.avatar.new_name);
                element.src = newSrc;
                if (element.dataset.old) {
                  element.removeAttribute("data-old");
                }
              });

              // show avatar element and remove-avatar-btn
              removeClassForIds({
                ids: ["remove-avatar-btn"],
                className: "d-none",
              });

              // hide avatar preview element, upload-avatar-btn and reset-avatar-btn
              addClassForIds({
                ids: ["upload-avatar-btn", "reset-avatar-btn"],
                className: "d-none",
              });

              Lobibox.notify("success", {
                title: "Success",
                msg: "Avatar has been successfully updated.",
              });

              break;
            case "remove":
              // update all avatars src to new avatar
              allAvatars.forEach((element) => {
                const newSrc = element.dataset.old
                  ? element.dataset.old.replace(/[^/\\&\?]+$/, defaultAvatar)
                  : element.src.replace(/[^/\\&\?]+$/, defaultAvatar);
                element.removeAttribute("data-old");
                element.src = newSrc;
              });

              // hide avatar preview element, upload-avatar-btn, reset-avatar-btn and remove-avatar-btn
              addClassForIds({
                ids: [
                  "upload-avatar-btn",
                  "reset-avatar-btn",
                  "remove-avatar-btn",
                ],
                className: "d-none",
              });

              Lobibox.notify("success", {
                title: "Success",
                msg: "Avatar has been successfully removed. Now using default avatar.",
              });

              break;
          }
        } else {
          // show error notification
          Object.entries(result["errors"]).forEach(([key, value]) => {
            //* every error in separate messages
            value.map((error) => {
              Lobibox.notify("error", {
                title: ucFirst(key) + " error",
                msg: error,
              });
            });
          });

          // cancel avatar upload and restore old avatar
          cancelAvatar();
        }

        // set file input value to empty string (now file input holds no file)
        document.getElementById("avatar-upload").value = "";
      })
      .catch((error) => {
        console.log("Error:", error);
      });
  });

  // check if user info data is changed and enable submit button
  document
    .querySelector("#updateUserInfoForm")
    .addEventListener("change", (event) => {
      const element = event.target;
      const name = element.name;

      if (initialStateUserInfo.hasOwnProperty(name)) {
        const value = initialStateUserInfo[name];
        const currentValue = element.value;
        if (currentValue !== value) {
          element
            .closest("form")
            .querySelector("button[type=submit]").disabled = false;
        } else {
          element
            .closest("form")
            .querySelector("button[type=submit]").disabled = true;
        }
      }
    });

  // submit new user info if data is changed
  document
    .querySelector("#updateUserInfoForm")
    .addEventListener("submit", (event) => {
      event.preventDefault();

      // remove all lobibox notifications if any
      removeAllLobiboxNotifications();

      // get form
      const form = event.target;

      //reset all error messages
      let inputs = form.querySelectorAll("input");
      inputs.forEach(function (input) {
        input.classList.remove("is-invalid");
      });

      // create form with data for sending
      let formData = new FormData(form);

      let hasChanged = false; // changed flag

      // remove data from formData that is not changed
      for (const name in initialStateUserInfo) {
        if (initialStateUserInfo.hasOwnProperty(name)) {
          const value = initialStateUserInfo[name];
          const currentValue = form.elements[name].value;
          if (currentValue === value) {
            formData.delete(name, form.elements[name].value);
          } else {
            hasChanged = true;

            // when setting atribute values with setAttribute,
            // form.reset() will reset to value set by setAttribute not to initial value.
            // this is needed so when resetting form with js it will reset to new value.
            form.elements[name].setAttribute("value", currentValue);
          }
        }
      }

      if (hasChanged) {
        // send data
        fetch("updateUserProfile", {
          method: "POST",
          body: formData,
        })
          .then((response) => {
            if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
          })
          .then((result) => {
            if (!result["errors"] || result["errors"].length === 0) {
              // Reset form to initial state (values and styles)
              form.reset();

              // Disable submit button
              form.querySelector("button[type=submit]").disabled = true;

              // Set new user info state
              initialStateUserInfo = getInitialFormState("#updateUserInfoForm");

              // Show success notification
              Lobibox.notify("success", {
                title: "Success",
                msg: "New user info has been successfully updated.",
              });
            } else {
              // to show all errors styles correctly we reset form befor displaying errors
              form.reset();

              // show errors below inputs in form
              for (const [key, value] of Object.entries(result["errors"])) {
                if (key !== "info") {
                  const element = form.elements["user_" + key];
                  element.classList.add("is-invalid");
                  element.nextElementSibling.innerHTML =
                    value.length <= 1
                      ? value[0]
                      : "<ul class='mb-0 ps-3'><li>" +
                        value.join("</li><li>") +
                        "</li></ul>";
                }
              }

              //if info show message in info notification
              if (result["errors"]["info"]) {
                Lobibox.notify("info", {
                  title: "User update info",
                  msg: result["errors"]["info"],
                });
              }

              //if info show message in info notification
              if (result["errors"]["update"]) {
                Lobibox.notify("error", {
                  title: "User update error",
                  msg: result["errors"]["update"],
                });
              }
            }
          })
          .catch((error) => {
            console.log("Error:", error);
          });
      } else {
        // if nothing changed show info notification
        Lobibox.notify("info", {
          title: "User update info",
          msg: "Nothing to update. No user info has been changed in the form.",
        });
      }
    });

  // check if password fields are not empty and enable submit button
  document
    .querySelector("#updatePasswordForm")
    .addEventListener("change", (event) => {
      const form = event.target.closest("form");
      form.querySelectorAll("input:not([type=hidden])").forEach((element) => {
        const value = element.value;
        if (value !== "") {
          form.querySelector("button[type=submit]").disabled = false;
        } else {
          form.querySelector("button[type=submit]").disabled = true;
        }
      });
    });

  // submit password change
  document
    .querySelector("#updatePasswordForm")
    .addEventListener("submit", async (event) => {
      event.preventDefault();

      // remove all lobibox notifications if any
      removeAllLobiboxNotifications();

      // get form
      const form = event.target;

      //reset all error messages
      let inputs = form.querySelectorAll("input");
      inputs.forEach(function (input) {
        input.classList.remove("is-invalid");
        input.nextElementSibling.classList.remove("border-danger");
      });

      // create form with data for sending
      let formData = new FormData(form);

      // get data from form for validation
      const oldPasswordEl = form.elements["old_password"];
      const password = form.elements["password"].value;
      const confirmPassword = form.elements["confirm_password"].value;

      // prepare errors
      let passwordErrors = {};

      // check if all fields are filled
      if (oldPasswordEl.value && password && confirmPassword) {
        const isOldPasswordValidated =
          oldPasswordEl.classList.contains("is-valid");
        let isOldPasswordCorrect = isOldPasswordValidated;

        if (!isOldPasswordValidated) {
          const verifyOldPassword = await oldPasswordVerification(formData);

          // if there are errors show them
          if (verifyOldPassword.errors) {
            Object.assign(passwordErrors, verifyOldPassword.errors);
          }

          if (verifyOldPassword === true) {
            isOldPasswordCorrect = true;
            oldPasswordEl.classList.add("is-valid");
            oldPasswordEl.nextElementSibling.classList.add("border-success");
          }
        }

        if (!isOldPasswordCorrect) {
          passwordErrors["old_password"] ??= "The old password is not correct.";
        } else if (oldPasswordEl.value === password) {
          passwordErrors.password =
            "The new password cannot be the same as the old one.";
        } else if (password !== confirmPassword) {
          passwordErrors.confirm_password =
            "The new password does not match the confirm password.";
        }

        if (!isObjectEmpty(passwordErrors)) {
          // show errors below inputs in form
          for (const [key, value] of Object.entries(passwordErrors)) {
            const element = form.elements[key];
            element.classList.add("is-invalid");
            element.nextElementSibling.classList.add("border-danger");
            element.parentElement.lastElementChild.innerHTML = value;
          }
        }
      } else {
        Lobibox.notify("warning", {
          title: "Password warning",
          msg: "All password fields must be filled out.",
        });
      }

      if (isObjectEmpty(passwordErrors)) {
        // send data
        fetch("updateUserPassword", {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((result) => {
            if (!result["errors"] || result["errors"].length === 0) {
              // reset form to initial state (values and styles)
              form.reset();

              // reset old password input style
              form.elements["old_password"].classList.remove("is-valid");
              form.elements["old_password"].nextElementSibling.classList.remove(
                "border-success"
              );

              // disable submit button
              form.querySelector("button[type=submit]").disabled = true;

              // show success notification
              Lobibox.notify("success", {
                title: "Success",
                msg: "New user info has been successfully updated.",
              });
            } else {
              // show errors below inputs in form
              for (const [key, value] of Object.entries(result["errors"])) {
                const element = form.elements[key];
                element.classList.add("is-invalid");
                element.nextElementSibling.classList.add("border-danger");
                element.parentElement.lastElementChild.innerHTML =
                  value.length <= 1
                    ? value[0]
                    : "<ul class='mb-0 ps-3'><li>" +
                      value.join("</li><li>") +
                      "</li></ul>";
              }
            }
          })
          .catch((error) => {
            console.log(error);
          });
      }
    });

  /**
   * Function to verify old password.
   *
   * @param {object} data - the data to be sent for verification
   * @return {Promise} the response data if verification is successful, otherwise false
   */
  async function oldPasswordVerification(data) {
    const requestOptions = {
      method: "POST",
      body: data,
    };

    try {
      const response = await fetch(
        base_url + "user/checkIsOldPasswordCorrect",
        requestOptions
      );
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      const responseData = await response.json();
      return responseData;
    } catch (error) {
      console.error("Error occurred while checking old password:", error);
      return false;
    }
  }

  /**
   * Check if the given object is empty.
   *
   * @param {Object} obj - the object to be checked
   * @return {boolean} true if the object is empty, false otherwise
   */
  const isObjectEmpty = (obj) => {
    for (let key in obj) {
      if (obj.hasOwnProperty(key)) {
        return false;
      }
    }
    return true;
  };
});
