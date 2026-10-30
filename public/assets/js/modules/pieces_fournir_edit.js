/**
 * Module JS: Édition / Ajout de Pièces Administratives à Fournir
 * Externalisation stricte du code JS pour views/pieces_fournir/edit.php
 */
let batchRowIndex = 2;

function switchPieceTab(tab) {
  const btnSingle = document.getElementById('tab-btn-single');
  const btnBatch = document.getElementById('tab-btn-batch');
  const paneSingle = document.getElementById('pane-single');
  const paneBatch = document.getElementById('pane-batch');

  if (!btnSingle || !btnBatch || !paneSingle || !paneBatch) return;

  if (tab === 'single') {
    btnSingle.style.background = '#1E3A5F';
    btnSingle.style.color = '#FFFFFF';
    btnSingle.style.border = 'none';

    btnBatch.style.background = '#FFFFFF';
    btnBatch.style.color = '#64748B';
    btnBatch.style.border = '1px solid #CBD5E1';

    paneSingle.style.display = 'block';
    paneBatch.style.display = 'none';
  } else {
    btnBatch.style.background = '#1E3A5F';
    btnBatch.style.color = '#FFFFFF';
    btnBatch.style.border = 'none';

    btnSingle.style.background = '#FFFFFF';
    btnSingle.style.color = '#64748B';
    btnSingle.style.border = '1px solid #CBD5E1';

    paneSingle.style.display = 'none';
    paneBatch.style.display = 'block';
  }
  if (window.lucide) lucide.createIcons();
}

function addBatchRow() {
  const container = document.getElementById('batch-rows-container');
  if (!container) return;

  const rowCount = container.querySelectorAll('.batch-row').length + 1;
  const tr = document.createElement('tr');
  tr.className = 'batch-row';
  tr.style.borderBottom = '1px solid #F1F5F9';
  tr.innerHTML = `
    <td style="padding: 12px 14px; font-weight: 700; color: #64748B;">${rowCount}</td>
    <td style="padding: 12px 14px;">
      <input type="text" 
             name="pieces[${batchRowIndex}][libelle]" 
             placeholder="Ex: Certificat médical d'aptitude" 
             class="form-control" 
             style="border-radius: 6px; padding: 9px 12px; border: 1px solid #CBD5E1; font-weight: 600; width: 100%;">
    </td>
    <td style="padding: 12px 14px;">
      <input type="text" 
             name="pieces[${batchRowIndex}][description]" 
             placeholder="Ex: Délivré par un médecin agréé" 
             class="form-control" 
             style="border-radius: 6px; padding: 9px 12px; border: 1px solid #CBD5E1; font-weight: 500; width: 100%;">
    </td>
    <td style="padding: 12px 14px; text-align: center;">
      <button type="button" onclick="removeBatchRow(this)" class="btn btn-sm btn-danger" style="background: #FEE2E2; color: #DC2626; border: none; border-radius: 6px; padding: 6px 10px; cursor: pointer;">
        <i data-lucide="trash-2" style="width: 15px; height: 15px;"></i>
      </button>
    </td>
  `;
  container.appendChild(tr);
  batchRowIndex++;
  if (window.lucide) lucide.createIcons();
}

function removeBatchRow(btn) {
  const row = btn.closest('tr');
  if (row) {
    row.remove();
    const rows = document.querySelectorAll('#batch-rows-container .batch-row');
    rows.forEach((r, idx) => {
      const tdNum = r.querySelector('td');
      if (tdNum) tdNum.textContent = idx + 1;
    });
  }
}

document.addEventListener('DOMContentLoaded', function() {
  if (window.lucide) lucide.createIcons();
});
