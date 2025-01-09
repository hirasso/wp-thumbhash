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
        this.querySelector("[data-placeholders-generate]")
      );
      this.attachmendID = parseInt(String(this.getAttribute("data-id")), 10);
      button.addEventListener("click", this.generate);
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
  }

  customElements.define("thumbhash-attachment-field", ThumbhashAttachmentField);
})(/** @type {jQuery} */ jQuery);
