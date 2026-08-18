window.openCommandeModal = function(clientCode) {
    const base = (typeof LINK !== 'undefined') ? LINK : ((typeof RACINE !== 'undefined') ? RACINE : '/admin-lavex/');
    window.location.href = base + 'commande/list' + (clientCode ? '?client=' + encodeURIComponent(clientCode) : '');
};

$(document).ready(function() {
    if ($('#dataTable').length) {
        const columns = [
            { title: 'N°', data: null, render: function(data, type, row, meta) { return meta.row + 1; } },
            { data: 'code', title: 'Code' },
            { data: 'nom', title: 'Nom' },
            { data: 'telephone', title: 'Téléphone' },
            { data: 'quartier', title: 'Quartier' },
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
                            <a href="${LINK}client/details/${row.editId}" title="Voir" class="btn-action btn-action-secondary"><i class="fa fa-eye"></i></a>
                            <a href="${LINK}client/edition/${row.editId}" title="Modifier" class="btn-action btn-action-primary"><i class="fa fa-edit"></i></a>
                            <button type="button" title="Nouvelle commande"
                                data-code="${row.code}"
                                class="btn-action btn-action-success btnNouvelleCommande">
                                <i class="fa fa-shopping-cart"></i>
                            </button>
                            <button type="button" title="${isActive ? 'Désactiver' : 'Activer'}"
                                data-id="${row.id}"
                                class="${isActive ? 'btn-action btn-action-warning changerStatus' : 'btn-action btn-action-success changerStatus'}">
                                <i class="fa ${isActive ? 'fa-toggle-on' : 'fa-toggle-off'}"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ];

        const table = initDataTable('dataTable', 'client/apiList', columns);
        bindStatusToggle('dataTable', 'client/changer', table);

        const clientsMobileConfig = {
        entity: 'client',
        primary: [{ key: 'code', label: 'Code' }, { key: 'nom', label: 'Nom' }],
        secondary: [{ key: 'telephone', label: 'Téléphone' }, { key: 'quartier', label: 'Quartier' }],
        detailUrl: function(r) { return LINK + 'client/details/' + r.editId; },
        actions: [
            { id: 'voir', label: 'Voir', icon: 'eye', href: function(r) { return LINK + 'client/details/' + r.editId; } },
            { id: 'modifier', label: 'Modifier', icon: 'edit', href: function(r) { return LINK + 'client/edition/' + r.editId; } },
            { id: 'nouvelle_commande', label: 'Nouvelle commande', icon: 'shopping-cart', onClick: function(rowData) {
                window.openCommandeModal(rowData.code);
            }},
        ],
        getActions: function(row) {
            var list = clientsMobileConfig.actions.map(function(a) { return Object.assign({}, a, { href: a.href ? a.href(row) : undefined }); });
            var isActive = row.statut === 'actif';
            list.push({
                id: isActive ? 'desactiver' : 'activer',
                label: isActive ? 'Désactiver' : 'Activer',
                icon: isActive ? 'toggle-left' : 'toggle-right',
                onClick: function(rowData) {
                    showConfirm('Changer le statut de ce client ?', function() {
                        $.post(LINK + 'client/changer', { id: rowData.id }, function(rep) {
                            showToast(rep.message || 'Statut changé', rep.status ? 'success' : 'error');
                            $('#dataTable').DataTable().ajax.reload();
                        }, 'json').fail(function() { showToast('Erreur serveur', 'error'); });
                    });
                }
            });
            return list;
        }
    };
    renderMobileCards('dataTable', clientsMobileConfig);

    $(document).on('click', '.btnNouvelleCommande', function() {
        var clientCode = $(this).data('code');
        window.openCommandeModal(clientCode);
    });

    $('#btnNouvelleCommande').on('click', function() {
        window.openCommandeModal('');
    });
    }

    $('.formEditClient').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = form.find('.btn_actions');
        loading(btn, true, '<i class="fa fa-spinner fa-spin"></i> Enregistrement...');

        $.ajax({
            url: LINK + 'client/edit',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(rep) {
                loading(btn, false, '<i class="fa fa-save"></i> Sauvegarder');
                if (rep.status) {
                    showToast(rep.message, 'success');
                    setTimeout(() => window.location.href = LINK + 'client/list', 700);
                } else {
                    showToast(rep.message, 'error');
                }
            },
            error: function(xhr) {
                loading(btn, false, '<i class="fa fa-save"></i> Sauvegarder');
                let msg = 'Erreur serveur';
                try {
                    const resp = JSON.parse(xhr.responseText);
                    if (resp && resp.message) msg = resp.message;
                } catch(e) {}
                showToast(msg, 'error');
            }
        });
    });

    const urlParams = new URLSearchParams(window.location.search);
    const newClientCode = urlParams.get('new_client');
    if (newClientCode && window.openCommandeModal) {
        setTimeout(function() {
            window.openCommandeModal(newClientCode);
        }, 500);
    }
});
