$(document).ready(function() {
    if ($('#dataTable').length) {
        const columns = [
            { title: 'N°', data: null, render: function(data, type, row, meta) { return meta.row + 1; } },
            { data: 'code', title: 'Code' },
            { data: 'client', title: 'Client' },
            { data: 'user', title: 'Utilisateur' },
            { data: 'date', title: 'Date' },
            {
                data: 'statut',
                title: 'Statut',
                render: function(data) {
                    const cls = data === 'actif' ? 'delivered' : 'cancelled';
                    return '<span class="badge-status ' + cls + '">' + data + '</span>';
                }
            },
            {
                data: null,
                title: 'Actions',
                className: 'text-center',
                render: function(data, type, row) {
                    const isActive = row.statut === 'actif';
                    return `
                        <div class="table-actions">
                            <a href="${LINK}commande/details/${row.editId}" title="Voir" class="btn-action btn-action-secondary"><i class="fa fa-eye"></i></a>
                            <a href="${LINK}commande/edition/${row.editId}" title="Modifier" class="btn-action btn-action-primary"><i class="fa fa-edit"></i></a>
                            <button type="button" title="${isActive ? 'Annuler' : 'Activer'}"
                                data-id="${row.id}"
                                class="${isActive ? 'btn-action btn-action-warning changerStatus' : 'btn-action btn-action-success changerStatus'}">
                                <i class="fa ${isActive ? 'fa-toggle-on' : 'fa-toggle-off'}"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ];

        const table = initDataTable('dataTable', 'commande/apiList', columns);
        bindStatusToggle('dataTable', 'commande/changer', table);

        const commandesMobileConfig = {
        entity: 'commande',
        primary: [{ key: 'code', label: 'Code' }],
        secondary: [{ key: 'client_nom', label: 'Client' }, { key: 'montant', label: 'Montant' }],
        detailUrl: function(r) { return LINK + 'commande/details/' + r.editId; },
        actions: [
            { id: 'voir', label: 'Voir', icon: 'eye', href: function(r) { return LINK + 'commande/details/' + r.editId; } },
            { id: 'modifier', label: 'Modifier', icon: 'edit', href: function(r) { return LINK + 'commande/edition/' + r.editId; } },
        ],
        getActions: function(row) {
            var list = commandesMobileConfig.actions.map(function(a) { return Object.assign({}, a, { href: a.href(row) }); });
            var isActive = row.statut === 'actif';
            list.push({
                id: isActive ? 'annuler' : 'activer',
                label: isActive ? 'Annuler' : 'Activer',
                icon: isActive ? 'toggle-left' : 'toggle-right',
                onClick: function(rowData) {
                    showConfirm('Changer le statut de cette commande ?', function() {
                        $.post(LINK + 'commande/changer', { id: rowData.id }, function(rep) {
                            showToast(rep.message || 'Statut changé', rep.status ? 'success' : 'error');
                            $('#dataTable').DataTable().ajax.reload();
                        }, 'json').fail(function() { showToast('Erreur serveur', 'error'); });
                    });
                }
            });
            return list;
        }
    };
    renderMobileCards('dataTable', commandesMobileConfig);
    }

    $('.formEditCommande').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = form.find('.btn_actions');
        loading(btn, true, '<i class="fa fa-spinner fa-spin"></i> Enregistrement...');

        $.ajax({
            url: LINK + 'commande/edit',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(rep) {
                loading(btn, false, '<i class="fa fa-save"></i> Sauvegarder');
                if (rep.status) {
                    showToast(rep.message, 'success');
                    setTimeout(() => window.location.href = LINK + 'commande/list', 700);
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
