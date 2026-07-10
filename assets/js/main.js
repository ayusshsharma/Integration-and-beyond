/**
 * Ayush Integration Lab — Main JavaScript
 */
(function () {
  'use strict';

  // Mobile menu toggle
  var menuToggle = document.querySelector('.menu-toggle');
  var navigation = document.querySelector('.main-navigation');

  if (menuToggle && navigation) {
    menuToggle.addEventListener('click', function () {
      var isOpen = navigation.classList.toggle('is-open');
      menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
  }

  // Generate table of contents from h2/h3 headings
  function generateTOC() {
    var content = document.querySelector('.single-post__content');
    var toc = document.getElementById('toc');
    var tocList = document.getElementById('toc-list');

    if (!content || !toc || !tocList) return;

    var headings = content.querySelectorAll('h2, h3');
    if (headings.length === 0) return;

    headings.forEach(function (heading, index) {
      if (!heading.id) {
        heading.id = 'section-' + index;
      }

      var li = document.createElement('li');
      var a = document.createElement('a');
      a.href = '#' + heading.id;
      a.textContent = heading.textContent;
      li.appendChild(a);

      if (heading.tagName === 'H3') {
        var subOl = document.createElement('ol');
        subOl.appendChild(li);
        tocList.appendChild(subOl);
      } else {
        tocList.appendChild(li);
      }
    });

    toc.style.display = 'block';
  }

  // Add language labels to code blocks
  function labelCodeBlocks() {
    document.querySelectorAll('pre code[class*="language-"]').forEach(function (code) {
      var pre = code.parentElement;
      if (pre.previousElementSibling && pre.previousElementSibling.classList.contains('code-block-label')) {
        return;
      }

      var match = code.className.match(/language-(\w+)/);
      if (!match) return;

      var label = document.createElement('span');
      label.className = 'code-block-label';
      label.textContent = match[1].toUpperCase();
      pre.parentNode.insertBefore(label, pre);
    });
  }

  generateTOC();
  labelCodeBlocks();

  if (typeof Prism !== 'undefined') {
    Prism.highlightAll();
  }
})();
