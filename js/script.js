var NOTES_SHARP = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];
var MAX_FRET = 24;
var fontSize = 14;

function noteIndex(note) {
  var m = note.match(/^([A-G])(b|#)?$/);
  if (!m) return -1;
  var name = m[1] + (m[2] || '');
  var idx = NOTES_SHARP.indexOf(name);
  if (idx !== -1) return idx;
  var flatToSharp = { 'Db': 'C#', 'Eb': 'D#', 'Gb': 'F#', 'Ab': 'G#', 'Bb': 'A#' };
  return flatToSharp[name] ? NOTES_SHARP.indexOf(flatToSharp[name]) : -1;
}

function transposeChord(chordText, semitones) {
  var regex = /^([A-G][b#]?)([^\s\/]*)(?:\/([A-G][b#]?))?$/;
  var m = chordText.match(regex);
  if (!m) return chordText;
  var root = m[1], quality = m[2], bass = m[3];
  var idx = noteIndex(root);
  if (idx < 0) return chordText;
  var newRoot = NOTES_SHARP[((idx + semitones) % 12 + 12) % 12];
  var newBass = '';
  if (bass) {
    var ib = noteIndex(bass);
    newBass = ib >= 0 ? '/' + NOTES_SHARP[((ib + semitones) % 12 + 12) % 12] : '/' + bass;
  }
  return newRoot + quality + newBass;
}

function transposeFret(original, semitones) {
  var newF = original + semitones;
  if (newF > MAX_FRET) newF -= 12;
  if (newF < 0) newF += 12;
  return Math.min(Math.max(newF, 0), MAX_FRET);
}

function updateTransposition(semitones) {
  var chords = document.querySelectorAll('.chord strong');
  for (var i = 0; i < chords.length; i++) {
    var el = chords[i];
    var orig = el.getAttribute('data-original-chord');
    el.textContent = transposeChord(orig, semitones);
  }

  var frets = document.querySelectorAll('.fret');
  for (var j = 0; j < frets.length; j++) {
    var el2 = frets[j];
    var o = parseInt(el2.getAttribute('data-original-fret'), 10);
    if (!isNaN(o)) {
      var nf = transposeFret(o, semitones);
      el2.setAttribute('data-fret', nf);
      el2.textContent = nf;
    }
  }
}

function updateDisplayedKey(originalKey, userTranspose) {
  var tomElement = document.getElementById('current-tom');
  if (tomElement && originalKey) {
    tomElement.textContent = transposeChord(originalKey, userTranspose);
  }
}

function resetTransposition() {
  var chords = document.querySelectorAll('.chord strong');
  for (var i = 0; i < chords.length; i++) {
    chords[i].textContent = chords[i].getAttribute('data-original-chord');
  }

  var frets = document.querySelectorAll('.fret');
  for (var j = 0; j < frets.length; j++) {
    var el = frets[j];
    var o = parseInt(el.getAttribute('data-original-fret'), 10);
    if (!isNaN(o)) {
      el.setAttribute('data-fret', o);
      el.textContent = o;
    }
  }

  var tomElement = document.getElementById('current-tom');
  if (tomElement && tomElement.getAttribute('data-original-tom')) {
    tomElement.textContent = tomElement.getAttribute('data-original-tom');
  }
}

function getCurrentChords() {
  var chordsEls = document.querySelectorAll('.chord strong');
  var chordsSet = {};
  for (var i = 0; i < chordsEls.length; i++) {
    var txt = chordsEls[i].textContent.trim();
    if (txt && !chordsSet[txt]) {
      chordsSet[txt] = true;
    }
  }
  var keys = [];
  for (var k in chordsSet) {
    keys.push(k);
  }
  return keys.sort();
}

function updateChordDictionary() {
  var container = document.getElementById('dictionary-content');
  if (!container) return;

  var chords = getCurrentChords();
  if (chords.length === 0) {
    container.innerHTML = '<p>Nenhum acorde detectado.</p>';
    return;
  }

  container.innerHTML = '';

  for (var i = 0; i < chords.length; i++) {
    (function (chord) {
      var xhr = new XMLHttpRequest();
      xhr.open('GET', 'http://192.168.0.70/cifras/pages/acorde.php?chord=' + encodeURIComponent(chord), true);

      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
          if (xhr.status === 200) {
            try {
              // Append the new content without overwriting previous
              container.innerHTML += xhr.responseText;
            } catch (e) {
              container.innerHTML += '<p>Erro ao interpretar resposta.</p>';
            }
          } else {
            container.innerHTML += '<p>Erro ao carregar acorde: ' + chord + '</p>';
          }
        }
      };
      xhr.send(null); // GET não envia corpo
    })(chords[i]);
  }
}


document.addEventListener('DOMContentLoaded', function () {
  var userTranspose = 0;
  var capo = 0;
  var originalKey = null;

  var chords = document.querySelectorAll('.chord strong');
  for (var i = 0; i < chords.length; i++) {
    chords[i].setAttribute('data-original-chord', chords[i].textContent);
  }

  var frets = document.querySelectorAll('.fret');
  for (var j = 0; j < frets.length; j++) {
    frets[j].setAttribute('data-original-fret', frets[j].getAttribute('data-fret'));
  }

  var tomEl = document.getElementById('current-tom');
  if (tomEl) {
    originalKey = tomEl.textContent.trim();
    tomEl.setAttribute('data-original-tom', originalKey);
  }

  function applyChanges() {
    var totalSemitones = userTranspose - capo;
    updateTransposition(totalSemitones);
    updateDisplayedKey(originalKey, userTranspose);
    updateChordDictionary();
  }

  var btnUp = document.getElementById('transpose-up');
  if (btnUp) {
    btnUp.addEventListener('click', function () {
      userTranspose = (userTranspose + 1) % 12;
      applyChanges();
    });
  }

  var btnDown = document.getElementById('transpose-down');
  if (btnDown) {
    btnDown.addEventListener('click', function () {
      userTranspose = (userTranspose - 1 + 12) % 12;
      applyChanges();
    });
  }

  var btnReset = document.getElementById('transpose-reset');
  if (btnReset) {
    btnReset.addEventListener('click', function () {
      userTranspose = 0;
      var capoSelect = document.getElementById('capo-select');
      if (capoSelect) {
        capo = parseInt(capoSelect.getAttribute('data-original-capo') || '0', 10);
        capoSelect.value = capo;
      }
      resetTransposition();
      applyChanges();
    });
  }

  var capoSelect = document.getElementById('capo-select');
  if (capoSelect) {
    capoSelect.setAttribute('data-original-capo', capoSelect.value);
    capo = parseInt(capoSelect.value, 10) || 0;
    capoSelect.addEventListener('change', function () {
      capo = parseInt(this.value, 10) || 0;
      applyChanges();
    });
  }

  var fontControls = document.getElementById('font-controls');
  var preEl = document.querySelector('pre');
  if (fontControls && preEl) {
    var btnDec = document.getElementById('font-decrease');
    var btnInc = document.getElementById('font-increase');
    if (btnDec) {
      btnDec.addEventListener('click', function () {
        fontSize = Math.max(10, fontSize - 1);
        preEl.style.fontSize = fontSize + 'px';
      });
    }
    if (btnInc) {
      btnInc.addEventListener('click', function () {
        fontSize = Math.min(32, fontSize + 1);
        preEl.style.fontSize = fontSize + 'px';
      });
    }
  }

  var fullscreenBtn = document.getElementById('fullscreen-toggle');
  if (fullscreenBtn) {
    fullscreenBtn.addEventListener('click', function () {
      var docEl = document.documentElement;
      var isFull = document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement;

      if (isFull) {
        if (document.exitFullscreen) document.exitFullscreen();
        else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
        else if (document.mozCancelFullScreen) document.mozCancelFullScreen();
        else if (document.msExitFullscreen) document.msExitFullscreen();
      } else {
        if (docEl.requestFullscreen) docEl.requestFullscreen();
        else if (docEl.webkitRequestFullscreen) docEl.webkitRequestFullscreen();
        else if (docEl.mozRequestFullScreen) docEl.mozRequestFullScreen();
        else if (docEl.msRequestFullscreen) docEl.msRequestFullscreen();
        else alert("Seu navegador não suporta fullscreen.");
      }
    });
  }

  var capoDown = document.getElementById("capo-down");
  capoDown.addEventListener("click", function () {
    var capoSelect = document.getElementById("capo-select");
    var novoValue = (parseInt(capoSelect.value) - 1);
    if (novoValue >= 0) {
      capoSelect.value = novoValue;

      var changeEvent = new Event('change');
      document.getElementById("capo-select").dispatchEvent(changeEvent);
    }
  });

  var capoUp = document.getElementById("capo-up");
  capoUp.addEventListener("click", function () {
    var capoSelect = document.getElementById("capo-select");
    var novoValue = (parseInt(capoSelect.value) + 1);
    if (novoValue <= 12) {
      capoSelect.value = novoValue;

      var changeEvent = new Event('change');
      document.getElementById("capo-select").dispatchEvent(changeEvent);
    }
  });

  applyChanges();
});
