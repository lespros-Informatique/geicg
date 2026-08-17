$(document).ready(function() {
    if ($('#dataTable').length) {
        const columns = [
            { title: 'N°', data: null, render: function(data, type, row, meta) { return meta.row + 1; } },
            { data: 'code', title: 'Pressing' },
            { data: 'jour', title: 'Jour' },
            { data: 'heure_ouverture', title: 'Ouverture' },
            { data: 'heure_fermeture', title: 'Fermeture' },
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
                    return `
                        <div class="table-actions">
                            <a href="${LINK}horaire/edition/${row.editId}" title="Modifier" class="btn-action btn-action-primary"><i class="fa fa-edit"></i></a>
                        </div>
                    `;
                }
            }
        ];

        const table = initDataTable('dataTable', 'horaire/apiList', columns);

        const horairesMobileConfig = {
        entity: 'horaire',
        primary: [{ key: 'code', label: 'Pressing' }],
        secondary: [{ key: 'jour', label: 'Jour' }],
        detailUrl: function(r) { return LINK + 'horaire/edition/' + r.editId; },
        actions: [
            { id: 'modifier', label: 'Modifier', icon: 'edit', href: function(r) { return LINK + 'horaire/edition/' + r.editId; } },
        ],
        getActions: function(row) {
            var list = horairesMobileConfig.actions.map(function(a) { return Object.assign({}, a, { href: a.href(row) }); });
            return list;
        }
    };
    renderMobileCards('dataTable', horairesMobileConfig);
    }

    $('.formEditHoraire').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = form.find('.btn_actions');
        const idHoraire = form.find('#id_horaire').val();
        const isAdd = !idHoraire;
        const url = isAdd ? LINK + 'horaire/add' : LINK + 'horaire/edit';

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
                    setTimeout(() => window.location.href = LINK + 'horaire/list', 700);
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
