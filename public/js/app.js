/* Interaksi kecil tanpa framework: baris dinamis, konfirmasi, total otomatis. */
(function () {
  const fmt = (n) => 'Rp ' + Math.round(n).toLocaleString('id-ID');
  const num = (v) => parseFloat(v) || 0;

  // Sidebar mobile
  document.querySelectorAll('[data-toggle-sidebar]').forEach((btn) =>
    btn.addEventListener('click', () => document.querySelector('.sidebar').classList.toggle('open')));
  document.addEventListener('click', (e) => {
    const sb = document.querySelector('.sidebar.open');
    if (sb && !sb.contains(e.target) && !e.target.closest('[data-toggle-sidebar]')) sb.classList.remove('open');
  });

  // Konfirmasi sebelum aksi penting
  document.addEventListener('submit', (e) => {
    const msg = e.target.dataset.confirm;
    if (msg && !window.confirm(msg)) e.preventDefault();
  });

  // Baris berulang
  document.querySelectorAll('[data-lines]').forEach((wrap) => {
    const list = wrap.querySelector('[data-lines-list]');
    const tpl = wrap.querySelector('template');
    let index = parseInt(wrap.dataset.next || list.children.length, 10);

    const refresh = () => {
      const rows = list.querySelectorAll('.line');
      rows.forEach((row) => {
        const btn = row.querySelector('.remove');
        if (btn) btn.disabled = wrap.hasAttribute('data-min-one') && rows.length === 1;
      });
      wrap.dispatchEvent(new Event('lines:change'));
    };

    wrap.querySelector('[data-add-line]').addEventListener('click', () => {
      list.insertAdjacentHTML('beforeend', tpl.innerHTML.replaceAll('__i__', index++));
      refresh();
      list.lastElementChild.querySelector('select, input')?.focus();
    });
    list.addEventListener('click', (e) => {
      const btn = e.target.closest('.remove');
      if (!btn) return;
      btn.closest('.line').remove();
      refresh();
    });
    refresh();
  });

  // Form pre-order: harga otomatis & total
  const po = document.querySelector('[data-po-form]');
  if (po) {
    const recalc = () => {
      let total = 0;
      po.querySelectorAll('.line').forEach((row) => {
        const sub = num(row.querySelector('[data-qty]')?.value) * num(row.querySelector('[data-unit-price]')?.value);
        total += sub;
        const out = row.querySelector('.subtotal');
        if (out) out.textContent = fmt(sub);
      });
      const dp = num(po.querySelector('[name=down_payment]')?.value);
      po.querySelector('[data-total]').textContent = fmt(total);
      po.querySelector('[data-remaining]').textContent = fmt(total - dp);
    };
    po.addEventListener('change', (e) => {
      if (e.target.matches('[data-product]')) {
        const price = e.target.selectedOptions[0]?.dataset.price;
        const priceInput = e.target.closest('.line').querySelector('[data-unit-price]');
        if (price && priceInput) priceInput.value = price;
      }
      recalc();
    });
    po.addEventListener('input', recalc);
    po.addEventListener('lines:change', recalc, true);
    recalc();
  }

  // Satuan otomatis di baris resep & rencana
  document.addEventListener('change', (e) => {
    if (!e.target.matches('[data-unit-source]')) return;
    const unit = e.target.selectedOptions[0]?.dataset.unit || '';
    const scope = e.target.closest('.line') || e.target.form;
    const out = scope?.querySelector('[data-unit-label]');
    if (out) out.textContent = unit;
  });

  // Stok keluar: batch mengikuti bahan yang dipilih
  const out = document.querySelector('[data-stock-out]');
  if (out) {
    const material = out.querySelector('[name=raw_material_id]');
    const batch = out.querySelector('[name=stock_batch_id]');
    const all = JSON.parse(out.dataset.batches || '[]');
    const selected = out.dataset.selectedBatch;
    const sync = () => {
      const id = parseInt(material.value, 10);
      batch.innerHTML = '<option value="">Otomatis (kedaluwarsa terdekat dulu)</option>';
      all.filter((b) => b.material === id).forEach((b) => {
        const opt = new Option(b.label, b.id, false, String(b.id) === selected);
        batch.add(opt);
      });
      const unit = material.selectedOptions[0]?.dataset.unit || '';
      out.querySelectorAll('[data-unit-label]').forEach((el) => (el.textContent = unit));
    };
    material.addEventListener('change', sync);
    sync();
  }
})();
