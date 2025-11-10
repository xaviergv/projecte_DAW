function showSection(id) {
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
  document.getElementById(id).classList.add('active');
  event.target.classList.add('active');
}

function showTab(id) {
  document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));
  document.getElementById(id).classList.add('active');
  event.target.classList.add('active');
}

// Actualització en temps real cada 10 segons
setInterval(() => {
  fetch('index.php')
      .then(r => r.text())
      .then(html => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          document.querySelector('#parceles-grid').innerHTML = doc.querySelector('#parceles-grid').innerHTML;
          document.querySelector('#estoc tbody').innerHTML = doc.querySelector('#estoc tbody').innerHTML;
      });
}, 10000);