$(document).ready(function() {
    if ($('#dataTable').length) {
        const columns = [
            { title: 'N°', data: null, render: function(data, type, row, meta) { return meta.row + 1; } },
            { data: 'code', title: 'Code' },
            { data: 'commande', title: 'Commande' },
            { data: 'livreur', title: 'Livreur' },
            {
                data: 'type',
                title: 'Type de Mission',
                render: function(data) {
                    const isCollecte = (data || '').toLowerCase() === 'collecte';
                    if (isCollecte) {
                        return '<span style="background:#FEF3C7; color:#B45309; border:1px solid #FCD34D; border-radius:6px; padding:4px 8px; font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:5px;"><i class="fa fa-box"></i> Collecte</span>';
                    } else {
                        return '<span style="background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; border-radius:6px; padding:4px 8px; font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:5px;"><i class="fa fa-truck"></i> Livraison</span>';
                    }
                }
            },
            { data: 'adresse', title: 'Adresse' },
            {
                data: 'statut',
                title: 'Statut',
                render: function(data) {
                    const map = { 'en_attente': 'warning', 'en_cours': 'info', 'terminee': 'delivered', 'annulee': 'cancelled' };
                    const cls = map[data] || 'cancelled';
                    const labels = { 'en_attente': 'En attente', 'en_cours': 'En cours', 'terminee': 'Terminée', 'annulee': 'Annulée' };
                    return '<span class="badge-status ' + cls + '">' + (labels[data] || data) + '</span>';
                }
            },
            {
                data: null,
                title: 'Actions',
                className: 'text-center',
                render: function(data, type, row) {
                    return `
                        <div class="table-actions">
                            <a href="${LINK}mission/edition/${row.editId}" title="Modifier" class="btn-action btn-action-primary"><i class="fa fa-edit"></i></a>
                        </div>
                    `;
                }
            }
        ];

        const table = initDataTable('dataTable', 'mission/apiList', columns);

        const missionsMobileConfig = {
        entity: 'mission',
        primary: [{ key: 'code', label: 'Mission' }],
        secondary: [{ key: 'commande', label: 'Commande' }, { key: 'type', label: 'Type' }],
        detailUrl: function(r) { return LINK + 'mission/edition/' + r.editId; },
        actions: [
            { id: 'modifier', label: 'Modifier', icon: 'edit', href: function(r) { return LINK + 'mission/edition/' + r.editId; } },
        ],
        getActions: function(row) {
            var list = missionsMobileConfig.actions.map(function(a) { return Object.assign({}, a, { href: a.href(row) }); });
            return list;
        }
    };
    renderMobileCards('dataTable', missionsMobileConfig);
    }

    $('.formEditMission').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = form.find('.btn_actions');
        const idMission = form.find('#id_mission').val();
        const isAdd = !idMission;
        const url = isAdd ? LINK + 'mission/add' : LINK + 'mission/edit';

        loading(btn, true, '<i class="fa fa-spinner fa-spin"></i> Enregistrement...');

        $.ajax({
            url: url,
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(rep) {
                loading(btn, false, '<i class="fa fa-save"></i> Sauvegarder');
                if (rep.status) {
                    showToast(rep.message, 'success');
                    setTimeout(() => window.location.href = LINK + 'mission/list', 700);
                } else {
                    showToast(rep.message, 'error');
                }
            },
            error: function() {
                loading(btn, false, '<i class="fa fa-save"></i> Sauvegarder');
                showToast('Erreur serveur', 'error');
            }
        });
    });
});
