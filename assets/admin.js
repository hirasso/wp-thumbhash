/// <reference path="./types.d.ts"/>

"use strict";

/**
 * @typedef {import('jquery')} jQuery
 * @typedef {import('jqueryui')} jQueryUI
 */

(($) => {
  /**
   * Create an element on the fly
   * @param {string} html - The HTML string to create the element from.
   * @return {HTMLElement} The created element.
   */
  function createElement(html) {
    const template = document.createElement("template");
    template.innerHTML = html;
    return /** @type {HTMLElement} */ (template.content.children[0]);
  }

  class ThumbhashAttachmentField extends HTMLElement {
    /** @type {number} attachmentID */
    attachmendID;

    constructor() {
      super();

      const button = /** @type {!HTMLButtonElement} */ (
        this.querySelector("[data-thumbhash-action]")
      );

      this.attachmendID = parseInt(String(this.getAttribute("data-id")), 10);

      button.addEventListener("click", () => {
        button.getAttribute("data-thumbhash-action") === "show"
          ? this.toggleShow()
          : this.generate();
      });

      const thumbHash =
        /** @type {HTMLElement} */ this.querySelector("thumb-hash");
      if (thumbHash) {
        thumbHash.addEventListener("click", this.toggleShow);
      }
    }

    generate = () => {
      const { url, action, nonce: security } = window.wpThumbhash.ajax;
      $.ajax({
        url,
        method: "POST",
        data: {
          action,
          security,
          id: this.attachmendID,
        },
        success: (response) => {
          const { html } = response?.data;
          if (response.success) {
            this.replaceWith(createElement(html));
          }
        },
        error: (error) => {
          console.error("AJAX Error:", error);
        },
      });
    };

    toggleShow = () => {
      this.classList.toggle("show");
    };
  }

  customElements.define("thumbhash-attachment-field", ThumbhashAttachmentField);
})(/** @type {jQuery} */ jQuery);
