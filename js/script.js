// script.js
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

document.addEventListener('DOMContentLoaded', function () {
  var userTranspose = 0;  // transposição do usuário
  var capo = 0;           // valor do capotraste
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
    var totalSemitones = userTranspose - capo;  // transposição final aplicada nos acordes
    updateTransposition(totalSemitones);
    updateDisplayedKey(originalKey, userTranspose); // tom exibido considera só a transposição do usuário
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

  // Fullscreen toggle (deixa como está, mesmo com limitações)
  var fullscreenBtn = document.getElementById('fullscreen-toggle');
  if (fullscreenBtn) {
    fullscreenBtn.addEventListener('click', function () {
      var docEl = document.documentElement;
      if (
        document.fullscreenElement ||
        document.webkitFullscreenElement ||
        document.mozFullScreenElement ||
        document.msFullscreenElement
      ) {
        if (document.exitFullscreen) {
          document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
          document.webkitExitFullscreen();
        } else if (document.mozCancelFullScreen) {
          document.mozCancelFullScreen();
        } else if (document.msExitFullscreen) {
          document.msExitFullscreen();
        }
      } else {
        if (docEl.requestFullscreen) {
          docEl.requestFullscreen();
        } else if (docEl.webkitRequestFullscreen) {
          docEl.webkitRequestFullscreen();
        } else if (docEl.mozRequestFullScreen) {
          docEl.mozRequestFullScreen();
        } else if (docEl.msRequestFullscreen) {
          docEl.msRequestFullscreen();
        } else {
          alert("Seu navegador não suporta fullscreen.");
        }
      }
    });
  }

  applyChanges();
});
