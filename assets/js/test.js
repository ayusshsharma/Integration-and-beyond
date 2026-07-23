/**
 * Integration & Beyond — /test UI preview scripts
 */
(function () {
  'use strict';

  var menuToggle = document.querySelector('.menu-toggle');
  var navigation = document.querySelector('.main-navigation');

  if (menuToggle && navigation) {
    menuToggle.addEventListener('click', function () {
      var isOpen = navigation.classList.toggle('is-open');
      menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
  }

  function generateTOC() {
    var content = document.querySelector('.single-post__content');
    var toc = document.getElementById('toc');
    var tocList = document.getElementById('toc-list');

    if (!content || !toc || !tocList) return;

    var headings = content.querySelectorAll('h2, h3');
    if (headings.length === 0) return;

    var currentSubList = null;

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
        if (!currentSubList) {
          currentSubList = document.createElement('ol');
          var wrapper = document.createElement('li');
          wrapper.appendChild(currentSubList);
          tocList.appendChild(wrapper);
        }
        currentSubList.appendChild(li);
      } else {
        currentSubList = null;
        tocList.appendChild(li);
      }
    });

    toc.hidden = false;
  }

  function enhanceCodeBlocks() {
    document.querySelectorAll('pre').forEach(function (pre) {
      if (pre.closest('.code-block')) return;

      var code = pre.querySelector('code');
      var language = 'CODE';

      if (code) {
        var match = code.className.match(/language-(\w+)/);
        if (match) language = match[1].toUpperCase();
      }

      var wrapper = document.createElement('div');
      wrapper.className = 'code-block';

      var header = document.createElement('div');
      header.className = 'code-block-header';

      var label = document.createElement('span');
      label.className = 'code-block-label';
      label.textContent = language;

      var button = document.createElement('button');
      button.type = 'button';
      button.className = 'copy-code-btn';
      button.textContent = 'Copy';
      button.setAttribute('aria-label', 'Copy code to clipboard');

      button.addEventListener('click', function () {
        var text = code ? code.textContent : pre.textContent;
        var done = function () {
          button.textContent = 'Copied';
          button.classList.add('is-copied');
          window.setTimeout(function () {
            button.textContent = 'Copy';
            button.classList.remove('is-copied');
          }, 1600);
        };

        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(text).then(done).catch(function () {
            fallbackCopy(text, done);
          });
        } else {
          fallbackCopy(text, done);
        }
      });

      header.appendChild(label);
      header.appendChild(button);
      pre.parentNode.insertBefore(wrapper, pre);
      wrapper.appendChild(header);
      wrapper.appendChild(pre);
    });
  }

  function fallbackCopy(text, done) {
    var area = document.createElement('textarea');
    area.value = text;
    area.setAttribute('readonly', '');
    area.style.position = 'absolute';
    area.style.left = '-9999px';
    document.body.appendChild(area);
    area.select();
    try {
      document.execCommand('copy');
      done();
    } catch (e) {
      // ignore
    }
    document.body.removeChild(area);
  }

  function observeTocActive() {
    var links = document.querySelectorAll('.table-of-contents a');
    if (!links.length || !('IntersectionObserver' in window)) return;

    var map = {};
    links.forEach(function (link) {
      var id = link.getAttribute('href');
      if (id && id.charAt(0) === '#') {
        map[id.slice(1)] = link;
      }
    });

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var active = map[entry.target.id];
        if (!active) return;
        links.forEach(function (link) { link.classList.remove('is-active'); });
        active.classList.add('is-active');
      });
    }, {
      rootMargin: '-20% 0px -65% 0px',
      threshold: 0.1
    });

    Object.keys(map).forEach(function (id) {
      var el = document.getElementById(id);
      if (el) observer.observe(el);
    });
  }

  generateTOC();
  enhanceCodeBlocks();
  observeTocActive();

  if (typeof Prism !== 'undefined') {
    Prism.highlightAll();
  }
})();
