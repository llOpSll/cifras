const NOTES_SHARP = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];
const MAX_FRET = 24;

function noteIndex(note) {
  const m = note.match(/^([A-G])(b|#)?$/);
  if (!m) return -1;
  const name = m[1] + (m[2] || '');
  const flats = { 'Db': 'C#', 'Eb': 'D#', 'Gb': 'F#', 'Ab': 'G#', 'Bb': 'A#' };
  return NOTES_SHARP.indexOf(name) !== -1 ? NOTES_SHARP.indexOf(name) : NOTES_SHARP.indexOf(flats[name] || '');
}

function transposeChord(chordText, semitones) {
  const regex = /^([A-G][b#]?)(.*?)(?:\/(\S+))?$/;
  const m = chordText.match(regex);
  if (!m) return chordText;
  const [, root, quality, bass] = m;
  const idx = noteIndex(root);
  if (idx < 0) return chordText;
  const newRoot = NOTES_SHARP[(idx + semitones + 12) % 12];
  let newBass = '';
  if (bass) {
    const ib = noteIndex(bass);
    newBass = ib >= 0 ? '/' + NOTES_SHARP[(ib + semitones + 12) % 12] : '/' + bass;
  }
  return newRoot + quality + newBass;
}

function transposeFret(original, semitones) {
  let newF = original + semitones;
  if (newF > MAX_FRET) newF -= 12;
  if (newF < 0) newF += 12;
  return Math.min(Math.max(newF, 0), MAX_FRET);
}

function updateTransposition(semitones) {
  document.querySelectorAll('.chord strong').forEach(el => {
    const orig = el.dataset.originalChord;
    el.textContent = transposeChord(orig, semitones);
  });
  document.querySelectorAll('.fret').forEach(el => {
    const o = parseInt(el.dataset.originalFret, 10);
    if (!isNaN(o)) {
      const nf = transposeFret(o, semitones);
      el.dataset.fret = nf;
      el.textContent = nf;
    }
  });

  // Atualizar o campo de TOM exibido
  const tomEl = document.getElementById('meta-tom');
  if (tomEl && tomEl.dataset.originalTom) {
    const origTom = tomEl.dataset.originalTom;
    const idx = noteIndex(origTom);
    if (idx >= 0) {
      const transTom = NOTES_SHARP[(idx + semitones + 12) % 12];
      tomEl.textContent = transTom;
    }
  }
}

function resetTransposition(originalCapo) {
  document.querySelectorAll('.chord strong').forEach(el => el.textContent = el.dataset.originalChord);
  document.querySelectorAll('.fret').forEach(el => {
    const o = parseInt(el.dataset.originalFret, 10);
    if (!isNaN(o)) {
      el.dataset.fret = o;
      el.textContent = o;
    }
  });

  const tomEl = document.getElementById('meta-tom');
  if (tomEl && tomEl.dataset.originalTom) {
    tomEl.textContent = tomEl.dataset.originalTom;
  }

  // Reset do select do capotraste para original
  const capoSelect = document.getElementById('capo-select');
  if (capoSelect) {
    capoSelect.value = originalCapo;
  }
}

document.addEventListener('DOMContentLoaded', () => {
  let manualTranspose = 0;
  let capoTranspose = 0;

  document.querySelectorAll('.chord strong').forEach(el => el.dataset.originalChord = el.textContent);
  document.querySelectorAll('.fret').forEach(el => el.dataset.originalFret = el.dataset.fret);

  const tomEl = document.getElementById('meta-tom');
  if (tomEl) tomEl.dataset.originalTom = tomEl.textContent;

  const capoSelect = document.getElementById('capo-select');
  const btnDown = document.getElementById('transpose-down');
  const btnUp = document.getElementById('transpose-up');
  const btnReset = document.getElementById('transpose-reset');

  // Inicializa capotraste com valor do select ou 0
  capoTranspose = capoSelect ? parseInt(capoSelect.value, 10) || 0 : 0;
  // Guarda valor original do capotraste para reset
  const originalCapo = capoTranspose;

  const updateAll = () => updateTransposition(manualTranspose + capoTranspose);

  const updateDisplay = () => {
    const totalTranspose = manualTranspose + capoTranspose;
    document.getElementById('current-transpose').textContent = `${totalTranspose > 0 ? '+' : ''}${totalTranspose} semitons`;
    updateAll();
  };

  if (btnDown) btnDown.addEventListener('click', () => {
    manualTranspose--;
    updateDisplay();
  });

  if (btnUp) btnUp.addEventListener('click', () => {
    manualTranspose++;
    updateDisplay();
  });

  if (capoSelect) {
    capoSelect.addEventListener('change', () => {
      capoTranspose = parseInt(capoSelect.value, 10) || 0;
      updateDisplay();
    });
  }

  if (btnReset) {
    btnReset.addEventListener('click', () => {
      manualTranspose = 0;
      capoTranspose = originalCapo;
      resetTransposition(originalCapo);
      updateDisplay();
    });
  }

  updateDisplay();
});
