(function () {
  function getCookie(name) {
    var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? match[2] : null;
  }

  function setCookie(name, value, days) {
    var expires = '';
    if (days) {
      var date = new Date();
      date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
      expires = '; expires=' + date.toUTCString();
    }
    document.cookie = name + '=' + value + expires + '; path=/';
  }

  var modal = document.getElementById('dvp-lead-modal');
  if (!modal) return;

  var form = document.getElementById('dvp-lead-form');
  var message = modal.querySelector('.dvp-lead-message');
  var pendingUrl = null;

  function openModal() {
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('dvp-modal-open');
  }

  function closeModal() {
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('dvp-modal-open');
  }

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.dvp-doc-btn');
    if (!btn) return;

    var unlocked = getCookie(dvpLead.cookieName);
    if (unlocked) return;

    e.preventDefault();
    pendingUrl = btn.getAttribute('href');
    message.textContent = '';
    openModal();
  });

  modal.addEventListener('click', function (e) {
    if (e.target.matches('[data-dvp-close]')) {
      closeModal();
    }
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    message.textContent = 'Submitting...';

    var formData = new FormData(form);
    formData.append('action', 'dvp_submit_lead');
    formData.append('nonce', dvpLead.nonce);

    fetch(dvpLead.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (!data || !data.success) {
          message.textContent = (data && data.data && data.data.message) ? data.data.message : 'Something went wrong.';
          return;
        }

        setCookie(dvpLead.cookieName, '1', dvpLead.unlockDays || 7);
        message.textContent = 'Thank you! Your download will start now.';
        closeModal();
        if (pendingUrl) {
          window.location.href = pendingUrl;
          pendingUrl = null;
        }
      })
      .catch(function () {
        message.textContent = 'Network error. Please try again.';
      });
  });
})();
