$(document).ready(function() {
    if ($('#dataTable').length) {
        const columns = [
            { title: 'N°', data: null, render: function(data, type, row, meta) { return meta.row + 1; } },
            { data: 'code', title: 'Code' },
            { data: 'nom', title: 'Nom' },
            { data: 'prenom', title: 'Prénom' },
            { data: 'telephone', title: 'Téléphone' },
            { data: 'pressing', title: 'Pressing' },
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
                            <a href="${LINK}livreur/details/${row.editId}" title="Voir" class="btn-action btn-action-secondary"><i class="fa fa-eye"></i></a>
                            <a href="${LINK}livreur/edition/${row.editId}" title="Modifier" class="btn-action btn-action-primary"><i class="fa fa-edit"></i></a>
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

        const table = initDataTable('dataTable', 'livreur/apiList', columns);
        bindStatusToggle('dataTable', 'livreur/changer', table);

        const livreursMobileConfig = {
        entity: 'livreur',
        primary: [{ key: 'nom', label: 'Livreur' }],
        secondary: [{ key: 'code', label: 'Code' }, { key: 'telephone', label: 'Téléphone' }],
        detailUrl: function(r) { return LINK + 'livreur/details/' + r.editId; },
        actions: [
            { id: 'voir', label: 'Voir', icon: 'eye', href: function(r) { return LINK + 'livreur/details/' + r.editId; } },
            { id: 'modifier', label: 'Modifier', icon: 'edit', href: function(r) { return LINK + 'livreur/edition/' + r.editId; } },
        ],
        getActions: function(row) {
            var list = livreursMobileConfig.actions.map(function(a) { return Object.assign({}, a, { href: a.href(row) }); });
            var isActive = row.statut === 'actif';
            list.push({
                id: isActive ? 'desactiver' : 'activer',
                label: isActive ? 'Désactiver' : 'Activer',
                icon: isActive ? 'toggle-left' : 'toggle-right',
                onClick: function(rowData) {
                    showConfirm('Changer le statut de ce livreur ?', function() {
                        $.post(LINK + 'livreur/changer', { id: rowData.id }, function(rep) {
                            showToast(rep.message || 'Statut changé', rep.status ? 'success' : 'error');
                            $('#dataTable').DataTable().ajax.reload();
                        }, 'json').fail(function() { showToast('Erreur serveur', 'error'); });
                    });
                }
            });
            return list;
        }
    };
    renderMobileCards('dataTable', livreursMobileConfig);
    }

    $('.formEditLivreur').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = form.find('.btn_actions');
        const idLivreur = form.find('#id_livreur').val();
        const isAdd = !idLivreur;
        const url = isAdd ? LINK + 'livreur/add' : LINK + 'livreur/edit';

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
                    setTimeout(() => window.location.href = LINK + 'livreur/list', 700);
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
