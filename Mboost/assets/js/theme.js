(function () {
  const storageKey = 'theme';
  const root = document.documentElement;

  function getPreferredTheme() {
    const saved = localStorage.getItem(storageKey);
    if (saved === 'light' || saved === 'dark') return saved;
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    return prefersDark ? 'dark' : 'light';
  }

  function applyTheme(theme) {
    const isDark = theme === 'dark';
    root.classList.toggle('dark', isDark);
    const btn = document.getElementById('theme-toggle');
    if (btn) {
      btn.setAttribute('aria-pressed', String(isDark));
      btn.title = isDark ? 'Passer en mode clair' : 'Passer en mode sombre';
    }
  }

  // Init on load
  const initial = getPreferredTheme();
  applyTheme(initial);

  // Expose global toggle
  window.toggleTheme = function () {
    const current = root.classList.contains('theme-dark') ? 'dark' : 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    localStorage.setItem(storageKey, next);
    applyTheme(next);
  };

  // Keep in sync with system changes if the user hasn't forced a choice yet
  try {
    const media = window.matchMedia('(prefers-color-scheme: dark)');
    media.addEventListener('change', (e) => {
      const saved = localStorage.getItem(storageKey);
      if (!saved) {
        applyTheme(e.matches ? 'dark' : 'light');
      }
    });
  } catch (_) {
    // Safari < 14 fallback (no addEventListener on MediaQueryList)
  }
})();

(function () {
  const container = document.getElementById('global-fruit-stream');
  if (!container) return;

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduceMotion) return;

  const fruits = ['🍎', '🍏', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓', '🫐', '🥝', '🍍', '🥭', '🍑', '🍒', '🍐', '🥕'];
  const fruitCount = window.innerWidth < 768 ? 24 : 42;
  const nodes = [];

  function rand(min, max) {
    return Math.random() * (max - min) + min;
  }

  function place(node, instant = false) {
    const x = rand(0, window.innerWidth);
    const y = rand(0, window.innerHeight);
    const rotation = rand(-180, 180);
    const scale = rand(0.7, 1.5);
    const duration = rand(9, 20);

    if (instant) {
      node.style.transition = 'none';
    } else {
      node.style.transition = `left ${duration}s linear, top ${duration}s linear, transform ${duration}s linear, opacity 1.5s ease`;
    }

    node.style.left = `${x}px`;
    node.style.top = `${y}px`;
    node.style.transform = `translate(-50%, -50%) rotate(${rotation}deg) scale(${scale})`;
    node.style.opacity = String(rand(0.16, 0.45));
  }

  function moveLoop(node) {
    place(node, false);
    const nextMs = rand(7000, 14000);
    window.setTimeout(() => moveLoop(node), nextMs);
  }

  for (let i = 0; i < fruitCount; i += 1) {
    const node = document.createElement('span');
    node.className = 'fruit-node';
    node.textContent = fruits[Math.floor(Math.random() * fruits.length)];
    node.style.fontSize = `${rand(24, 46)}px`;
    container.appendChild(node);
    place(node, true);
    nodes.push(node);
  }

  window.setTimeout(() => {
    nodes.forEach((node, index) => {
      window.setTimeout(() => moveLoop(node), index * 120);
    });
  }, 100);
})();
